<script setup>
import { ref, onMounted } from 'vue'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import Pagination from '@/components/common/Pagination.vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const ui = useUiStore()
const repairs = ref([])
const technicians = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 15 })
const loading = ref(true)
const filterStatus = ref('')
const selectedRepair = ref(null)
const showModal = ref(false)
const updatingStatus = ref(false)
const newDiagnosisNotes = ref('')
const newQuotedPrice = ref('')
const originalStatus = ref('')
const newTechnicianId = ref('')

const statusConfig = {
  received:      { label: 'Đã tiếp nhận',  class: 'bg-blue-100 text-blue-700' },
  diagnosing:    { label: 'Chẩn đoán',      class: 'bg-yellow-100 text-yellow-700' },
  repairing:     { label: 'Đang sửa',       class: 'bg-purple-100 text-purple-700' },
  waiting_parts: { label: 'Chờ linh kiện',  class: 'bg-orange-100 text-orange-700' },
  completed:     { label: 'Hoàn thành',     class: 'bg-green-100 text-green-700' },
  cancelled:     { label: 'Đã hủy',         class: 'bg-red-100 text-red-700' },
}

const statusOptions = Object.entries(statusConfig).map(([value, cfg]) => ({ value, label: cfg.label }))

function getStatus(status) {
  return statusConfig[status] || { label: status, class: 'bg-gray-100 text-gray-700' }
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('vi-VN')
}

async function fetchRepairs(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (filterStatus.value) params.status = filterStatus.value
    const { data } = await api.get('/admin/repair-orders', { params })
    const paginator = data.data || {}
    repairs.value = paginator.data || []
    if (paginator.current_page !== undefined) {
      meta.value = {
        current_page: paginator.current_page,
        last_page: paginator.last_page,
        total: paginator.total,
        per_page: paginator.per_page,
      }
    }
  } catch {
    ui.error('Không thể tải đơn sửa chữa')
  } finally {
    loading.value = false
  }
}

async function fetchTechnicians() {
  try {
    const { data } = await api.get('/admin/users', { params: { role: 'technician' } })
    technicians.value = data.data?.data || data.data || []
  } catch { /* ignore */ }
}

async function openRepair(repair) {
  selectedRepair.value = { ...repair }
  originalStatus.value = repair.status
  newDiagnosisNotes.value = repair.diagnosis_notes || ''
  newQuotedPrice.value = repair.quoted_price || ''
  newTechnicianId.value = repair.technician_id || ''
  showModal.value = true
}

async function updateRepair() {
  if (!selectedRepair.value) return
  updatingStatus.value = true
  try {
    // Update non-status fields
    await api.put(`/admin/repair-orders/${selectedRepair.value.id}`, {
      diagnosis_notes: newDiagnosisNotes.value,
      quoted_price: newQuotedPrice.value || null,
      technician_id: newTechnicianId.value || null,
    })

    // Update status separately if changed
    if (selectedRepair.value.status !== originalStatus.value) {
      await api.post(`/admin/repair-orders/${selectedRepair.value.id}/status`, {
        status: selectedRepair.value.status,
      })
    }

    ui.success('Cập nhật thành công!')
    fetchRepairs()
    showModal.value = false
  } catch {
    ui.error('Không thể cập nhật')
  } finally {
    updatingStatus.value = false
  }
}

function onPageChange(page) {
  fetchRepairs(page)
}

onMounted(() => {
  fetchRepairs()
  fetchTechnicians()
})
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Đơn sửa chữa</h1>
      <select
        v-model="filterStatus"
        @change="fetchRepairs(1)"
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
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Thiết bị</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Dịch vụ</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Trạng thái</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">KTV</th>
              <th class="text-center px-4 py-3 text-gray-500 font-medium">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="repair in repairs" :key="repair.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-800">#{{ repair.order_number }}</td>
              <td class="px-4 py-3">
                <p class="text-gray-800">{{ repair.customer_name }}</p>
                <p class="text-xs text-gray-400">{{ repair.customer_phone }}</p>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ repair.device_brand }} {{ repair.device_model }}</td>
              <td class="px-4 py-3 text-gray-600">{{ repair.repair_service?.name || '—' }}</td>
              <td class="px-4 py-3">
                <span class="text-xs px-2 py-1 rounded-full font-medium" :class="getStatus(repair.status).class">
                  {{ getStatus(repair.status).label }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ repair.technician?.name || '—' }}</td>
              <td class="px-4 py-3 text-center">
                <button @click="openRepair(repair)"
                  class="px-3 py-1 text-xs text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">
                  Cập nhật
                </button>
              </td>
            </tr>
            <tr v-if="repairs.length === 0">
              <td colspan="7" class="text-center py-10 text-gray-400">Không có đơn sửa chữa</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination v-if="!loading && meta.last_page > 1" :meta="meta" @page-change="onPageChange" />

    <!-- Detail modal -->
    <Dialog :open="showModal" @close="showModal = false" class="relative z-50">
      <div class="fixed inset-0 bg-black/40" />
      <div class="fixed inset-0 flex items-center justify-center p-4">
        <DialogPanel class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
          <div class="p-6" v-if="selectedRepair">
            <div class="flex items-center justify-between mb-5">
              <DialogTitle class="text-lg font-bold text-gray-800">
                #{{ selectedRepair.order_number }}
              </DialogTitle>
              <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

            <!-- Customer & Device -->
            <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm space-y-1">
              <p><span class="font-medium text-gray-700">Khách: </span>{{ selectedRepair.customer_name }} · {{ selectedRepair.customer_phone }}</p>
              <p><span class="font-medium text-gray-700">Thiết bị: </span>{{ selectedRepair.device_brand }} {{ selectedRepair.device_model }}</p>
              <p v-if="selectedRepair.issue_description"><span class="font-medium text-gray-700">Vấn đề: </span>{{ selectedRepair.issue_description }}</p>
            </div>

            <div class="space-y-4">
              <!-- Status -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                <select v-model="selectedRepair.status"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>

              <!-- Technician -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kỹ thuật viên</label>
                <select v-model="newTechnicianId"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="">Chưa phân công</option>
                  <option v-for="tech in technicians" :key="tech.id" :value="tech.id">{{ tech.name }}</option>
                </select>
              </div>

              <!-- Diagnosis notes -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Chẩn đoán</label>
                <textarea v-model="newDiagnosisNotes" rows="3" placeholder="Mô tả kết quả chẩn đoán..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                />
              </div>

              <!-- Actual cost -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Chi phí thực tế (₫)</label>
                <input v-model.number="newQuotedPrice" type="number" min="0"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>

              <div class="flex justify-end gap-3">
                <button @click="showModal = false" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                  Hủy
                </button>
                <button @click="updateRepair" :disabled="updatingStatus"
                  class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60 flex items-center gap-2">
                  <LoadingSpinner v-if="updatingStatus" size="sm" color="white" />
                  Lưu
                </button>
              </div>
            </div>
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  </div>
</template>
