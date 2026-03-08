<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import Pagination from '@/components/common/Pagination.vue'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const ui = useUiStore()
const users = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 20 })
const loading = ref(true)
const search = ref('')
const togglingId = ref(null)

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('vi-VN')
}

const roleConfig = {
  admin:      { label: 'Admin',       class: 'bg-red-100 text-red-700' },
  technician: { label: 'Kỹ thuật viên', class: 'bg-orange-100 text-orange-700' },
  customer:   { label: 'Khách hàng',  class: 'bg-green-100 text-green-700' },
}

function getRole(role) {
  return roleConfig[role] || { label: role, class: 'bg-gray-100 text-gray-600' }
}

async function fetchUsers(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (search.value) params.search = search.value
    const { data } = await api.get('/admin/users', { params })
    users.value = data.data?.data || data.data || []
    if (data.data?.meta) meta.value = data.data.meta
  } catch {
    ui.error('Không thể tải danh sách người dùng')
  } finally {
    loading.value = false
  }
}

async function toggleRole(user) {
  togglingId.value = user.id
  const newRole = user.role === 'customer' ? 'technician' : 'customer'
  try {
    await api.put(`/admin/users/${user.id}/role`, { role: newRole })
    user.role = newRole
    ui.success('Đã thay đổi vai trò')
  } catch {
    ui.error('Không thể thay đổi vai trò')
  } finally {
    togglingId.value = null
  }
}

function onSearch() {
  fetchUsers(1)
}

function onPageChange(page) {
  fetchUsers(page)
}

onMounted(() => fetchUsers())
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Quản lý người dùng</h1>
      <div class="relative w-64">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input
          v-model="search"
          type="text"
          placeholder="Tìm theo tên, email..."
          @keyup.enter="onSearch"
          class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="flex justify-center py-10">
        <LoadingSpinner />
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">ID</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Tên</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Email</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Điện thoại</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Vai trò</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Ngày đăng ký</th>
              <th class="text-center px-4 py-3 text-gray-500 font-medium">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-400 text-xs">{{ user.id }}</td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ user.name }}</td>
              <td class="px-4 py-3 text-gray-600">{{ user.email }}</td>
              <td class="px-4 py-3 text-gray-600">{{ user.phone || '—' }}</td>
              <td class="px-4 py-3">
                <span class="text-xs px-2 py-1 rounded-full font-medium" :class="getRole(user.role).class">
                  {{ getRole(user.role).label }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-500">{{ formatDate(user.created_at) }}</td>
              <td class="px-4 py-3 text-center">
                <button
                  v-if="user.role !== 'admin'"
                  @click="toggleRole(user)"
                  :disabled="togglingId === user.id"
                  class="px-3 py-1 text-xs border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                >
                  <LoadingSpinner v-if="togglingId === user.id" size="sm" />
                  <span v-else>{{ user.role === 'customer' ? '→ KTV' : '→ KH' }}</span>
                </button>
                <span v-else class="text-xs text-gray-400">—</span>
              </td>
            </tr>
            <tr v-if="users.length === 0">
              <td colspan="7" class="text-center py-10 text-gray-400">Không có người dùng</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination v-if="!loading && meta.last_page > 1" :meta="meta" @page-change="onPageChange" />
  </div>
</template>
