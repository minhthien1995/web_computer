<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import Pagination from '@/components/common/Pagination.vue'

const ui = useUiStore()
const orders = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 10 })
const loading = ref(true)

// Status config
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
  return new Date(dateStr).toLocaleDateString('vi-VN')
}

async function fetchOrders(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/orders', { params: { page } })
    const paginator = data.data || {}
    orders.value = paginator.data || []
    if (paginator.current_page !== undefined) {
      meta.value = {
        current_page: paginator.current_page,
        last_page: paginator.last_page,
        total: paginator.total,
        per_page: paginator.per_page,
      }
    }
  } catch {
    ui.error('Không thể tải danh sách đơn hàng')
  } finally {
    loading.value = false
  }
}

function onPageChange(page) {
  fetchOrders(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => fetchOrders())
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-gray-800 mb-5">Đơn hàng của tôi</h2>

    <div v-if="loading" class="flex justify-center py-12">
      <LoadingSpinner size="lg" />
    </div>

    <EmptyState
      v-else-if="orders.length === 0"
      icon="📦"
      title="Chưa có đơn hàng nào"
      message="Hãy mua sắm để xem đơn hàng tại đây"
    >
      <RouterLink
        to="/san-pham"
        class="mt-4 inline-block px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
      >
        Mua sắm ngay
      </RouterLink>
    </EmptyState>

    <div v-else class="space-y-3">
      <div
        v-for="order in orders"
        :key="order.id"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
      >
        <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
          <div>
            <p class="text-sm font-bold text-gray-800">#{{ order.order_number }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ formatDate(order.created_at) }}</p>
          </div>
          <span
            class="text-xs font-medium px-2.5 py-1 rounded-full"
            :class="getStatus(order.status).class"
          >
            {{ getStatus(order.status).label }}
          </span>
        </div>

        <div class="text-sm text-gray-600 mb-3">
          <span>{{ order.items_count || order.items?.length || 0 }} sản phẩm</span>
          <span class="mx-2">·</span>
          <span class="font-semibold text-gray-800">{{ formatPrice(order.total_amount) }}</span>
        </div>

        <RouterLink
          :to="`/tai-khoan/don-hang/${order.order_number}`"
          class="inline-block text-sm text-blue-600 font-medium hover:underline"
        >
          Xem chi tiết &rarr;
        </RouterLink>
      </div>

      <Pagination
        v-if="meta.last_page > 1"
        :meta="meta"
        @page-change="onPageChange"
      />
    </div>
  </div>
</template>
