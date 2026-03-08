<script setup>
import { RouterLink } from 'vue-router'
import { HomeIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'

function goBack() {
  history.back()
}
</script>

<template>
  <div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
      <div class="text-8xl font-black text-gray-200 mb-4 leading-none">404</div>
      <h1 class="text-2xl font-bold text-gray-800 mb-3">Trang không tìm thấy</h1>
      <p class="text-gray-500 mb-8">
        Trang bạn đang tìm kiếm không tồn tại hoặc đã được di chuyển.
      </p>

      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <RouterLink
          to="/"
          class="flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors"
        >
          <HomeIcon class="w-4 h-4" />
          Về trang chủ
        </RouterLink>
        <button
          @click="goBack"
          class="flex items-center justify-center gap-2 px-6 py-2.5 border border-gray-300 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
        >
          <ArrowLeftIcon class="w-4 h-4" />
          Quay lại
        </button>
      </div>
    </div>
  </div>
</template>
