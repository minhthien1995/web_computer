<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const router = useRouter()
const auth = useAuthStore()
const ui = useUiStore()

const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})
const showPassword = ref(false)
const loading = ref(false)

async function handleRegister() {
  if (form.value.password !== form.value.password_confirmation) {
    ui.error('Mật khẩu xác nhận không khớp')
    return
  }
  if (form.value.password.length < 8) {
    ui.error('Mật khẩu phải có ít nhất 8 ký tự')
    return
  }

  loading.value = true
  try {
    await auth.register(form.value)
    ui.success('Đăng ký thành công! Chào mừng bạn!')
    router.push('/')
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) {
      const firstError = Object.values(errors)[0]
      ui.error(Array.isArray(firstError) ? firstError[0] : firstError)
    } else {
      ui.error(err.response?.data?.message || 'Đăng ký thất bại')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <RouterLink to="/" class="text-2xl font-bold text-blue-600">Web Computer Store</RouterLink>
        <h1 class="text-xl font-semibold text-gray-800 mt-2">Tạo tài khoản</h1>
        <p class="text-gray-500 text-sm mt-1">Đăng ký để mua sắm và theo dõi đơn hàng</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
          <input
            v-model="form.name"
            type="text"
            required
            placeholder="Nguyễn Văn A"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
          <input
            v-model="form.email"
            type="email"
            required
            placeholder="your@email.com"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Phone -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
          <input
            v-model="form.phone"
            type="tel"
            placeholder="0901234567"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu <span class="text-red-500">*</span></label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              minlength="8"
              placeholder="Tối thiểu 8 ký tự"
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <EyeSlashIcon v-if="showPassword" class="w-4 h-4" />
              <EyeIcon v-else class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Password confirmation -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu <span class="text-red-500">*</span></label>
          <input
            v-model="form.password_confirmation"
            :type="showPassword ? 'text' : 'password'"
            required
            placeholder="Nhập lại mật khẩu"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors disabled:opacity-60 flex items-center justify-center gap-2"
        >
          <LoadingSpinner v-if="loading" size="sm" color="white" />
          {{ loading ? 'Đang đăng ký...' : 'Đăng ký' }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-600 mt-6">
        Đã có tài khoản?
        <RouterLink to="/dang-nhap" class="text-blue-600 font-medium hover:underline">Đăng nhập</RouterLink>
      </p>
    </div>
  </div>
</template>
