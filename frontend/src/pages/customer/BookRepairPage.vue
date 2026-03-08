<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { CheckCircleIcon } from '@heroicons/vue/24/outline'

const ui = useUiStore()
const services = ref([])
const loading = ref(false)
const submitted = ref(false)
const orderNumber = ref('')

const form = ref({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  device_type: '',
  device_brand: '',
  device_model: '',
  device_serial: '',
  issue_description: '',
  repair_service_id: null,
  customer_notes: '',
})

const deviceTypes = [
  { value: 'laptop', label: 'Laptop' },
  { value: 'desktop', label: 'Máy tính để bàn' },
  { value: 'tablet', label: 'Máy tính bảng' },
  { value: 'monitor', label: 'Màn hình' },
  { value: 'other', label: 'Thiết bị khác' },
]

async function fetchServices() {
  try {
    const { data } = await api.get('/repair-services')
    services.value = data.data || []
    if (services.value.length > 0) {
      form.value.repair_service_id = services.value[0].id
    }
  } catch {
    services.value = []
  }
}

async function handleSubmit() {
  if (!form.value.customer_name || !form.value.customer_phone || !form.value.issue_description) {
    ui.error('Vui lòng điền đầy đủ thông tin bắt buộc')
    return
  }

  loading.value = true
  try {
    const { data } = await api.post('/repair-bookings', form.value)
    orderNumber.value = data.data?.order_number || data.data?.id
    submitted.value = true
    ui.success('Đặt lịch thành công!')
  } catch (err) {
    const msg = err.response?.data?.message || 'Đặt lịch thất bại, vui lòng thử lại'
    ui.error(msg)
  } finally {
    loading.value = false
  }
}

onMounted(fetchServices)
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Đặt lịch sửa chữa</h1>
    <p class="text-gray-500 text-sm mb-6">Điền thông tin để chúng tôi liên hệ và sắp xếp lịch sửa chữa</p>

    <!-- Success state -->
    <div v-if="submitted" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
      <CheckCircleIcon class="w-16 h-16 text-green-500 mx-auto mb-4" />
      <h2 class="text-xl font-bold text-gray-800 mb-2">Đặt lịch thành công!</h2>
      <p class="text-gray-500 mb-4">Chúng tôi sẽ liên hệ để xác nhận lịch hẹn trong vòng 24 giờ</p>
      <div class="bg-orange-50 rounded-xl p-4 mb-6">
        <p class="text-sm text-gray-600">Mã đơn sửa chữa</p>
        <p class="text-xl font-bold text-orange-600">{{ orderNumber }}</p>
      </div>
      <p class="text-sm text-gray-500 mb-6">
        Giữ mã đơn để tra cứu trạng thái sửa chữa. Bạn cũng có thể dùng số điện thoại để tra cứu.
      </p>
      <div class="flex gap-3">
        <RouterLink
          to="/sua-chua/tra-cuu"
          class="flex-1 bg-orange-500 text-white py-2.5 rounded-xl text-sm font-medium hover:bg-orange-600"
        >
          Tra cứu đơn
        </RouterLink>
        <RouterLink
          to="/sua-chua"
          class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50"
        >
          Về dịch vụ
        </RouterLink>
      </div>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="handleSubmit" class="space-y-5">
      <!-- Customer info -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-base font-bold text-gray-800 mb-4">Thông tin liên hệ</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
            <input v-model="form.customer_name" type="text" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
              <input v-model="form.customer_phone" type="tel" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="form.customer_email" type="email"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
          </div>
        </div>
      </div>

      <!-- Device info -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-base font-bold text-gray-800 mb-4">Thông tin thiết bị</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Loại thiết bị</label>
            <select v-model="form.device_type"
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Chọn loại thiết bị</option>
              <option v-for="type in deviceTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
            </select>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hãng sản xuất</label>
              <input v-model="form.device_brand" type="text" placeholder="Dell, HP, ASUS..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
              <input v-model="form.device_model" type="text" placeholder="XPS 15, EliteBook..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Số serial (tùy chọn)</label>
            <input v-model="form.device_serial" type="text"
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả sự cố <span class="text-red-500">*</span></label>
            <textarea v-model="form.issue_description" required rows="3"
              placeholder="Mô tả chi tiết vấn đề bạn đang gặp phải..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
            />
          </div>
        </div>
      </div>

      <!-- Service selection -->
      <div v-if="services.length > 0" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-base font-bold text-gray-800 mb-4">Chọn dịch vụ</h2>
        <div class="space-y-2">
          <label
            v-for="service in services"
            :key="service.id"
            class="flex items-start gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all"
            :class="form.repair_service_id === service.id
              ? 'border-blue-500 bg-blue-50'
              : 'border-gray-200 hover:border-gray-300'"
          >
            <input type="radio" v-model="form.repair_service_id" :value="service.id" class="mt-0.5 text-blue-600" />
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-800">{{ service.name }}</p>
              <p v-if="service.description" class="text-xs text-gray-500 mt-0.5">{{ service.description }}</p>
            </div>
            <p class="text-sm font-bold text-orange-600 shrink-0">
              {{ service.base_price ? service.base_price.toLocaleString('vi-VN') + '₫' : 'Liên hệ' }}
            </p>
          </label>
        </div>
      </div>

      <!-- Notes -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú thêm</label>
        <textarea v-model="form.customer_notes" rows="2"
          placeholder="Thời gian thuận tiện, yêu cầu đặc biệt..."
          class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
        />
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full bg-orange-500 text-white py-3 rounded-xl text-sm font-semibold hover:bg-orange-600 transition-colors disabled:opacity-60 flex items-center justify-center gap-2"
      >
        <LoadingSpinner v-if="loading" size="sm" color="white" />
        {{ loading ? 'Đang gửi...' : 'Đặt lịch sửa chữa' }}
      </button>
    </form>
  </div>
</template>
