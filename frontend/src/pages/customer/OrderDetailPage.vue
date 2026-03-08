<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const ui = useUiStore()

const order = ref(null)
const loading = ref(true)
const cancelModal = ref(false)
const cancelling = ref(false)

const statusConfig = {
  pending:    { label: 'Chờ xác nhận', class: 'bg-yellow-100 text-yellow-700' },
  confirmed:  { label: 'Đã xác nhận',  class: 'bg-blue-100 text-blue-700' },
  processing: { label: 'Đang xử lý',   class: 'bg-purple-100 text-purple-700' },
  shipped:    { label: 'Đang giao',     class: 'bg-orange-100 text-orange-700' },
  delivered:  { label: 'Đã giao',      class: 'bg-green-100 text-green-700' },
  cancelled:  { label: 'Đã hủy',       class: 'bg-red-100 text-red-700' },
}

function getStatus(status) {
  return statusConfig[status] || { label: status, class: 'bg-gray-100 text-gray-700' }
}

function formatPrice(price) {
  return price?.toLocaleString('vi-VN') + '₫'
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleString('vi-VN')
}

function getImageUrl(item) {
  const images = item.product?.images
  if (images && images.length > 0) return images[0].url || images[0]
  return null
}

async function fetchOrder() {
  loading.value = true
  try {
    const { data } = await api.get(`/orders/${route.params.orderNo}`)
    order.value = data.data
  } catch {
    ui.error('Không tìm thấy đơn hàng')
    router.push('/tai-khoan/don-hang')
  } finally {
    loading.value = false
  }
}

async function cancelOrder() {
  cancelling.value = true
  try {
    await api.post(`/orders/${order.value.order_number}/cancel`)
    ui.success('Đã hủy đơn hàng thành công')
    order.value.status = 'cancelled'
  } catch (err) {
    ui.error(err.response?.data?.message || 'Không thể hủy đơn hàng')
  } finally {
    cancelling.value = false
    cancelModal.value = false
  }
}

const paymentLabels = {
  cod: 'Thanh toán khi nhận hàng (COD)',
  bank_transfer: 'Chuyển khoản ngân hàng',
}

onMounted(fetchOrder)
</script>

<template>
  <div>
    <div class="flex items-center gap-3 mb-5">
      <RouterLink to="/tai-khoan/don-hang" class="text-gray-500 hover:text-gray-700">
        <ArrowLeftIcon class="w-5 h-5" />
      </RouterLink>
      <h2 class="text-xl font-bold text-gray-800">Chi tiết đơn hàng</h2>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <LoadingSpinner size="lg" />
    </div>

    <template v-else-if="order">
      <!-- Order header -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="text-sm text-gray-500">Mã đơn hàng</p>
            <p class="text-lg font-bold text-gray-800">#{{ order.order_number }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ formatDate(order.created_at) }}</p>
          </div>
          <span
            class="text-sm font-medium px-3 py-1.5 rounded-full"
            :class="getStatus(order.status).class"
          >
            {{ getStatus(order.status).label }}
          </span>
        </div>
      </div>

      <!-- Shipping address -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-4">
        <h3 class="text-sm font-bold text-gray-700 mb-3">Địa chỉ giao hàng</h3>
        <div class="text-sm text-gray-600 space-y-1">
          <p class="font-medium text-gray-800">{{ order.shipping_address?.name }}</p>
          <p>{{ order.shipping_address?.phone }}</p>
          <p>
            {{ [order.shipping_address?.address, order.shipping_address?.ward, order.shipping_address?.district, order.shipping_address?.province].filter(Boolean).join(', ') }}
          </p>
        </div>
      </div>

      <!-- Products -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-4">
        <h3 class="text-sm font-bold text-gray-700 mb-3">Sản phẩm đặt mua</h3>
        <div class="space-y-3">
          <div v-for="item in order.items" :key="item.id" class="flex gap-3">
            <div class="w-14 h-14 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
              <img v-if="getImageUrl(item)" :src="getImageUrl(item)" :alt="item.product?.name" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-xl">🖥️</div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800">{{ item.product?.name || item.product_name }}</p>
              <p v-if="item.variant" class="text-xs text-gray-500">{{ item.variant.name }}</p>
              <p class="text-xs text-gray-500 mt-0.5">x{{ item.quantity }} × {{ formatPrice(item.unit_price) }}</p>
            </div>
            <p class="text-sm font-semibold text-gray-800 shrink-0">
              {{ formatPrice(item.unit_price * item.quantity) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Payment & Total -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-4">
        <h3 class="text-sm font-bold text-gray-700 mb-3">Thanh toán</h3>
        <div class="text-sm space-y-2">
          <div class="flex justify-between text-gray-600">
            <span>Phương thức</span>
            <span>{{ paymentLabels[order.payment_method] || order.payment_method }}</span>
          </div>
          <div class="flex justify-between text-gray-600">
            <span>Tạm tính</span>
            <span>{{ formatPrice(order.subtotal) }}</span>
          </div>
          <div class="flex justify-between text-gray-600">
            <span>Phí vận chuyển</span>
            <span>{{ formatPrice(order.shipping_fee) }}</span>
          </div>
          <div class="flex justify-between font-bold text-gray-800 border-t border-gray-100 pt-2 mt-2">
            <span>Tổng cộng</span>
            <span class="text-blue-600 text-lg">{{ formatPrice(order.total_amount) }}</span>
          </div>
        </div>
      </div>

      <!-- Cancel button -->
      <div v-if="order.status === 'pending'">
        <button
          @click="cancelModal = true"
          class="w-full py-2.5 border-2 border-red-300 text-red-600 rounded-xl text-sm font-medium hover:bg-red-50 transition-colors"
        >
          Hủy đơn hàng
        </button>
      </div>
    </template>

    <ConfirmModal
      :show="cancelModal"
      title="Hủy đơn hàng"
      message="Bạn có chắc muốn hủy đơn hàng này? Hành động này không thể hoàn tác."
      confirm-text="Xác nhận hủy"
      @confirm="cancelOrder"
      @cancel="cancelModal = false"
    />
  </div>
</template>
