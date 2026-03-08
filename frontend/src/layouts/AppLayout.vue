<script setup>
import { RouterView, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useRouter } from 'vue-router'
import { onMounted } from 'vue'
import { ShoppingCartIcon, UserIcon, Bars3Icon, XMarkIcon, WrenchScrewdriverIcon } from '@heroicons/vue/24/outline'
import { ref } from 'vue'

const auth = useAuthStore()
const cart = useCartStore()
const router = useRouter()
const mobileMenuOpen = ref(false)

onMounted(async () => {
  await auth.fetchUser()
  if (auth.isAuthenticated) cart.fetchCart()
})

async function logout() {
  await auth.logout()
  router.push('/')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        <!-- Logo -->
        <RouterLink to="/" class="text-xl font-bold text-blue-600 shrink-0">
          💻 Web Computer
        </RouterLink>

        <!-- Desktop Nav -->
        <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-700">
          <RouterLink to="/san-pham" class="hover:text-blue-600 transition-colors">Sản phẩm</RouterLink>
          <RouterLink to="/sua-chua" class="hover:text-blue-600 transition-colors">
            <span class="flex items-center gap-1">
              <WrenchScrewdriverIcon class="w-4 h-4" />
              Sửa chữa
            </span>
          </RouterLink>
        </nav>

        <!-- Right actions -->
        <div class="hidden md:flex items-center gap-3">
          <RouterLink to="/gio-hang" class="relative p-2 hover:text-blue-600 transition-colors">
            <ShoppingCartIcon class="w-6 h-6" />
            <span v-if="cart.itemCount > 0"
              class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center font-bold">
              {{ cart.itemCount > 9 ? '9+' : cart.itemCount }}
            </span>
          </RouterLink>
          <template v-if="auth.isAuthenticated">
            <RouterLink to="/tai-khoan" class="flex items-center gap-1.5 text-sm font-medium text-gray-700 hover:text-blue-600">
              <UserIcon class="w-5 h-5" />
              <span>{{ auth.user?.name?.split(' ').pop() }}</span>
            </RouterLink>
            <button @click="logout" class="text-sm text-gray-500 hover:text-red-600 transition-colors">Đăng xuất</button>
          </template>
          <template v-else>
            <RouterLink to="/dang-nhap" class="text-sm font-medium text-gray-700 hover:text-blue-600">Đăng nhập</RouterLink>
            <RouterLink to="/dang-ky" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">Đăng ký</RouterLink>
          </template>
        </div>

        <!-- Mobile menu button -->
        <button class="md:hidden p-2" @click="mobileMenuOpen = !mobileMenuOpen">
          <XMarkIcon v-if="mobileMenuOpen" class="w-6 h-6" />
          <Bars3Icon v-else class="w-6 h-6" />
        </button>
      </div>

      <!-- Mobile menu -->
      <div v-if="mobileMenuOpen" class="md:hidden border-t bg-white px-4 py-3 space-y-2 text-sm">
        <RouterLink to="/san-pham" class="block py-2 hover:text-blue-600" @click="mobileMenuOpen = false">Sản phẩm</RouterLink>
        <RouterLink to="/sua-chua" class="block py-2 hover:text-blue-600" @click="mobileMenuOpen = false">Sửa chữa</RouterLink>
        <RouterLink to="/gio-hang" class="block py-2 hover:text-blue-600" @click="mobileMenuOpen = false">Giỏ hàng ({{ cart.itemCount }})</RouterLink>
        <RouterLink v-if="auth.isAuthenticated" to="/tai-khoan" class="block py-2 hover:text-blue-600" @click="mobileMenuOpen = false">Tài khoản</RouterLink>
        <RouterLink v-else to="/dang-nhap" class="block py-2 hover:text-blue-600" @click="mobileMenuOpen = false">Đăng nhập</RouterLink>
      </div>
    </header>

    <!-- Main content -->
    <main class="flex-1">
      <RouterView />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 text-sm mt-auto">
      <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
          <h3 class="text-white font-semibold mb-3">💻 Web Computer Store</h3>
          <p>Chuyên cung cấp máy tính, linh kiện chính hãng và dịch vụ sửa chữa chuyên nghiệp.</p>
        </div>
        <div>
          <h3 class="text-white font-semibold mb-3">Liên kết</h3>
          <ul class="space-y-1.5">
            <li><RouterLink to="/san-pham" class="hover:text-white transition-colors">Sản phẩm</RouterLink></li>
            <li><RouterLink to="/sua-chua" class="hover:text-white transition-colors">Dịch vụ sửa chữa</RouterLink></li>
            <li><RouterLink to="/sua-chua/tra-cuu" class="hover:text-white transition-colors">Tra cứu đơn sửa</RouterLink></li>
          </ul>
        </div>
        <div>
          <h3 class="text-white font-semibold mb-3">Liên hệ</h3>
          <p>Hotline: <a href="tel:19001234" class="text-blue-400 hover:text-blue-300">1900 1234</a></p>
          <p class="mt-1">Email: info@webcomputer.vn</p>
          <p class="mt-1">Giờ làm việc: 8:00 - 21:00</p>
        </div>
      </div>
      <div class="border-t border-gray-700 py-4 text-center">
        © 2026 Web Computer Store. All rights reserved.
      </div>
    </footer>
  </div>
</template>
