<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const ui = useUiStore()
const settings = ref([])
const loading = ref(true)
const saving = ref(false)

// Group settings by category prefix
function groupSettings(settingsList) {
  const groups = {}
  for (const s of settingsList) {
    const key = s.key || s.name
    const prefix = key.includes('_') ? key.split('_')[0] : 'general'
    if (!groups[prefix]) groups[prefix] = []
    groups[prefix].push({ ...s, editKey: key })
  }
  return groups
}

const settingsGroups = ref({})

async function fetchSettings() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/settings')
    settings.value = data.data || []
    settingsGroups.value = groupSettings(settings.value)
  } catch {
    ui.error('Không thể tải cài đặt')
  } finally {
    loading.value = false
  }
}

async function saveSettings() {
  saving.value = true
  try {
    // Flatten groups back to array
    const all = Object.values(settingsGroups.value).flat()
    const payload = all.reduce((acc, s) => {
      acc[s.editKey] = s.value
      return acc
    }, {})
    await api.put('/admin/settings', payload)
    ui.success('Lưu cài đặt thành công!')
  } catch {
    ui.error('Không thể lưu cài đặt')
  } finally {
    saving.value = false
  }
}

function groupLabel(prefix) {
  const labels = {
    store: 'Thông tin cửa hàng',
    mail: 'Email',
    shipping: 'Vận chuyển',
    general: 'Cài đặt chung',
  }
  return labels[prefix] || prefix.charAt(0).toUpperCase() + prefix.slice(1)
}

function fieldLabel(key) {
  return key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

onMounted(fetchSettings)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Cài đặt hệ thống</h1>
      <button
        @click="saveSettings"
        :disabled="saving"
        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-60"
      >
        <LoadingSpinner v-if="saving" size="sm" color="white" />
        {{ saving ? 'Đang lưu...' : 'Lưu tất cả' }}
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <LoadingSpinner size="lg" />
    </div>

    <div v-else-if="Object.keys(settingsGroups).length === 0" class="text-center py-16 text-gray-400">
      Không có cài đặt nào
    </div>

    <div v-else class="space-y-5">
      <div
        v-for="(groupItems, prefix) in settingsGroups"
        :key="prefix"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
      >
        <h2 class="text-base font-bold text-gray-800 mb-4">{{ groupLabel(prefix) }}</h2>
        <div class="space-y-4">
          <div v-for="setting in groupItems" :key="setting.editKey">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ fieldLabel(setting.editKey) }}
            </label>
            <textarea
              v-if="setting.type === 'text' || (setting.value && setting.value.length > 60)"
              v-model="setting.value"
              rows="3"
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
            />
            <input
              v-else
              v-model="setting.value"
              :type="setting.type === 'number' ? 'number' : 'text'"
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p v-if="setting.description" class="text-xs text-gray-400 mt-0.5">{{ setting.description }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
