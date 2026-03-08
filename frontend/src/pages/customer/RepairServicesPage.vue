<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import {
  WrenchScrewdriverIcon,
  ClockIcon,
  ShieldCheckIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const services = ref([])
const loading = ref(true)

function formatPrice(price) {
  if (!price) return 'Liên hệ'
  return price.toLocaleString('vi-VN') + '₫'
}

async function fetchServices() {
  loading.value = true
  try {
    const { data } = await api.get('/repair-services')
    services.value = data.data || []
  } catch {
    services.value = []
  } finally {
    loading.value = false
  }
}

onMounted(fetchServices)

const highlights = [
  { icon: WrenchScrewdriverIcon, title: 'Kỹ thuật viên chuyên nghiệp', desc: 'Đội ngũ có kinh nghiệm 5+ năm, được đào tạo bài bản' },
  { icon: ClockIcon, title: 'Thời gian nhanh chóng', desc: 'Sửa trong ngày với các lỗi thông thường' },
  { icon: ShieldCheckIcon, title: 'Bảo hành linh kiện', desc: 'Tất cả linh kiện được bảo hành từ 3-12 tháng' },
]
</script>

<template>
  <div>
    <!-- Hero -->
    <section class="bg-gradient-to-br from-gray-800 to-gray-900 text-white py-16 px-4">
      <div class="max-w-5xl mx-auto text-center">
        <WrenchScrewdriverIcon class="w-14 h-14 text-orange-400 mx-auto mb-4" />
        <h1 class="text-3xl md:text-4xl font-bold mb-3">Dịch vụ sửa chữa máy tính</h1>
        <p class="text-gray-300 text-lg mb-6 max-w-xl mx-auto">
          Chuyên sửa laptop, máy tính để bàn, màn hình. Đội ngũ kỹ thuật viên dày dạn kinh nghiệm.
        </p>
        <div class="flex flex-wrap gap-3 justify-center">
          <RouterLink
            to="/sua-chua/dat-lich"
            class="bg-orange-500 text-white font-semibold px-6 py-3 rounded-xl hover:bg-orange-600 transition-colors"
          >
            Đặt lịch sửa chữa
          </RouterLink>
          <RouterLink
            to="/sua-chua/tra-cuu"
            class="border-2 border-white/50 text-white font-semibold px-6 py-3 rounded-xl hover:bg-white/10 transition-colors flex items-center gap-2"
          >
            <MagnifyingGlassIcon class="w-4 h-4" />
            Tra cứu đơn
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Highlights -->
    <section class="py-10 bg-white">
      <div class="max-w-5xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="item in highlights" :key="item.title" class="text-center p-5">
            <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center mx-auto mb-3">
              <component :is="item.icon" class="w-6 h-6 text-orange-600" />
            </div>
            <h3 class="font-semibold text-gray-800 mb-1">{{ item.title }}</h3>
            <p class="text-sm text-gray-500">{{ item.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Services list -->
    <section class="py-10 bg-gray-50">
      <div class="max-w-5xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Bảng giá dịch vụ</h2>

        <div v-if="loading" class="flex justify-center py-10">
          <LoadingSpinner size="lg" />
        </div>

        <div v-else-if="services.length === 0" class="text-center py-10 text-gray-500">
          Đang cập nhật bảng giá. Vui lòng liên hệ hotline để biết thêm chi tiết.
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="service in services"
            :key="service.id"
            class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1">
                <h3 class="font-semibold text-gray-800 mb-1">{{ service.name }}</h3>
                <p v-if="service.description" class="text-sm text-gray-500 mb-2">{{ service.description }}</p>
              </div>
              <div class="text-right shrink-0">
                <p class="font-bold text-orange-600 text-sm">{{ formatPrice(service.base_price) }}</p>
                <p v-if="service.estimated_duration" class="text-xs text-gray-400 mt-0.5">
                  ~{{ service.estimated_duration }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- CTA -->
        <div class="mt-8 text-center">
          <RouterLink
            to="/sua-chua/dat-lich"
            class="inline-block bg-orange-500 text-white font-semibold px-8 py-3 rounded-xl hover:bg-orange-600 transition-colors"
          >
            Đặt lịch sửa chữa ngay
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>
