<script setup>
defineProps({
  title: { type: String, default: 'Không có dữ liệu' },
  message: { type: String, default: '' },
  icon: { type: String, default: '📭' },
})
</script>

<template>
  <div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="text-5xl mb-4">{{ icon }}</div>
    <h3 class="text-lg font-semibold text-gray-700 mb-2">{{ title }}</h3>
    <p v-if="message" class="text-gray-500 text-sm max-w-xs">{{ message }}</p>
    <slot />
  </div>
</template>
