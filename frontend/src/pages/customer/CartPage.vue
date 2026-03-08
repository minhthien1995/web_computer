<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'
import { useDebounceFn } from '@vueuse/core'
import EmptyState from '@/components/common/EmptyState.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import {
  TrashIcon,
  ShoppingBagIcon,
  MinusIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const cart = useCartStore()
const ui = useUiStore()
const auth = useAuthStore()

const confirmRemove = ref({ show: false, itemId: null, itemName: '' })

function removeConfirmMessage() {
  return 'Bạn có chắc muốn xóa sản phẩm ' + confirmRemove.value.itemName + ' khỏi giỏ hàng?'
}

function formatPrice(price) {
  return price?.toLocaleString('vi-VN') + '₫'
}

// Debounced quantity update to avoid too many API calls
const debouncedUpdate = useDebounceFn(async (itemId, quantity) => {
  try {
    await cart.updateItem(itemId, quantity)
  } catch {
    ui.error('Không thể cập nhật số lượng')
    cart.fetchCart()
  }
}, 500)

function onQtyChange(item, delta) {
  const newQty = item.quantity + delta
  if (newQty < 1) return
  item.quantity = newQty
  debouncedUpdate(item.id, newQty)
}

function confirmRemoveItem(item) {
  confirmRemove.value = { show: true, itemId: item.id, itemName: item.product?.name || 'sản phẩm' }
}

async function doRemoveItem() {
  try {
    await cart.removeItem(confirmRemove.value.itemId)
    ui.success('Đã xóa khỏi giỏ hàng')
  } catch {
    ui.error('Không thể xóa sản phẩm')
  }
  confirmRemove.value = { show: false, itemId: null, itemName: '' }
}

function getImageUrl(item) {
  const images = item.product?.images
  if (images && images.length > 0) {
    return images[0].url || images[0]
  }
  return null
}

function goToCheckout() {
  if (!auth.isAuthenticated) {
    ui.info('Vui lòng đăng nhập để thanh toán')
    router.push('/dang-nhap?redirect=/thanh-toan')
    return
  }
  router.push('/thanh-toan')
}
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Giỏ hàng</h1>

    <EmptyState
      v-if="cart.items.length === 0"
      icon="🛒"
      title="Giỏ hàng trống"
      message="Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm"
    >
      <RouterLink
        to="/san-pham"
        class="mt-4 inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
      >
        <ShoppingBagIcon class="w-4 h-4" />
        Tiếp tục mua sắm
      </RouterLink>
    </EmptyState>

    <div v-else class="flex flex-col lg:flex-row gap-6">
      <!-- Cart items -->
      <div class="flex-1 space-y-3">
        <div
          v-for="item in cart.items"
          :key="item.id"
          class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex gap-4"
        >
          <!-- Product image -->
          <RouterLink :to="`/san-pham/${item.product?.slug}`" class="flex-shrink-0">
            <div class="w-20 h-20 rounded-lg bg-gray-100 overflow-hidden">
              <img
                v-if="getImageUrl(item)"
                :src="getImageUrl(item)"
                :alt="item.product?.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-2xl">🖥️</div>
            </div>
          </RouterLink>

          <!-- Product details -->
          <div class="flex-1 min-w-0">
            <RouterLink :to="`/san-pham/${item.product?.slug}`">
              <h3 class="text-sm font-medium text-gray-800 hover:text-blue-600 line-clamp-2">
                {{ item.product?.name }}
              </h3>
            </RouterLink>
            <p v-if="item.variant" class="text-xs text-gray-500 mt-0.5">{{ item.variant.name }}</p>
            <p class="text-sm font-bold text-blue-600 mt-1">{{ formatPrice(item.unit_price) }}</p>
          </div>

          <!-- Qty controls & remove -->
          <div class="flex flex-col items-end justify-between gap-2">
            <button
              @click="confirmRemoveItem(item)"
              class="text-gray-400 hover:text-red-500 transition-colors"
            >
              <TrashIcon class="w-4 h-4" />
            </button>

            <div class="flex items-center gap-1 border border-gray-200 rounded-lg overflow-hidden">
              <button
                @click="onQtyChange(item, -1)"
                :disabled="item.quantity <= 1"
                class="p-1.5 hover:bg-gray-100 disabled:opacity-40"
              >
                <MinusIcon class="w-3 h-3" />
              </button>
              <span class="w-8 text-center text-sm font-medium">{{ item.quantity }}</span>
              <button
                @click="onQtyChange(item, 1)"
                class="p-1.5 hover:bg-gray-100"
              >
                <PlusIcon class="w-3 h-3" />
              </button>
            </div>

            <p class="text-sm font-semibold text-gray-800">
              {{ formatPrice(item.unit_price * item.quantity) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Order summary -->
      <div class="lg:w-72 shrink-0">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sticky top-20">
          <h2 class="text-base font-bold text-gray-800 mb-4">Tóm tắt đơn hàng</h2>

          <div class="space-y-2 text-sm mb-4">
            <div class="flex justify-between text-gray-600">
              <span>Tạm tính ({{ cart.itemCount }} sản phẩm)</span>
              <span>{{ formatPrice(cart.subtotal) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Phí vận chuyển</span>
              <span class="text-green-600">Tính sau</span>
            </div>
          </div>

          <div class="border-t border-gray-100 pt-3 mb-4">
            <div class="flex justify-between font-bold text-gray-800">
              <span>Tổng cộng</span>
              <span class="text-blue-600 text-lg">{{ formatPrice(cart.subtotal) }}</span>
            </div>
          </div>

          <button
            @click="goToCheckout"
            class="w-full bg-blue-600 text-white py-3 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors mb-3"
          >
            Thanh toán
          </button>

          <RouterLink
            to="/san-pham"
            class="block text-center text-sm text-gray-500 hover:text-blue-600"
          >
            Tiếp tục mua sắm
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Confirm remove modal -->
    <ConfirmModal
      :show="confirmRemove.show"
      title="Xóa sản phẩm"
      :message="removeConfirmMessage()"
      confirm-text="Xóa"
      @confirm="doRemoveItem"
      @cancel="confirmRemove.show = false"
    />
  </div>
</template>
