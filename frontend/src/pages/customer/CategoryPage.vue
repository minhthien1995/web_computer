<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import { useCartStore } from '@/stores/cart'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'
import ProductCard from '@/components/common/ProductCard.vue'
import Pagination from '@/components/common/Pagination.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import EmptyState from '@/components/common/EmptyState.vue'

const route = useRoute()
const router = useRouter()
const cart = useCartStore()
const ui = useUiStore()
const auth = useAuthStore()

const products = ref([])
const brands = ref([])
const category = ref(null)
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 16 })
const loading = ref(true)

const filters = ref({
  brand_slug: route.query.brand || '',
  sort: route.query.sort || 'newest',
  page: parseInt(route.query.page) || 1,
})

const sortOptions = [
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá tăng dần' },
  { value: 'price_desc', label: 'Giá giảm dần' },
  { value: 'featured', label: 'Nổi bật' },
]

async function fetchCategory() {
  try {
    const { data } = await api.get(`/categories/${route.params.slug}`)
    category.value = data.data
  } catch {
    // ignore, category info optional
  }
}

async function fetchProducts() {
  loading.value = true
  try {
    const params = {
      category_slug: route.params.slug,
      sort: filters.value.sort,
      page: filters.value.page,
    }
    if (filters.value.brand_slug) params.brand_slug = filters.value.brand_slug

    const { data } = await api.get('/products', { params })
    products.value = data.data?.data || data.data || []
    if (data.data?.meta) meta.value = data.data.meta

    // Extract brands from products if not loaded separately
    if (brands.value.length === 0) {
      const brandSet = new Map()
      products.value.forEach(p => {
        if (p.brand) {
          const b = typeof p.brand === 'object' ? p.brand : { name: p.brand, slug: p.brand_slug }
          if (b.slug) brandSet.set(b.slug, b)
        }
      })
      brands.value = Array.from(brandSet.values())
    }
  } catch {
    ui.error('Không thể tải sản phẩm')
  } finally {
    loading.value = false
  }
}

function applyFilter() {
  filters.value.page = 1
  router.replace({
    query: {
      ...(filters.value.brand_slug ? { brand: filters.value.brand_slug } : {}),
      ...(filters.value.sort !== 'newest' ? { sort: filters.value.sort } : {}),
    },
  })
  fetchProducts()
}

function onPageChange(page) {
  filters.value.page = page
  fetchProducts()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function addToCart(product) {
  if (!auth.isAuthenticated) {
    ui.info('Vui lòng đăng nhập để thêm vào giỏ hàng')
    router.push('/dang-nhap')
    return
  }
  try {
    await cart.addItem(product.id, null, 1)
    ui.success(`Đã thêm "${product.name}" vào giỏ hàng!`)
  } catch {
    ui.error('Không thể thêm vào giỏ hàng')
  }
}

// Reload when slug changes
watch(() => route.params.slug, () => {
  filters.value = { brand_slug: '', sort: 'newest', page: 1 }
  fetchCategory()
  fetchProducts()
})

onMounted(() => {
  fetchCategory()
  fetchProducts()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Category header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">
        {{ category?.name || 'Danh mục' }}
      </h1>
      <p v-if="category?.description" class="text-gray-500 text-sm mt-1">{{ category.description }}</p>
    </div>

    <!-- Filter bar -->
    <div class="flex flex-wrap items-center gap-3 mb-6 bg-white border border-gray-100 rounded-xl p-3 shadow-sm">
      <!-- Brand filter -->
      <div v-if="brands.length > 0" class="flex items-center gap-2">
        <span class="text-sm text-gray-500">Thương hiệu:</span>
        <select
          v-model="filters.brand_slug"
          @change="applyFilter"
          class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Tất cả</option>
          <option v-for="brand in brands" :key="brand.slug" :value="brand.slug">{{ brand.name }}</option>
        </select>
      </div>

      <!-- Sort -->
      <div class="flex items-center gap-2 ml-auto">
        <span class="text-sm text-gray-500">Sắp xếp:</span>
        <select
          v-model="filters.sort"
          @change="applyFilter"
          class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </div>

      <!-- Count -->
      <span class="text-sm text-gray-400">{{ meta.total }} sản phẩm</span>
    </div>

    <!-- Products -->
    <div v-if="loading" class="flex justify-center py-16">
      <LoadingSpinner size="lg" />
    </div>

    <EmptyState
      v-else-if="products.length === 0"
      icon="📦"
      title="Không có sản phẩm"
      message="Danh mục này hiện chưa có sản phẩm"
    />

    <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
        @add-to-cart="addToCart"
      />
    </div>

    <Pagination
      v-if="!loading && meta.last_page > 1"
      :meta="meta"
      @page-change="onPageChange"
    />
  </div>
</template>
