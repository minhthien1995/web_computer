<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  images: { type: Array, default: () => [] },
})

const selectedIndex = ref(0)

const normalizedImages = computed(() => {
  if (!props.images || props.images.length === 0) return []
  return props.images.map((img) => (typeof img === 'string' ? { url: img } : img))
})

const mainImage = computed(() => normalizedImages.value[selectedIndex.value]?.url || null)

function selectImage(index) {
  selectedIndex.value = index
}
</script>

<template>
  <div class="flex flex-col gap-3">
    <!-- Main image -->
    <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden border border-gray-200">
      <img
        v-if="mainImage"
        :src="mainImage"
        alt="Product image"
        class="w-full h-full object-contain"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <span class="text-8xl">🖥️</span>
      </div>
    </div>

    <!-- Thumbnails -->
    <div v-if="normalizedImages.length > 1" class="flex gap-2 overflow-x-auto pb-1">
      <button
        v-for="(img, i) in normalizedImages"
        :key="i"
        @click="selectImage(i)"
        class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-all"
        :class="i === selectedIndex ? 'border-blue-500' : 'border-gray-200 hover:border-gray-400'"
      >
        <img :src="img.url" :alt="`Ảnh ${i + 1}`" class="w-full h-full object-cover" />
      </button>
    </div>
  </div>
</template>
