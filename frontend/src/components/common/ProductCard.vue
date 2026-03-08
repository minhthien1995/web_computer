<script setup>
import { RouterLink } from 'vue-router'
import { ShoppingCartIcon, StarIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  product: {
    type: Object,
    required: true,
    // { id, name, slug, base_price, sale_price, images, category, brand, is_featured }
  },
})

const emit = defineEmits(['add-to-cart'])

function formatPrice(price) {
  return price?.toLocaleString('vi-VN') + '₫'
}

function getImageUrl(product) {
  if (product.images && product.images.length > 0) {
    return product.images[0].url || product.images[0]
  }
  return null
}
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-200 hover:scale-[1.02] overflow-hidden group border border-gray-100">
    <!-- Product Image -->
    <RouterLink :to="`/san-pham/${product.slug}`" class="block">
      <div class="relative aspect-square bg-gray-50 overflow-hidden">
        <img
          v-if="getImageUrl(product)"
          :src="getImageUrl(product)"
          :alt="product.name"
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        />
        <div v-else class="w-full h-full flex items-center justify-center">
          <span class="text-6xl">🖥️</span>
        </div>

        <!-- Featured badge -->
        <div
          v-if="product.is_featured"
          class="absolute top-2 left-2 bg-orange-500 text-white text-xs font-medium px-2 py-0.5 rounded-full flex items-center gap-1"
        >
          <StarIcon class="w-3 h-3" />
          Nổi bật
        </div>

        <!-- Sale badge -->
        <div
          v-if="product.sale_price && product.sale_price < product.base_price"
          class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"
        >
          -{{ Math.round((1 - product.sale_price / product.base_price) * 100) }}%
        </div>
      </div>
    </RouterLink>

    <!-- Content -->
    <div class="p-4">
      <!-- Brand -->
      <p v-if="product.brand" class="text-xs text-blue-600 font-medium uppercase tracking-wide mb-1">
        {{ product.brand.name || product.brand }}
      </p>

      <!-- Name -->
      <RouterLink :to="`/san-pham/${product.slug}`">
        <h3 class="text-sm font-semibold text-gray-800 line-clamp-2 hover:text-blue-600 transition-colors mb-3">
          {{ product.name }}
        </h3>
      </RouterLink>

      <!-- Price -->
      <div class="flex items-end gap-2 mb-3">
        <span class="text-lg font-bold text-blue-600">
          {{ formatPrice(product.sale_price || product.base_price) }}
        </span>
        <span
          v-if="product.sale_price && product.sale_price < product.base_price"
          class="text-sm text-gray-400 line-through"
        >
          {{ formatPrice(product.base_price) }}
        </span>
      </div>

      <!-- Add to cart button -->
      <button
        @click.prevent="$emit('add-to-cart', product)"
        class="w-full flex items-center justify-center gap-2 py-2 px-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
      >
        <ShoppingCartIcon class="w-4 h-4" />
        Thêm vào giỏ
      </button>
    </div>
  </div>
</template>
