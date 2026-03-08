<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import {
  CurrencyDollarIcon,
  ShoppingBagIcon,
  WrenchScrewdriverIcon,
  UsersIcon,
} from '@heroicons/vue/24/outline'

const ui = useUiStore()
const stats = ref(null)
const recentOrders = ref([])
const recentRepairs = ref([])
const loading = ref(true)

function formatPrice(price) {
  return price?.toLocaleString('vi-VN') + '₫'
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('vi-VN')
}

const orderStatusConfig = {
  pending:    { label: 'Chờ xác nhận', class: 'bg-yellow-100 text-yellow-700' },
  confirmed:  { label: 'Đã xác nhận',  class: 'bg-blue-100 text-blue-700' },
  processing: { label: 'Đang xử lý',   class: 'bg-purple-100 text-purple-700' },
  shipped:    { label: 'Đang giao',     class: 'bg-orange-100 text-orange-700' },
  delivered:  { label: 'Đã giao',      class: 'bg-green-100 text-green-700' },
  cancelled:  { label: 'Đã hủy',       class: 'bg-red-100 text-red-700' },
}

function getOrderStatus(status) {
  return orderStatusConfig[status] || { label: status, class: 'bg-gray-100 text-gray-700' }
}

async function fetchDashboard() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/analytics/dashboard')
    const dashData = data.data || {}
    stats.value = dashData.stats || dashData
    recentOrders.value = dashData.recent_orders || []
    recentRepairs.value = dashData.recent_repairs || dashData.recent_repair_orders || []
  } catch {
    ui.error('Không thể tải dữ liệu dashboard')
  } finally {
    loading.value = false
  }
}

const statCards = [
  { key: 'total_revenue', label: 'Doanh thu', icon: CurrencyDollarIcon, color: 'text-green-600 bg-green-100', format: 'price' },
  { key: 'total_orders', label: 'Đơn hàng', icon: ShoppingBagIcon, color: 'text-blue-600 bg-blue-100', format: 'number' },
  { key: 'total_repair_orders', label: 'Sửa chữa', icon: WrenchScrewdriverIcon, color: 'text-orange-600 bg-orange-100', format: 'number' },
  { key: 'total_users', label: 'Khách hàng', icon: UsersIcon, color: 'text-purple-600 bg-purple-100', format: 'number' },
]

function formatStat(value, format) {
  if (!value && value !== 0) return '—'
  if (format === 'price') return formatPrice(value)
  return value.toLocaleString('vi-VN')
}

onMounted(fetchDashboard)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

    <div v-if="loading" class="flex justify-center py-16">
      <LoadingSpinner size="lg" />
    </div>

    <template v-else>
      <!-- Stat cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div
          v-for="card in statCards"
          :key="card.key"
          class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
        >
          <div class="flex items-center gap-3 mb-3">
            <div class="p-2 rounded-lg" :class="card.color">
              <component :is="card.icon" class="w-5 h-5" />
            </div>
            <span class="text-sm text-gray-500">{{ card.label }}</span>
          </div>
          <p class="text-2xl font-bold text-gray-800">
            {{ formatStat(stats?.[card.key], card.format) }}
          </p>
        </div>
      </div>

      <!-- Tables -->
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Recent orders -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-gray-800">Đơn hàng gần đây</h2>
            <RouterLink to="/admin/don-hang" class="text-sm text-blue-600 hover:underline">Xem tất cả</RouterLink>
          </div>
          <div v-if="recentOrders.length === 0" class="text-center py-6 text-gray-400 text-sm">
            Chưa có đơn hàng
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-100">
                  <th class="text-left py-2 text-gray-500 font-medium">Mã đơn</th>
                  <th class="text-left py-2 text-gray-500 font-medium">Khách</th>
                  <th class="text-left py-2 text-gray-500 font-medium">Trạng thái</th>
                  <th class="text-right py-2 text-gray-500 font-medium">Tổng</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in recentOrders.slice(0, 5)" :key="order.id" class="border-b border-gray-50">
                  <td class="py-2 font-medium text-gray-800">#{{ order.order_number }}</td>
                  <td class="py-2 text-gray-600">{{ order.user?.name || order.customer_name }}</td>
                  <td class="py-2">
                    <span class="text-xs px-2 py-0.5 rounded-full" :class="getOrderStatus(order.status).class">
                      {{ getOrderStatus(order.status).label }}
                    </span>
                  </td>
                  <td class="py-2 text-right font-medium">{{ formatPrice(order.total_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent repairs -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-gray-800">Đơn sửa chữa gần đây</h2>
            <RouterLink to="/admin/sua-chua" class="text-sm text-blue-600 hover:underline">Xem tất cả</RouterLink>
          </div>
          <div v-if="recentRepairs.length === 0" class="text-center py-6 text-gray-400 text-sm">
            Chưa có đơn sửa chữa
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-100">
                  <th class="text-left py-2 text-gray-500 font-medium">Mã đơn</th>
                  <th class="text-left py-2 text-gray-500 font-medium">Khách</th>
                  <th class="text-left py-2 text-gray-500 font-medium">Thiết bị</th>
                  <th class="text-left py-2 text-gray-500 font-medium">Trạng thái</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="repair in recentRepairs.slice(0, 5)" :key="repair.id" class="border-b border-gray-50">
                  <td class="py-2 font-medium text-gray-800">#{{ repair.repair_order_number }}</td>
                  <td class="py-2 text-gray-600">{{ repair.customer_name }}</td>
                  <td class="py-2 text-gray-600">{{ repair.device_brand }} {{ repair.device_model }}</td>
                  <td class="py-2">
                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                      {{ repair.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
