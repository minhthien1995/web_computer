<script setup>
defineProps({
  size: { type: String, default: 'md' },
  color: { type: String, default: 'blue' },
})
</script>

<template>
  <div class="flex items-center justify-center">
    <div
      class="animate-spin rounded-full border-2 border-current border-t-transparent"
      :class="[
        size === 'sm' ? 'w-4 h-4' : size === 'lg' ? 'w-10 h-10' : 'w-7 h-7',
        color === 'white' ? 'text-white' : 'text-blue-600',
      ]"
    />
  </div>
</template>
