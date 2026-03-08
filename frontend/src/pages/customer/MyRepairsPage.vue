<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import EmptyState from '@/components/common/EmptyState.vue'

const ui = useUiStore()
const repairs = ref([])
const loading = ref(true)

const statusConfig = {
  received:    { label: 'Đã tiếp nhận',  class: 'bg-blue-100 text-blue-700' },
  diagnosing:  { label: 'Đang chẩn đoán', class: 'bg-yellow-100 text-yellow-700' },
  repairing:   { label: 'Đang sửa',       class: 'bg-purple-100 text-purple-700' },
  waiting_parts: { label: 'Chờ linh kiện', class: 'bg-orange-100 text-orange-700' },
  completed:   { label: 'Hoàn thành',     class: 'bg-green-100 text-green-700' },
  cancelled:   { label: 'Đã hủy',         class: 'bg-red-100 text-red-700' },
}

function getStatus(status) {
  return statusConfig[status] || { label: status, class: 'bg-gray-100 text-gray-700' }
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('vi-VN')
}

async function fetchRepairs() {
  loading.value = true
  try {
    const { data } = await api.get('/my-repairs')
    repairs.value = data.data?.data || data.data || []
  } catch {
    ui.error('Không thể tải danh sách đơn sửa chữa')
  } finally {
    loading.value = false
  }
}

onMounted(fetchRepairs)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-5">
      <h2 class="text-xl font-bold text-gray-800">Đơn sửa chữa của tôi</h2>
      <RouterLink
        to="/sua-chua/dat-lich"
        class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
      >
        Đặt lịch mới
      </RouterLink>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <LoadingSpinner size="lg" />
    </div>

    <EmptyState
      v-else-if="repairs.length === 0"
      icon="🔧"
      title="Chưa có đơn sửa chữa"
      message="Đặt lịch sửa chữa để theo dõi tiến trình tại đây"
    >
      <RouterLink
        to="/sua-chua/dat-lich"
        class="mt-4 inline-block px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
      >
        Đặt lịch ngay
      </RouterLink>
    </EmptyState>

    <div v-else class="space-y-3">
      <div
        v-for="repair in repairs"
        :key="repair.id"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
      >
        <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
          <div>
            <p class="text-sm font-bold text-gray-800">#{{ repair.order_number }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ formatDate(repair.created_at) }}</p>
          </div>
          <span
            class="text-xs font-medium px-2.5 py-1 rounded-full"
            :class="getStatus(repair.status).class"
          >
            {{ getStatus(repair.status).label }}
          </span>
        </div>

        <!-- Device info -->
        <div class="text-sm text-gray-600 mb-3 space-y-1">
          <p>
            <span class="font-medium text-gray-700">Thiết bị: </span>
            {{ repair.device_brand }} {{ repair.device_model }}
            <span v-if="repair.device_type" class="text-gray-400">({{ repair.device_type }})</span>
          </p>
          <p v-if="repair.repair_service">
            <span class="font-medium text-gray-700">Dịch vụ: </span>{{ repair.repair_service?.name }}
          </p>
          <p v-if="repair.technician">
            <span class="font-medium text-gray-700">Kỹ thuật viên: </span>{{ repair.technician?.name }}
          </p>
        </div>

        <!-- Status timeline (mini) -->
        <div class="flex items-center gap-1 text-xs text-gray-400">
          <div v-for="(step, i) in ['Tiếp nhận', 'Chẩn đoán', 'Sửa chữa', 'Hoàn thành']" :key="i"
            class="flex items-center gap-1">
            <span
              class="w-2 h-2 rounded-full"
              :class="repair.status === 'completed' || i < ['received','diagnosing','repairing','completed'].indexOf(repair.status)
                ? 'bg-green-500'
                : i === ['received','diagnosing','repairing','completed'].indexOf(repair.status)
                  ? 'bg-blue-500'
                  : 'bg-gray-200'"
            />
            <span>{{ step }}</span>
            <span v-if="i < 3" class="text-gray-200">—</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
