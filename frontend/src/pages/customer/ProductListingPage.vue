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
import { MagnifyingGlassIcon, FunnelIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const cart = useCartStore()
const ui = useUiStore()
const auth = useAuthStore()

const products = ref([])
const categories = ref([])
const brands = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 16 })
const loading = ref(false)
const showFilters = ref(false)

// Filters state (synced with URL)
const filters = ref({
  search: route.query.search || '',
  category_slug: route.query.category || '',
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

async function fetchProducts() {
  loading.value = true
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.category_slug) params.category_slug = filters.value.category_slug
    if (filters.value.brand_slug) params.brand_slug = filters.value.brand_slug
    if (filters.value.sort) params.sort = filters.value.sort
    if (filters.value.page > 1) params.page = filters.value.page

    const { data } = await api.get('/products', { params })
    const paginator = data.data || {}
    products.value = paginator.data || []
    if (paginator.current_page !== undefined) {
      meta.value = {
        current_page: paginator.current_page,
        last_page: paginator.last_page,
        total: paginator.total,
        per_page: paginator.per_page,
      }
    }
  } catch {
    ui.error('Không thể tải sản phẩm')
  } finally {
    loading.value = false
  }
}

async function fetchFiltersData() {
  try {
    const [catsRes, brandsRes] = await Promise.all([
      api.get('/categories'),
      api.get('/brands').catch(() => ({ data: { data: [] } })),
    ])
    categories.value = catsRes.data.data || []
    brands.value = brandsRes.data.data || []
  } catch {
    // ignore
  }
}

function syncUrl() {
  const query = {}
  if (filters.value.search) query.search = filters.value.search
  if (filters.value.category_slug) query.category = filters.value.category_slug
  if (filters.value.brand_slug) query.brand = filters.value.brand_slug
  if (filters.value.sort !== 'newest') query.sort = filters.value.sort
  if (filters.value.page > 1) query.page = filters.value.page
  router.replace({ query })
}

function applyFilter() {
  filters.value.page = 1
  syncUrl()
  fetchProducts()
}

function clearFilters() {
  filters.value = { search: '', category_slug: '', brand_slug: '', sort: 'newest', page: 1 }
  syncUrl()
  fetchProducts()
}

function onPageChange(page) {
  filters.value.page = page
  syncUrl()
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

const hasActiveFilters = computed(() => {
  return filters.value.search || filters.value.category_slug || filters.value.brand_slug
})

onMounted(() => {
  fetchFiltersData()
  fetchProducts()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Sản phẩm</h1>
      <button
        @click="showFilters = !showFilters"
        class="md:hidden flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm"
      >
        <FunnelIcon class="w-4 h-4" />
        Bộ lọc
      </button>
    </div>

    <!-- Search bar -->
    <div class="relative mb-6">
      <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
      <input
        v-model="filters.search"
        type="text"
        placeholder="Tìm kiếm sản phẩm..."
        class="w-full border border-gray-300 rounded-lg pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        @keyup.enter="applyFilter"
      />
    </div>

    <div class="flex gap-6">
      <!-- Filters Sidebar -->
      <aside
        class="w-56 shrink-0"
        :class="showFilters ? 'block' : 'hidden md:block'"
      >
        <!-- Sort (mobile) -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-4 shadow-sm">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Sắp xếp</h3>
          <select
            v-model="filters.sort"
            @change="applyFilter"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>

        <!-- Category filter -->
        <div class="bg-white rounded-xl border border-gray-100 p-4 mb-4 shadow-sm">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Danh mục</h3>
          <div class="space-y-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="radio"
                v-model="filters.category_slug"
                value=""
                @change="applyFilter"
                class="text-blue-600"
              />
              <span class="text-sm text-gray-700">Tất cả</span>
            </label>
            <label
              v-for="cat in categories"
              :key="cat.id"
              class="flex items-center gap-2 cursor-pointer"
            >
              <input
                type="radio"
                v-model="filters.category_slug"
                :value="cat.slug"
                @change="applyFilter"
                class="text-blue-600"
              />
              <span class="text-sm text-gray-700">{{ cat.name }}</span>
            </label>
          </div>
        </div>

        <!-- Brand filter -->
        <div v-if="brands.length > 0" class="bg-white rounded-xl border border-gray-100 p-4 mb-4 shadow-sm">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Thương hiệu</h3>
          <div class="space-y-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="radio"
                v-model="filters.brand_slug"
                value=""
                @change="applyFilter"
                class="text-blue-600"
              />
              <span class="text-sm text-gray-700">Tất cả</span>
            </label>
            <label
              v-for="brand in brands"
              :key="brand.id"
              class="flex items-center gap-2 cursor-pointer"
            >
              <input
                type="radio"
                v-model="filters.brand_slug"
                :value="brand.slug"
                @change="applyFilter"
                class="text-blue-600"
              />
              <span class="text-sm text-gray-700">{{ brand.name }}</span>
            </label>
          </div>
        </div>

        <!-- Clear filters -->
        <button
          v-if="hasActiveFilters"
          @click="clearFilters"
          class="w-full flex items-center justify-center gap-2 py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50"
        >
          <XMarkIcon class="w-4 h-4" />
          Xóa bộ lọc
        </button>
      </aside>

      <!-- Products area -->
      <div class="flex-1 min-w-0">
        <!-- Sort bar (desktop) -->
        <div class="hidden md:flex items-center justify-between mb-4">
          <p class="text-sm text-gray-500">{{ meta.total }} sản phẩm</p>
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">Sắp xếp:</span>
            <select
              v-model="filters.sort"
              @change="applyFilter"
              class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-20">
          <LoadingSpinner size="lg" />
        </div>

        <!-- Empty -->
        <EmptyState
          v-else-if="products.length === 0"
          icon="🔍"
          title="Không tìm thấy sản phẩm"
          message="Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm"
        />

        <!-- Products grid -->
        <div v-else class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
          <ProductCard
            v-for="product in products"
            :key="product.id"
            :product="product"
            @add-to-cart="addToCart"
          />
        </div>

        <!-- Pagination -->
        <Pagination
          v-if="!loading && meta.last_page > 1"
          :meta="meta"
          @page-change="onPageChange"
        />
      </div>
    </div>
  </div>
</template>
