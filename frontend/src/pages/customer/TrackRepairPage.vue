<script setup>
import { ref } from 'vue'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { MagnifyingGlassIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'

const ui = useUiStore()
const form = ref({ order_number: '', phone: '' })
const result = ref(null)
const loading = ref(false)
const notFound = ref(false)

const statusConfig = {
  received:     { label: 'Đã tiếp nhận',   class: 'bg-blue-100 text-blue-700', step: 0 },
  diagnosing:   { label: 'Đang chẩn đoán',  class: 'bg-yellow-100 text-yellow-700', step: 1 },
  repairing:    { label: 'Đang sửa chữa',   class: 'bg-purple-100 text-purple-700', step: 2 },
  waiting_parts: { label: 'Chờ linh kiện',  class: 'bg-orange-100 text-orange-700', step: 2 },
  completed:    { label: 'Hoàn thành',      class: 'bg-green-100 text-green-700', step: 3 },
  cancelled:    { label: 'Đã hủy',          class: 'bg-red-100 text-red-700', step: -1 },
}

function getStatus(status) {
  return statusConfig[status] || { label: status, class: 'bg-gray-100 text-gray-700', step: 0 }
}

const timelineSteps = ['Tiếp nhận', 'Chẩn đoán', 'Sửa chữa', 'Hoàn thành']

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

async function handleSearch() {
  if (!form.value.order_number || !form.value.phone) {
    ui.error('Vui lòng nhập mã đơn và số điện thoại')
    return
  }

  loading.value = true
  notFound.value = false
  result.value = null

  try {
    const { data } = await api.get(`/repair/track/${form.value.order_number}`, {
      params: { phone: form.value.phone },
    })
    result.value = data.data
  } catch (err) {
    if (err.response?.status === 404) {
      notFound.value = true
    } else {
      ui.error('Không thể tra cứu. Vui lòng thử lại.')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center">Tra cứu đơn sửa chữa</h1>
    <p class="text-gray-500 text-sm mb-8 text-center">
      Nhập mã đơn sửa chữa và số điện thoại để kiểm tra trạng thái
    </p>

    <!-- Search form -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-6">
      <form @submit.prevent="handleSearch" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mã đơn sửa chữa <span class="text-red-500">*</span></label>
          <input
            v-model="form.order_number"
            type="text"
            required
            placeholder="Ví dụ: RP-20260101-0001"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
          <input
            v-model="form.phone"
            type="tel"
            required
            placeholder="Số điện thoại khi đặt lịch"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-60 flex items-center justify-center gap-2"
        >
          <LoadingSpinner v-if="loading" size="sm" color="white" />
          <MagnifyingGlassIcon v-else class="w-4 h-4" />
          {{ loading ? 'Đang tìm...' : 'Tra cứu' }}
        </button>
      </form>
    </div>

    <!-- Not found -->
    <div v-if="notFound" class="bg-red-50 border border-red-200 rounded-xl p-5 text-center">
      <p class="text-red-700 font-medium mb-1">Không tìm thấy đơn sửa chữa</p>
      <p class="text-red-500 text-sm">Vui lòng kiểm tra lại mã đơn và số điện thoại</p>
    </div>

    <!-- Result -->
    <div v-if="result" class="space-y-4">
      <!-- Header -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
          <div>
            <p class="text-sm text-gray-500">Mã đơn</p>
            <p class="text-lg font-bold text-gray-800">#{{ result.repair_order_number }}</p>
          </div>
          <span
            class="text-sm font-medium px-3 py-1.5 rounded-full"
            :class="getStatus(result.status).class"
          >
            {{ getStatus(result.status).label }}
          </span>
        </div>

        <!-- Timeline -->
        <div class="flex items-center mb-4">
          <template v-for="(step, i) in timelineSteps" :key="i">
            <div class="flex flex-col items-center">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold"
                :class="getStatus(result.status).step >= i
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-200 text-gray-500'"
              >
                <CheckCircleIcon v-if="getStatus(result.status).step > i" class="w-5 h-5" />
                <span v-else>{{ i + 1 }}</span>
              </div>
              <p class="text-xs text-gray-500 mt-1 w-16 text-center">{{ step }}</p>
            </div>
            <div v-if="i < 3" class="flex-1 h-0.5 mx-1 mb-5"
              :class="getStatus(result.status).step > i ? 'bg-blue-500' : 'bg-gray-200'"
            />
          </template>
        </div>

        <!-- Device info -->
        <div class="text-sm text-gray-600 space-y-1">
          <p v-if="result.device_type || result.device_brand">
            <span class="font-medium text-gray-700">Thiết bị: </span>
            {{ [result.device_brand, result.device_model, result.device_type].filter(Boolean).join(' · ') }}
          </p>
          <p v-if="result.service">
            <span class="font-medium text-gray-700">Dịch vụ: </span>{{ result.service?.name }}
          </p>
          <p v-if="result.technician">
            <span class="font-medium text-gray-700">Kỹ thuật viên: </span>{{ result.technician?.name }}
          </p>
          <p v-if="result.estimated_completion">
            <span class="font-medium text-gray-700">Dự kiến hoàn thành: </span>{{ formatDate(result.estimated_completion) }}
          </p>
          <p v-if="result.actual_cost">
            <span class="font-medium text-gray-700">Chi phí: </span>
            <span class="font-bold text-orange-600">{{ result.actual_cost.toLocaleString('vi-VN') }}₫</span>
          </p>
        </div>

        <p v-if="result.diagnosis_notes" class="mt-3 text-sm bg-yellow-50 text-yellow-700 p-3 rounded-lg">
          <span class="font-medium">Chẩn đoán: </span>{{ result.diagnosis_notes }}
        </p>
      </div>
    </div>
  </div>
</template>
