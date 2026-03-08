<script setup>
import { computed } from 'vue'

const props = defineProps({
  meta: {
    type: Object,
    required: true,
    // { current_page, last_page, per_page, total }
  },
})

const emit = defineEmits(['page-change'])

const pages = computed(() => {
  const { current_page, last_page } = props.meta
  const range = []
  const delta = 2
  const left = Math.max(1, current_page - delta)
  const right = Math.min(last_page, current_page + delta)

  for (let i = left; i <= right; i++) {
    range.push(i)
  }
  // Add first/last with ellipsis
  const result = []
  let prev = null
  for (const p of range) {
    if (prev && p - prev > 1) result.push('...')
    result.push(p)
    prev = p
  }
  if (range[0] > 1) result.unshift(1)
  if (range[range.length - 1] < last_page) result.push(last_page)
  return result
})

function go(page) {
  if (page < 1 || page > props.meta.last_page || page === props.meta.current_page) return
  emit('page-change', page)
}
</script>

<template>
  <div v-if="meta.last_page > 1" class="flex items-center justify-center gap-1 mt-6">
    <!-- Previous -->
    <button
      @click="go(meta.current_page - 1)"
      :disabled="meta.current_page === 1"
      class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
    >
      &laquo;
    </button>

    <!-- Page numbers -->
    <template v-for="page in pages" :key="page">
      <span v-if="page === '...'" class="px-2 text-gray-400">...</span>
      <button
        v-else
        @click="go(page)"
        :class="[
          'px-3 py-1.5 text-sm rounded-lg border',
          page === meta.current_page
            ? 'bg-blue-600 text-white border-blue-600'
            : 'border-gray-300 hover:bg-gray-50',
        ]"
      >
        {{ page }}
      </button>
    </template>

    <!-- Next -->
    <button
      @click="go(meta.current_page + 1)"
      :disabled="meta.current_page === meta.last_page"
      class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
    >
      &raquo;
    </button>

    <span class="text-sm text-gray-500 ml-2">
      {{ meta.total }} kết quả
    </span>
  </div>
</template>
