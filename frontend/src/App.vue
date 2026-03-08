<script setup>
import { RouterView } from 'vue-router'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()
</script>

<template>
  <RouterView />
  <!-- Toast notifications -->
  <div class="fixed bottom-4 right-4 z-50 space-y-2">
    <div
      v-for="toast in ui.toasts"
      :key="toast.id"
      class="px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium"
      :class="{
        'bg-green-600': toast.type === 'success',
        'bg-red-600': toast.type === 'error',
        'bg-blue-600': toast.type === 'info',
      }"
    >
      {{ toast.message }}
    </div>
  </div>
</template>
