<script setup>
import { ref, computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import api from '@/services/api'
import { useCartStore } from '@/stores/cart'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { ShoppingBagIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const cart = useCartStore()
const ui = useUiStore()
const auth = useAuthStore()

const SHIPPING_FEE = 30000

const form = ref({
  name: auth.user?.name || '',
  phone: auth.user?.phone || '',
  email: auth.user?.email || '',
  province: '',
  district: '',
  ward: '',
  address_detail: '',
  notes: '',
  payment_method: 'cod',
})

const loading = ref(false)

function formatPrice(price) {
  return price?.toLocaleString('vi-VN') + '₫'
}

const total = computed(() => cart.subtotal + SHIPPING_FEE)

function getImageUrl(item) {
  const images = item.product?.images
  if (images && images.length > 0) return images[0].url || images[0]
  return null
}

async function handleSubmit() {
  if (!form.value.name || !form.value.phone || !form.value.address_detail) {
    ui.error('Vui lòng điền đầy đủ thông tin giao hàng')
    return
  }
  if (cart.items.length === 0) {
    ui.error('Giỏ hàng trống')
    return
  }

  loading.value = true
  try {
    const payload = {
      shipping_address: {
        name: form.value.name,
        phone: form.value.phone,
        email: form.value.email,
        province: form.value.province,
        district: form.value.district,
        ward: form.value.ward,
        address: form.value.address_detail,
      },
      payment_method: form.value.payment_method,
      notes: form.value.notes,
    }

    const { data } = await api.post('/orders', payload)
    const orderNumber = data.data?.order_number || data.data?.id
    await cart.clearCart()
    router.push(`/thanh-toan/thanh-cong/${orderNumber}`)
  } catch (err) {
    const msg = err.response?.data?.message || 'Đặt hàng thất bại, vui lòng thử lại'
    ui.error(msg)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Thanh toán</h1>

    <!-- Empty cart redirect -->
    <div v-if="cart.items.length === 0" class="text-center py-16">
      <ShoppingBagIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <p class="text-gray-500 mb-4">Giỏ hàng trống</p>
      <RouterLink to="/san-pham" class="text-blue-600 hover:underline">Tiếp tục mua sắm</RouterLink>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="grid grid-cols-1 lg:grid-cols-5 gap-6">
      <!-- Left column: Form -->
      <div class="lg:col-span-3 space-y-5">
        <!-- Shipping info -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
          <h2 class="text-base font-bold text-gray-800 mb-4">Thông tin giao hàng</h2>

          <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                <input
                  v-model="form.phone"
                  type="tel"
                  required
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành phố</label>
                <input
                  v-model="form.province"
                  type="text"
                  placeholder="TP. Hồ Chí Minh"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện</label>
                <input
                  v-model="form.district"
                  type="text"
                  placeholder="Quận 1"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phường/Xã</label>
                <input
                  v-model="form.ward"
                  type="text"
                  placeholder="Phường Bến Nghé"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ chi tiết <span class="text-red-500">*</span></label>
              <input
                v-model="form.address_detail"
                type="text"
                required
                placeholder="Số nhà, tên đường..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
              <textarea
                v-model="form.notes"
                rows="2"
                placeholder="Ghi chú cho đơn hàng (tùy chọn)"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
              />
            </div>
          </div>
        </div>

        <!-- Payment method -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
          <h2 class="text-base font-bold text-gray-800 mb-4">Phương thức thanh toán</h2>
          <div class="space-y-3">
            <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all"
              :class="form.payment_method === 'cod' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
            >
              <input v-model="form.payment_method" type="radio" value="cod" class="text-blue-600" />
              <div>
                <p class="text-sm font-medium text-gray-800">💵 Thanh toán khi nhận hàng (COD)</p>
                <p class="text-xs text-gray-500">Trả tiền mặt khi nhận hàng tại nhà</p>
              </div>
            </label>
            <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all"
              :class="form.payment_method === 'bank_transfer' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
            >
              <input v-model="form.payment_method" type="radio" value="bank_transfer" class="text-blue-600" />
              <div>
                <p class="text-sm font-medium text-gray-800">🏦 Chuyển khoản ngân hàng</p>
                <p class="text-xs text-gray-500">Chuyển khoản sau khi đặt hàng</p>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Right column: Order summary -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sticky top-20">
          <h2 class="text-base font-bold text-gray-800 mb-4">Đơn hàng ({{ cart.itemCount }} sản phẩm)</h2>

          <!-- Items list -->
          <div class="space-y-3 mb-4 max-h-60 overflow-y-auto">
            <div v-for="item in cart.items" :key="item.id" class="flex gap-3">
              <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                <img v-if="getImageUrl(item)" :src="getImageUrl(item)" :alt="item.product?.name" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-lg">🖥️</div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-800 line-clamp-2">{{ item.product?.name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">x{{ item.quantity }}</p>
              </div>
              <p class="text-xs font-semibold text-gray-800 shrink-0">
                {{ (item.unit_price * item.quantity).toLocaleString('vi-VN') }}₫
              </p>
            </div>
          </div>

          <!-- Pricing -->
          <div class="border-t border-gray-100 pt-3 space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
              <span>Tạm tính</span>
              <span>{{ formatPrice(cart.subtotal) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Phí vận chuyển</span>
              <span>{{ formatPrice(SHIPPING_FEE) }}</span>
            </div>
          </div>

          <div class="border-t border-gray-100 mt-3 pt-3 mb-4">
            <div class="flex justify-between font-bold text-gray-800">
              <span>Tổng cộng</span>
              <span class="text-blue-600 text-lg">{{ formatPrice(total) }}</span>
            </div>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-blue-600 text-white py-3 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors disabled:opacity-60 flex items-center justify-center gap-2"
          >
            <LoadingSpinner v-if="loading" size="sm" color="white" />
            {{ loading ? 'Đang xử lý...' : 'Đặt hàng' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>
