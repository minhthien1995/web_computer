<script setup>
import { ref } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const ui = useUiStore()

const form = ref({ email: '', password: '' })
const showPassword = ref(false)
const loading = ref(false)

async function handleLogin() {
  if (!form.value.email || !form.value.password) {
    ui.error('Vui lòng nhập đầy đủ thông tin')
    return
  }

  loading.value = true
  try {
    await auth.login(form.value)
    ui.success('Đăng nhập thành công!')
    const redirect = route.query.redirect || '/'
    router.push(redirect)
  } catch (err) {
    const msg = err.response?.data?.message || 'Email hoặc mật khẩu không đúng'
    ui.error(msg)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-8">
      <!-- Logo & title -->
      <div class="text-center mb-8">
        <RouterLink to="/" class="text-2xl font-bold text-blue-600">Web Computer Store</RouterLink>
        <h1 class="text-xl font-semibold text-gray-800 mt-2">Đăng nhập</h1>
        <p class="text-gray-500 text-sm mt-1">Chào mừng bạn quay trở lại!</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            autocomplete="email"
            placeholder="your@email.com"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              autocomplete="current-password"
              placeholder="••••••••"
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10"
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

        <!-- Submit -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors disabled:opacity-60 flex items-center justify-center gap-2"
        >
          <LoadingSpinner v-if="loading" size="sm" color="white" />
          {{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
        </button>
      </form>

      <p class="text-center text-sm text-gray-600 mt-6">
        Chưa có tài khoản?
        <RouterLink to="/dang-ky" class="text-blue-600 font-medium hover:underline">Đăng ký ngay</RouterLink>
      </p>
    </div>
  </div>
</template>
