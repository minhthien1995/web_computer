<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const ui = useUiStore()
const loading = ref(true)
const stats = ref(null)
const topProducts = ref([])
const revenueByDay = ref([])

const dateRange = ref({
  from: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  to: new Date().toISOString().split('T')[0],
})

function formatPrice(price) {
  return price?.toLocaleString('vi-VN') + '₫'
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('vi-VN')
}

async function fetchAnalytics() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/analytics/revenue', {
      params: { from: dateRange.value.from, to: dateRange.value.to },
    })
    const analyticsData = data.data || {}
    stats.value = analyticsData.summary || analyticsData
    topProducts.value = analyticsData.top_products || []
    revenueByDay.value = analyticsData.revenue_by_day || []
  } catch {
    ui.error('Không thể tải báo cáo')
  } finally {
    loading.value = false
  }
}

onMounted(fetchAnalytics)
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Báo cáo & Thống kê</h1>

      <!-- Date range -->
      <div class="flex items-center gap-2">
        <input
          v-model="dateRange.from"
          type="date"
          class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <span class="text-gray-400">—</span>
        <input
          v-model="dateRange.to"
          type="date"
          class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button
          @click="fetchAnalytics"
          class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
        >
          Xem
        </button>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <LoadingSpinner size="lg" />
    </div>

    <template v-else>
      <!-- Summary stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div v-for="(value, key) in stats" :key="key" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
          <p class="text-xs text-gray-500 mb-1 capitalize">{{ key.replace(/_/g, ' ') }}</p>
          <p class="text-xl font-bold text-gray-800">
            {{ typeof value === 'number' && key.includes('revenue') ? formatPrice(value) : (value?.toLocaleString?.('vi-VN') ?? value) }}
          </p>
        </div>
      </div>

      <!-- Revenue by day -->
      <div v-if="revenueByDay.length > 0" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-6">
        <h2 class="text-base font-bold text-gray-800 mb-4">Doanh thu theo ngày</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="text-left py-2 text-gray-500 font-medium">Ngày</th>
                <th class="text-right py-2 text-gray-500 font-medium">Đơn hàng</th>
                <th class="text-right py-2 text-gray-500 font-medium">Doanh thu</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in revenueByDay" :key="row.date" class="border-b border-gray-50">
                <td class="py-2 text-gray-700">{{ formatDate(row.date) }}</td>
                <td class="py-2 text-right text-gray-600">{{ row.orders }}</td>
                <td class="py-2 text-right font-medium text-gray-800">{{ formatPrice(row.revenue) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top products -->
      <div v-if="topProducts.length > 0" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-base font-bold text-gray-800 mb-4">Sản phẩm bán chạy</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="text-left py-2 text-gray-500 font-medium">STT</th>
                <th class="text-left py-2 text-gray-500 font-medium">Sản phẩm</th>
                <th class="text-right py-2 text-gray-500 font-medium">Số lượng bán</th>
                <th class="text-right py-2 text-gray-500 font-medium">Doanh thu</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in topProducts" :key="item.id" class="border-b border-gray-50">
                <td class="py-2 text-gray-400">{{ i + 1 }}</td>
                <td class="py-2 font-medium text-gray-800">{{ item.name }}</td>
                <td class="py-2 text-right text-gray-600">{{ item.total_sold?.toLocaleString('vi-VN') }}</td>
                <td class="py-2 text-right font-medium text-gray-800">{{ formatPrice(item.total_revenue) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="!stats && revenueByDay.length === 0 && topProducts.length === 0"
        class="text-center py-10 text-gray-400">
        Không có dữ liệu trong khoảng thời gian đã chọn
      </div>
    </template>
  </div>
</template>
