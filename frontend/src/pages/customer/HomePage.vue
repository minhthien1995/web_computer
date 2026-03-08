<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import api from '@/services/api'
import { useCartStore } from '@/stores/cart'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'
import ProductCard from '@/components/common/ProductCard.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import {
  ShieldCheckIcon,
  WrenchScrewdriverIcon,
  TruckIcon,
  PhoneIcon,
  ArrowRightIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const cart = useCartStore()
const ui = useUiStore()
const auth = useAuthStore()

const featuredProducts = ref([])
const categories = ref([])
const loadingProducts = ref(true)
const loadingCategories = ref(true)

const categoryIcons = {
  laptop: '💻',
  'may-tinh-de-ban': '🖥️',
  'man-hinh': '🖥️',
  'ban-phim': '⌨️',
  chuot: '🖱️',
  'linh-kien': '🔧',
  'tai-nghe': '🎧',
  'luu-tru': '💾',
  default: '📦',
}

function getCategoryIcon(slug) {
  return categoryIcons[slug] || categoryIcons.default
}

async function fetchFeaturedProducts() {
  try {
    const { data } = await api.get('/featured-products')
    featuredProducts.value = data.data || []
  } catch {
    featuredProducts.value = []
  } finally {
    loadingProducts.value = false
  }
}

async function fetchCategories() {
  try {
    const { data } = await api.get('/categories')
    categories.value = data.data || []
  } catch {
    categories.value = []
  } finally {
    loadingCategories.value = false
  }
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

onMounted(() => {
  fetchFeaturedProducts()
  fetchCategories()
})

const features = [
  { icon: ShieldCheckIcon, title: 'Hàng chính hãng 100%', desc: 'Sản phẩm có tem nhãn, hóa đơn đầy đủ từ nhà phân phối chính thức' },
  { icon: WrenchScrewdriverIcon, title: 'Bảo hành tận nhà', desc: 'Đội ngũ kỹ thuật viên tới tận nơi, hỗ trợ nhanh chóng trong 24h' },
  { icon: TruckIcon, title: 'Giao hàng nhanh', desc: 'Giao hàng trong ngày tại TP.HCM, 1-3 ngày toàn quốc' },
  { icon: PhoneIcon, title: 'Hỗ trợ 24/7', desc: 'Tư vấn chuyên nghiệp qua hotline, chat trực tiếp bất kỳ lúc nào' },
]
</script>

<template>
  <div>
    <!-- Hero Banner -->
    <section class="bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700 text-white py-20 px-4">
      <div class="max-w-7xl mx-auto text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">
          Web Computer Store
        </h1>
        <p class="text-blue-100 text-xl mb-3">Máy tính chính hãng - Giá tốt nhất</p>
        <p class="text-blue-200 text-base mb-8 max-w-xl mx-auto">
          Hơn 1000+ sản phẩm công nghệ chính hãng. Bảo hành toàn quốc. Giao hàng tận nơi.
        </p>
        <div class="flex flex-wrap gap-4 justify-center">
          <RouterLink
            to="/san-pham"
            class="bg-white text-blue-700 font-semibold px-6 py-3 rounded-xl hover:bg-blue-50 transition-colors flex items-center gap-2"
          >
            Mua ngay
            <ArrowRightIcon class="w-4 h-4" />
          </RouterLink>
          <RouterLink
            to="/sua-chua"
            class="border-2 border-white text-white font-semibold px-6 py-3 rounded-xl hover:bg-white/10 transition-colors"
          >
            Xem dịch vụ sửa chữa
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section class="py-12 bg-white">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Danh mục sản phẩm</h2>

        <div v-if="loadingCategories" class="flex justify-center py-8">
          <LoadingSpinner />
        </div>

        <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
          <RouterLink
            v-for="cat in categories.slice(0, 12)"
            :key="cat.id"
            :to="`/danh-muc/${cat.slug}`"
            class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-100 hover:border-blue-300 hover:bg-blue-50 transition-all group"
          >
            <span class="text-3xl">{{ getCategoryIcon(cat.slug) }}</span>
            <span class="text-xs font-medium text-gray-700 group-hover:text-blue-600 text-center leading-tight">
              {{ cat.name }}
            </span>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="py-12 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-800">Sản phẩm nổi bật</h2>
          <RouterLink to="/san-pham" class="text-blue-600 text-sm font-medium hover:underline flex items-center gap-1">
            Xem tất cả <ArrowRightIcon class="w-4 h-4" />
          </RouterLink>
        </div>

        <div v-if="loadingProducts" class="flex justify-center py-12">
          <LoadingSpinner size="lg" />
        </div>

        <div v-else-if="featuredProducts.length === 0" class="text-center py-12 text-gray-500">
          Chưa có sản phẩm nổi bật
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          <ProductCard
            v-for="product in featuredProducts.slice(0, 8)"
            :key="product.id"
            :product="product"
            @add-to-cart="addToCart"
          />
        </div>
      </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-12 bg-white">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-10">Tại sao chọn chúng tôi?</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="feature in features"
            :key="feature.title"
            class="text-center p-6 rounded-xl border border-gray-100 hover:shadow-md transition-shadow"
          >
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mx-auto mb-4">
              <component :is="feature.icon" class="w-6 h-6 text-blue-600" />
            </div>
            <h3 class="font-semibold text-gray-800 mb-2">{{ feature.title }}</h3>
            <p class="text-gray-500 text-sm leading-relaxed">{{ feature.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Repair CTA -->
    <section class="py-12 bg-gradient-to-r from-orange-500 to-red-500 text-white">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-3">Máy tính bị hỏng?</h2>
        <p class="text-orange-100 mb-6 text-lg">
          Đội ngũ kỹ thuật viên chuyên nghiệp sẵn sàng hỗ trợ bạn. Đặt lịch ngay hôm nay!
        </p>
        <div class="flex gap-4 justify-center">
          <RouterLink
            to="/sua-chua/dat-lich"
            class="bg-white text-orange-600 font-semibold px-6 py-3 rounded-xl hover:bg-orange-50 transition-colors"
          >
            Đặt lịch sửa chữa
          </RouterLink>
          <RouterLink
            to="/sua-chua/tra-cuu"
            class="border-2 border-white text-white font-semibold px-6 py-3 rounded-xl hover:bg-white/10 transition-colors"
          >
            Tra cứu đơn sửa chữa
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>
