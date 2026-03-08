<script setup>
import { useRoute, RouterLink } from 'vue-router'
import { CheckCircleIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const orderNumber = route.params.orderNo
</script>

<template>
  <div class="max-w-lg mx-auto px-4 py-20 text-center">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10">
      <CheckCircleIcon class="w-20 h-20 text-green-500 mx-auto mb-6" />
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Đặt hàng thành công!</h1>
      <p class="text-gray-500 mb-4">Cảm ơn bạn đã mua sắm tại Web Computer Store</p>

      <div class="bg-blue-50 rounded-xl p-4 mb-6">
        <p class="text-sm text-gray-600 mb-1">Mã đơn hàng</p>
        <p class="text-xl font-bold text-blue-600">{{ orderNumber }}</p>
      </div>

      <p class="text-sm text-gray-500 mb-8">
        Chúng tôi sẽ liên hệ xác nhận và giao hàng trong thời gian sớm nhất.
        Bạn có thể theo dõi trạng thái đơn hàng trong trang tài khoản.
      </p>

      <div class="flex flex-col sm:flex-row gap-3">
        <RouterLink
          :to="`/tai-khoan/don-hang/${orderNumber}`"
          class="flex-1 bg-blue-600 text-white py-2.5 rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors"
        >
          Xem đơn hàng
        </RouterLink>
        <RouterLink
          to="/san-pham"
          class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
        >
          Tiếp tục mua sắm
        </RouterLink>
      </div>
    </div>
  </div>
</template>
