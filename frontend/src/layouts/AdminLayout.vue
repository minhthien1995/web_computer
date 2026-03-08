<script setup>
import { RouterView, RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const navItems = [
  { to: '/admin', label: 'Dashboard', exact: true },
  { to: '/admin/san-pham', label: 'Sản phẩm' },
  { to: '/admin/don-hang', label: 'Đơn hàng' },
  { to: '/admin/sua-chua', label: 'Sửa chữa' },
  { to: '/admin/nguoi-dung', label: 'Người dùng' },
  { to: '/admin/bao-cao', label: 'Báo cáo' },
  { to: '/admin/cai-dat', label: 'Cài đặt' },
]

async function logout() {
  await auth.logout()
  router.push('/dang-nhap')
}

function isActive(item) {
  return item.exact ? route.path === item.to : route.path.startsWith(item.to)
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex">
    <aside class="w-64 bg-gray-900 text-white flex flex-col">
      <div class="p-4 border-b border-gray-700">
        <h1 class="text-lg font-bold">Admin Panel</h1>
        <p class="text-xs text-gray-400 mt-1">Web Computer Store</p>
      </div>
      <nav class="flex-1 p-3 space-y-1 text-sm">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="block px-3 py-2 rounded transition-colors"
          :class="isActive(item) ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
        >
          {{ item.label }}
        </RouterLink>
      </nav>
      <div class="p-4 border-t border-gray-700">
        <button @click="logout" class="w-full text-left text-sm text-gray-400 hover:text-white px-3 py-2 rounded hover:bg-gray-700 transition-colors">
          Đăng xuất
        </button>
      </div>
    </aside>
    <main class="flex-1 p-6 overflow-auto">
      <RouterView />
    </main>
  </div>
</template>
