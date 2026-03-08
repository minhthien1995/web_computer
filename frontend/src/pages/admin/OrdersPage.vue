<script setup>
import { ref, onMounted } from 'vue'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import Pagination from '@/components/common/Pagination.vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const ui = useUiStore()
const orders = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 15 })
const loading = ref(true)
const filterStatus = ref('')
const selectedOrder = ref(null)
const showModal = ref(false)
const updatingStatus = ref(false)

const statusConfig = {
  pending:    { label: 'Chờ xác nhận', class: 'bg-yellow-100 text-yellow-700' },
  confirmed:  { label: 'Đã xác nhận',  class: 'bg-blue-100 text-blue-700' },
  processing: { label: 'Đang xử lý',   class: 'bg-purple-100 text-purple-700' },
  shipped:    { label: 'Đang giao',     class: 'bg-orange-100 text-orange-700' },
  delivered:  { label: 'Đã giao',      class: 'bg-green-100 text-green-700' },
  cancelled:  { label: 'Đã hủy',       class: 'bg-red-100 text-red-700' },
}

const statusOptions = Object.entries(statusConfig).map(([value, cfg]) => ({ value, label: cfg.label }))

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
    const params = { page }
    if (filterStatus.value) params.status = filterStatus.value
    const { data } = await api.get('/admin/orders', { params })
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
    ui.error('Không thể tải đơn hàng')
  } finally {
    loading.value = false
  }
}

async function openOrder(order) {
  selectedOrder.value = order
  // Fetch full order details
  try {
    const { data } = await api.get(`/admin/orders/${order.id}`)
    selectedOrder.value = data.data
  } catch { /* use list data */ }
  showModal.value = true
}

async function updateStatus(newStatus) {
  if (!selectedOrder.value) return
  updatingStatus.value = true
  try {
    await api.put(`/admin/orders/${selectedOrder.value.id}`, {
      status: newStatus,
    })
    selectedOrder.value.status = newStatus
    ui.success('Cập nhật trạng thái thành công!')
    // Update in list
    const idx = orders.value.findIndex(o => o.id === selectedOrder.value.id)
    if (idx !== -1) orders.value[idx].status = newStatus
  } catch {
    ui.error('Không thể cập nhật trạng thái')
  } finally {
    updatingStatus.value = false
  }
}

function onFilterChange() {
  fetchOrders(1)
}

function onPageChange(page) {
  fetchOrders(page)
}

onMounted(() => fetchOrders())
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Quản lý đơn hàng</h1>
      <select
        v-model="filterStatus"
        @change="onFilterChange"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="">Tất cả trạng thái</option>
        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
      </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="flex justify-center py-10">
        <LoadingSpinner />
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Mã đơn</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Khách hàng</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Ngày đặt</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Trạng thái</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Thanh toán</th>
              <th class="text-right px-4 py-3 text-gray-500 font-medium">Tổng</th>
              <th class="text-center px-4 py-3 text-gray-500 font-medium">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-800">#{{ order.order_number }}</td>
              <td class="px-4 py-3 text-gray-600">{{ order.user?.name || order.shipping_address?.name }}</td>
              <td class="px-4 py-3 text-gray-500">{{ formatDate(order.created_at) }}</td>
              <td class="px-4 py-3">
                <span class="text-xs px-2 py-1 rounded-full font-medium" :class="getStatus(order.status).class">
                  {{ getStatus(order.status).label }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-600 text-xs">{{ order.payment_method?.toUpperCase() || 'COD' }}</td>
              <td class="px-4 py-3 text-right font-medium text-gray-800">{{ formatPrice(order.total_amount) }}</td>
              <td class="px-4 py-3 text-center">
                <button
                  @click="openOrder(order)"
                  class="px-3 py-1 text-xs text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50"
                >
                  Chi tiết
                </button>
              </td>
            </tr>
            <tr v-if="orders.length === 0">
              <td colspan="7" class="text-center py-10 text-gray-400">Không có đơn hàng</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination v-if="!loading && meta.last_page > 1" :meta="meta" @page-change="onPageChange" />

    <!-- Order detail modal -->
    <Dialog :open="showModal" @close="showModal = false" class="relative z-50">
      <div class="fixed inset-0 bg-black/40" />
      <div class="fixed inset-0 flex items-center justify-center p-4">
        <DialogPanel class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
          <div class="p-6" v-if="selectedOrder">
            <div class="flex items-center justify-between mb-5">
              <DialogTitle class="text-lg font-bold text-gray-800">
                Chi tiết đơn #{{ selectedOrder.order_number }}
              </DialogTitle>
              <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

            <!-- Status update -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Cập nhật trạng thái</label>
              <div class="flex gap-2 flex-wrap">
                <button
                  v-for="opt in statusOptions"
                  :key="opt.value"
                  @click="updateStatus(opt.value)"
                  :disabled="updatingStatus || selectedOrder.status === opt.value"
                  class="px-3 py-1.5 text-xs rounded-lg border transition-all disabled:opacity-50"
                  :class="selectedOrder.status === opt.value
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'border-gray-300 hover:bg-gray-50'"
                >
                  {{ opt.label }}
                </button>
              </div>
            </div>

            <!-- Shipping address -->
            <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm">
              <p class="font-medium text-gray-700 mb-1">Địa chỉ giao hàng</p>
              <p class="text-gray-600">{{ selectedOrder.shipping_address?.name }} · {{ selectedOrder.shipping_address?.phone }}</p>
              <p class="text-gray-600">
                {{ [selectedOrder.shipping_address?.address, selectedOrder.shipping_address?.district, selectedOrder.shipping_address?.province].filter(Boolean).join(', ') }}
              </p>
            </div>

            <!-- Items -->
            <div class="space-y-2 mb-4">
              <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between text-sm">
                <span class="text-gray-700">{{ item.product?.name || item.product_name }} x{{ item.quantity }}</span>
                <span class="font-medium">{{ formatPrice(item.unit_price * item.quantity) }}</span>
              </div>
            </div>

            <div class="border-t border-gray-100 pt-3 text-sm">
              <div class="flex justify-between font-bold text-gray-800">
                <span>Tổng cộng</span>
                <span class="text-blue-600">{{ formatPrice(selectedOrder.total_amount) }}</span>
              </div>
            </div>
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  </div>
</template>
