<script setup>
import { ref } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const auth = useAuthStore()
const ui = useUiStore()

const profileForm = ref({
  name: auth.user?.name || '',
  phone: auth.user?.phone || '',
})

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const savingProfile = ref(false)
const savingPassword = ref(false)

async function saveProfile() {
  if (!profileForm.value.name) {
    ui.error('Họ tên không được để trống')
    return
  }
  savingProfile.value = true
  try {
    const { data } = await api.put('/auth/profile', profileForm.value)
    auth.user = data.data
    ui.success('Cập nhật hồ sơ thành công!')
  } catch (err) {
    ui.error(err.response?.data?.message || 'Cập nhật thất bại')
  } finally {
    savingProfile.value = false
  }
}

async function changePassword() {
  if (!passwordForm.value.current_password || !passwordForm.value.password) {
    ui.error('Vui lòng điền đầy đủ thông tin')
    return
  }
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    ui.error('Mật khẩu mới không khớp')
    return
  }
  if (passwordForm.value.password.length < 8) {
    ui.error('Mật khẩu mới phải có ít nhất 8 ký tự')
    return
  }

  savingPassword.value = true
  try {
    await api.put('/auth/password', passwordForm.value)
    ui.success('Đổi mật khẩu thành công!')
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
  } catch (err) {
    ui.error(err.response?.data?.message || 'Đổi mật khẩu thất bại')
  } finally {
    savingPassword.value = false
  }
}

const initials = auth.user?.name?.split(' ').map(w => w[0]).slice(-2).join('').toUpperCase() || 'U'
</script>

<template>
  <div class="space-y-5">
    <h2 class="text-xl font-bold text-gray-800">Hồ sơ cá nhân</h2>

    <!-- Avatar section -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
      <div class="flex items-center gap-4 mb-5">
        <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-xl font-bold">
          {{ initials }}
        </div>
        <div>
          <p class="font-semibold text-gray-800">{{ auth.user?.name }}</p>
          <p class="text-sm text-gray-500">{{ auth.user?.email }}</p>
          <p class="text-xs text-gray-400 mt-0.5 capitalize">{{ auth.user?.role || 'Khách hàng' }}</p>
        </div>
      </div>

      <!-- Profile form -->
      <form @submit.prevent="saveProfile" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
            <input
              v-model="profileForm.name"
              type="text"
              required
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
            <input
              v-model="profileForm.phone"
              type="tel"
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            :value="auth.user?.email"
            type="email"
            disabled
            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-gray-50 text-gray-500 cursor-not-allowed"
          />
          <p class="text-xs text-gray-400 mt-1">Email không thể thay đổi</p>
        </div>

        <button
          type="submit"
          :disabled="savingProfile"
          class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-60"
        >
          <LoadingSpinner v-if="savingProfile" size="sm" color="white" />
          {{ savingProfile ? 'Đang lưu...' : 'Lưu thay đổi' }}
        </button>
      </form>
    </div>

    <!-- Change password -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
      <h3 class="text-base font-bold text-gray-800 mb-4">Đổi mật khẩu</h3>

      <form @submit.prevent="changePassword" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu hiện tại</label>
          <input
            v-model="passwordForm.current_password"
            type="password"
            autocomplete="current-password"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
            <input
              v-model="passwordForm.password"
              type="password"
              minlength="8"
              autocomplete="new-password"
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu</label>
            <input
              v-model="passwordForm.password_confirmation"
              type="password"
              autocomplete="new-password"
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <button
          type="submit"
          :disabled="savingPassword"
          class="flex items-center gap-2 px-5 py-2.5 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 disabled:opacity-60"
        >
          <LoadingSpinner v-if="savingPassword" size="sm" color="white" />
          {{ savingPassword ? 'Đang lưu...' : 'Đổi mật khẩu' }}
        </button>
      </form>
    </div>
  </div>
</template>
