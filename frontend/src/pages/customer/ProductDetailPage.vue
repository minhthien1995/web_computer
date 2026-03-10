<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import api from '@/services/api'
import { useCartStore } from '@/stores/cart'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'
import ProductImageGallery from '@/components/product/ProductImageGallery.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import {
  ShoppingCartIcon,
  BoltIcon,
  MinusIcon,
  PlusIcon,
  TagIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const cart = useCartStore()
const ui = useUiStore()
const auth = useAuthStore()

const product = ref(null)
const loading = ref(true)
const quantity = ref(1)
const selectedVariant = ref(null)
const addingToCart = ref(false)
const showAllSpecs = ref(false)

// Group specs by spec_group, preserving order
const groupedSpecs = computed(() => {
  const specs = product.value?.specs || []
  if (!specs.length) return []
  const groups = []
  const groupMap = new Map()
  for (const spec of specs) {
    const group = spec.spec_group || 'Thông tin chung'
    if (!groupMap.has(group)) {
      const entry = { group, items: [] }
      groupMap.set(group, entry)
      groups.push(entry)
    }
    groupMap.get(group).items.push(spec)
  }
  return groups
})

function formatPrice(price) {
  return price?.toLocaleString('vi-VN') + '₫'
}

const displayPrice = computed(() => {
  if (selectedVariant.value) {
    return selectedVariant.value.price || product.value?.sale_price || product.value?.base_price
  }
  return product.value?.sale_price || product.value?.base_price
})

async function fetchProduct() {
  loading.value = true
  try {
    const { data } = await api.get(`/products/${route.params.slug}`)
    product.value = data.data
    if (product.value?.variants?.length > 0) {
      selectedVariant.value = product.value.variants[0]
    }
  } catch {
    ui.error('Không tìm thấy sản phẩm')
    router.push('/san-pham')
  } finally {
    loading.value = false
  }
}

function adjustQty(delta) {
  const newQty = quantity.value + delta
  if (newQty >= 1 && newQty <= 99) quantity.value = newQty
}

async function handleAddToCart() {
  if (!auth.isAuthenticated) {
    ui.info('Vui lòng đăng nhập để thêm vào giỏ hàng')
    router.push('/dang-nhap')
    return
  }

  addingToCart.value = true
  try {
    await cart.addItem(product.value.id, selectedVariant.value?.id || null, quantity.value)
    ui.success('Đã thêm vào giỏ hàng!')
  } catch {
    ui.error('Không thể thêm vào giỏ hàng')
  } finally {
    addingToCart.value = false
  }
}

async function handleBuyNow() {
  await handleAddToCart()
  if (auth.isAuthenticated) {
    router.push('/gio-hang')
  }
}

onMounted(fetchProduct)
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <LoadingSpinner size="lg" />
    </div>

    <template v-else-if="product">
      <!-- Breadcrumb -->
      <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2">
        <RouterLink to="/" class="hover:text-blue-600">Trang chủ</RouterLink>
        <span>/</span>
        <RouterLink to="/san-pham" class="hover:text-blue-600">Sản phẩm</RouterLink>
        <span>/</span>
        <span class="text-gray-800">{{ product.name }}</span>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        <!-- Image Gallery -->
        <ProductImageGallery :images="product.images || []" />

        <!-- Product Info -->
        <div>
          <!-- Category & Brand -->
          <div class="flex items-center gap-2 mb-3">
            <span v-if="product.brand" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">
              {{ product.brand?.name || product.brand }}
            </span>
            <span v-if="product.category" class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
              {{ product.category?.name || product.category }}
            </span>
          </div>

          <h1 class="text-2xl font-bold text-gray-900 mb-4 leading-snug">{{ product.name }}</h1>

          <!-- Price -->
          <div class="flex items-end gap-3 mb-6">
            <span class="text-3xl font-bold text-blue-600">{{ formatPrice(displayPrice) }}</span>
            <span
              v-if="product.sale_price && product.sale_price < product.base_price"
              class="text-lg text-gray-400 line-through"
            >
              {{ formatPrice(product.base_price) }}
            </span>
            <span
              v-if="product.sale_price && product.sale_price < product.base_price"
              class="text-sm font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded"
            >
              -{{ Math.round((1 - product.sale_price / product.base_price) * 100) }}%
            </span>
          </div>

          <!-- Variants -->
          <div v-if="product.variants && product.variants.length > 0" class="mb-6">
            <p class="text-sm font-medium text-gray-700 mb-2">Phiên bản:</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="variant in product.variants"
                :key="variant.id"
                @click="selectedVariant = variant"
                class="px-3 py-1.5 text-sm rounded-lg border-2 transition-all"
                :class="selectedVariant?.id === variant.id
                  ? 'border-blue-500 bg-blue-50 text-blue-700 font-medium'
                  : 'border-gray-200 hover:border-gray-400 text-gray-700'"
              >
                {{ variant.name || variant.sku }}
              </button>
            </div>
          </div>

          <!-- Quantity -->
          <div class="flex items-center gap-4 mb-6">
            <p class="text-sm font-medium text-gray-700">Số lượng:</p>
            <div class="flex items-center gap-2 border border-gray-300 rounded-lg overflow-hidden">
              <button
                @click="adjustQty(-1)"
                :disabled="quantity <= 1"
                class="p-2 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <MinusIcon class="w-4 h-4" />
              </button>
              <input
                v-model.number="quantity"
                type="number"
                min="1"
                max="99"
                class="w-12 text-center text-sm font-medium focus:outline-none border-0"
              />
              <button
                @click="adjustQty(1)"
                :disabled="quantity >= 99"
                class="p-2 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <PlusIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Action buttons -->
          <div class="flex gap-3 mb-6">
            <button
              @click="handleAddToCart"
              :disabled="addingToCart"
              class="flex-1 flex items-center justify-center gap-2 py-3 border-2 border-blue-600 text-blue-600 font-semibold rounded-xl hover:bg-blue-50 transition-colors disabled:opacity-60"
            >
              <ShoppingCartIcon class="w-5 h-5" />
              {{ addingToCart ? 'Đang thêm...' : 'Thêm vào giỏ' }}
            </button>
            <button
              @click="handleBuyNow"
              :disabled="addingToCart"
              class="flex-1 flex items-center justify-center gap-2 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors disabled:opacity-60"
            >
              <BoltIcon class="w-5 h-5" />
              Mua ngay
            </button>
          </div>

          <!-- Stock info -->
          <div v-if="product.stock_qty != null" class="flex items-center gap-2 text-sm text-gray-500">
            <TagIcon class="w-4 h-4" />
            <span>Còn lại: {{ product.stock_qty }} sản phẩm</span>
          </div>
        </div>
      </div>

      <!-- Specs & Description (thegioididong-style) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Specs (left, prominent like TGDD) -->
        <div v-if="groupedSpecs.length > 0" class="lg:col-span-2 order-1">
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <h2 class="text-lg font-bold text-gray-800 px-6 pt-6 pb-3">Thông số kỹ thuật</h2>
            <div :class="{ 'max-h-[400px] overflow-hidden relative': !showAllSpecs && groupedSpecs.length > 2 }">
              <table class="w-full text-sm">
                <tbody>
                  <template v-for="(group, gIdx) in groupedSpecs" :key="gIdx">
                    <!-- Group header -->
                    <tr class="bg-gray-50">
                      <td colspan="2" class="px-6 py-2.5 font-semibold text-gray-700 text-sm">
                        {{ group.group }}
                      </td>
                    </tr>
                    <!-- Spec rows with alternating background -->
                    <tr
                      v-for="(spec, sIdx) in group.items"
                      :key="`${gIdx}-${sIdx}`"
                      :class="sIdx % 2 === 0 ? 'bg-white' : 'bg-gray-50/50'"
                    >
                      <td class="px-6 py-2.5 text-gray-500 w-2/5 align-top">{{ spec.spec_key }}</td>
                      <td class="px-6 py-2.5 text-gray-800">{{ spec.spec_value }}</td>
                    </tr>
                  </template>
                </tbody>
              </table>
              <!-- Gradient fade when collapsed -->
              <div
                v-if="!showAllSpecs && groupedSpecs.length > 2"
                class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-white to-transparent"
              />
            </div>
            <!-- Toggle button -->
            <div v-if="groupedSpecs.length > 2" class="px-6 py-3 text-center border-t border-gray-100">
              <button
                @click="showAllSpecs = !showAllSpecs"
                class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700"
              >
                {{ showAllSpecs ? 'Thu gọn' : 'Xem thêm thông số' }}
                <ChevronDownIcon class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showAllSpecs }" />
              </button>
            </div>
          </div>
        </div>

        <!-- Description (right sidebar or full width) -->
        <div :class="groupedSpecs.length > 0 ? 'lg:col-span-1 order-2' : 'lg:col-span-3 order-1'">
          <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Mô tả sản phẩm</h2>
            <div
              class="prose prose-sm max-w-none text-gray-600 leading-relaxed"
              v-html="product.description || 'Chưa có mô tả sản phẩm.'"
            />
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
