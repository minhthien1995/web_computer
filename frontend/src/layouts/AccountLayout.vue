<script setup>
import { RouterView, RouterLink, useRoute } from 'vue-router'
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import {
  ShoppingBagIcon,
  WrenchScrewdriverIcon,
  UserCircleIcon,
  Bars3Icon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const route = useRoute()
const mobileMenuOpen = ref(false)

const navItems = [
  { label: 'Đơn hàng của tôi', to: '/tai-khoan/don-hang', icon: ShoppingBagIcon },
  { label: 'Đơn sửa chữa', to: '/tai-khoan/sua-chua', icon: WrenchScrewdriverIcon },
  { label: 'Hồ sơ', to: '/tai-khoan/ho-so', icon: UserCircleIcon },
]

function isActive(path) {
  return route.path.startsWith(path)
}
</script>

<template>
  <div class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Mobile toggle -->
      <div class="flex items-center justify-between mb-4 md:hidden">
        <h2 class="text-lg font-semibold text-gray-800">Tài khoản của tôi</h2>
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg bg-white border border-gray-200">
          <Bars3Icon v-if="!mobileMenuOpen" class="w-5 h-5" />
          <XMarkIcon v-else class="w-5 h-5" />
        </button>
      </div>

      <div class="flex gap-6">
        <!-- Sidebar -->
        <aside class="w-56 shrink-0" :class="mobileMenuOpen ? 'block' : 'hidden md:block'">
          <div class="bg-white rounded-xl p-4 mb-4 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                {{ auth.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
              </div>
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ auth.user?.name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth.user?.email }}</p>
              </div>
            </div>
          </div>

          <nav class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <RouterLink
              v-for="item in navItems"
              :key="item.to"
              :to="item.to"
              @click="mobileMenuOpen = false"
              class="flex items-center gap-3 px-4 py-3 text-sm transition-colors border-b border-gray-50 last:border-0"
              :class="isActive(item.to)
                ? 'bg-blue-50 text-blue-600 font-medium'
                : 'text-gray-700 hover:bg-gray-50'"
            >
              <component :is="item.icon" class="w-4 h-4" />
              {{ item.label }}
            </RouterLink>
          </nav>
        </aside>

        <!-- Main content -->
        <div class="flex-1 min-w-0">
          <RouterView />
        </div>
      </div>
    </div>
  </div>
</template>
