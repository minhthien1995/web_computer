<script setup>
import { ref, onMounted } from 'vue'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import { useProductImages } from '@/composables/use-product-images'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import Pagination from '@/components/common/Pagination.vue'
import ProductImageManager from '@/components/admin/product-image-manager.vue'
import ProductSpecManager from '@/components/admin/product-spec-manager.vue'
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, PhotoIcon } from '@heroicons/vue/24/outline'

const ui = useUiStore()
const images = useProductImages()

const products = ref([])
const categories = ref([])
const brands = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 15 })
const loading = ref(true)
const saving = ref(false)

const filters = ref({ search: '', category_id: '', status: '', page: 1 })
const showModal = ref(false)
const editingId = ref(null)
const deleteConfirm = ref({ show: false, id: null, name: '' })

const form = ref({
  name: '', slug: '', category_id: '', brand_id: '',
  base_price: '', sale_price: '', stock_qty: '',
  status: 'active', is_featured: false, description: '',
  specs: [],
})

function resetForm() {
  form.value = {
    name: '', slug: '', category_id: '', brand_id: '',
    base_price: '', sale_price: '', stock_qty: '',
    status: 'active', is_featured: false, description: '',
    specs: [],
  }
  editingId.value = null
  images.reset()
}

function generateSlug(name) {
  return name.toLowerCase()
    .replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g, 'a')
    .replace(/[èéẹẻẽêềếệểễ]/g, 'e')
    .replace(/[ìíịỉĩ]/g, 'i')
    .replace(/[òóọỏõôồốộổỗơờớợởỡ]/g, 'o')
    .replace(/[ùúụủũưừứựửữ]/g, 'u')
    .replace(/[ỳýỵỷỹ]/g, 'y')
    .replace(/đ/g, 'd')
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim()
}

function onNameInput() {
  if (!editingId.value) {
    form.value.slug = generateSlug(form.value.name)
  }
}

function openCreate() {
  resetForm()
  showModal.value = true
}

async function openEdit(product) {
  editingId.value = product.id
  showModal.value = true
  // Fetch full product with specs (index doesn't include specs)
  try {
    const { data } = await api.get(`/admin/products/${product.id}`)
    const p = data.data || product
    form.value = {
      name: p.name, slug: p.slug,
      category_id: p.category_id || p.category?.id || '',
      brand_id: p.brand_id || p.brand?.id || '',
      base_price: p.base_price, sale_price: p.sale_price || '',
      stock_qty: p.stock_qty ?? '', status: p.status || 'active',
      is_featured: p.is_featured || false, description: p.description || '',
      specs: (p.specs || []).map(s => ({
        spec_group: s.spec_group, spec_key: s.spec_key, spec_value: s.spec_value,
      })),
    }
    images.loadExisting(p.images)
  } catch {
    form.value = {
      name: product.name, slug: product.slug,
      category_id: product.category_id || '', brand_id: product.brand_id || '',
      base_price: product.base_price, sale_price: product.sale_price || '',
      stock_qty: product.stock_qty ?? '', status: product.status || 'active',
      is_featured: product.is_featured || false, description: product.description || '',
      specs: [],
    }
    images.loadExisting(product.images)
  }
}

function formatPrice(price) {
  return price?.toLocaleString('vi-VN') + '₫'
}

async function fetchProducts() {
  loading.value = true
  try {
    const params = { page: filters.value.page }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.category_id) params.category_id = filters.value.category_id
    if (filters.value.status) params.status = filters.value.status

    const { data } = await api.get('/admin/products', { params })
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
    ui.error('Không thể tải danh sách sản phẩm')
  } finally {
    loading.value = false
  }
}

async function fetchFilterData() {
  try {
    const [catsRes, brandsRes] = await Promise.all([
      api.get('/categories'),
      api.get('/brands').catch(() => ({ data: { data: [] } })),
    ])
    categories.value = catsRes.data.data || []
    brands.value = brandsRes.data.data || []
  } catch { /* ignore */ }
}

async function saveProduct() {
  if (!form.value.name || !form.value.base_price) {
    ui.error('Vui lòng điền tên và giá sản phẩm')
    return
  }
  if (!form.value.category_id) {
    ui.error('Vui lòng chọn danh mục sản phẩm')
    return
  }
  saving.value = true
  try {
    let productId = editingId.value
    if (editingId.value) {
      await api.put(`/admin/products/${editingId.value}`, form.value)
      ui.success('Cập nhật sản phẩm thành công!')
    } else {
      const { data } = await api.post('/admin/products', form.value)
      productId = data.data?.id
      ui.success('Tạo sản phẩm thành công!')
    }
    if (productId && images.hasPending()) {
      await images.uploadAll(productId)
    }
    showModal.value = false
    fetchProducts()
  } catch (err) {
    ui.error(err.response?.data?.message || 'Lưu thất bại')
  } finally {
    saving.value = false
  }
}

function confirmDelete(product) {
  deleteConfirm.value = { show: true, id: product.id, name: product.name }
}

async function deleteProduct() {
  try {
    await api.delete(`/admin/products/${deleteConfirm.value.id}`)
    ui.success('Đã xóa sản phẩm')
    fetchProducts()
  } catch {
    ui.error('Không thể xóa sản phẩm')
  }
  deleteConfirm.value = { show: false, id: null, name: '' }
}

function onSearch() {
  filters.value.page = 1
  fetchProducts()
}

function onPageChange(page) {
  filters.value.page = page
  fetchProducts()
}

onMounted(() => {
  fetchFilterData()
  fetchProducts()
})
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Quản lý sản phẩm</h1>
      <button
        @click="openCreate"
        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
      >
        <PlusIcon class="w-4 h-4" />
        Thêm sản phẩm
      </button>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 mb-5">
      <div class="relative flex-1 min-w-48">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input
          v-model="filters.search"
          type="text"
          placeholder="Tìm sản phẩm..."
          @keyup.enter="onSearch"
          class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
      <select v-model="filters.category_id" @change="onSearch"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Tất cả danh mục</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
      <select v-model="filters.status" @change="onSearch"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Tất cả trạng thái</option>
        <option value="active">Đang bán</option>
        <option value="inactive">Ngừng bán</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div v-if="loading" class="flex justify-center py-10">
        <LoadingSpinner />
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Sản phẩm</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Danh mục</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Giá bán</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Kho</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Trạng thái</th>
              <th class="text-center px-4 py-3 text-gray-500 font-medium">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                    <img v-if="product.images?.[0]?.url" :src="product.images[0].url" :alt="product.name" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center">
                      <PhotoIcon class="w-5 h-5 text-gray-300" />
                    </div>
                  </div>
                  <div class="min-w-0">
                    <p class="font-medium text-gray-800 line-clamp-1">{{ product.name }}</p>
                    <p class="text-xs text-gray-400">{{ product.brand?.name }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ product.category?.name || '—' }}</td>
              <td class="px-4 py-3">
                <p class="font-medium text-gray-800">{{ formatPrice(product.sale_price || product.base_price) }}</p>
                <p v-if="product.sale_price" class="text-xs text-gray-400 line-through">{{ formatPrice(product.base_price) }}</p>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ product.stock_qty ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="text-xs px-2 py-1 rounded-full font-medium"
                  :class="product.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                >
                  {{ product.status === 'active' ? 'Đang bán' : 'Ngừng bán' }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button @click="openEdit(product)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg">
                    <PencilIcon class="w-4 h-4" />
                  </button>
                  <button @click="confirmDelete(product)" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg">
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="products.length === 0">
              <td colspan="6" class="text-center py-10 text-gray-400">Không có sản phẩm</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination v-if="!loading && meta.last_page > 1" :meta="meta" @page-change="onPageChange" />

    <!-- Create/Edit Modal -->
    <Dialog :open="showModal" @close="showModal = false" class="relative z-50">
      <div class="fixed inset-0 bg-black/40" />
      <div class="fixed inset-0 flex items-center justify-center p-4">
        <DialogPanel class="bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <DialogTitle class="text-lg font-bold text-gray-800 mb-5">
              {{ editingId ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới' }}
            </DialogTitle>

            <form @submit.prevent="saveProduct" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên sản phẩm *</label>
                <input v-model="form.name" @input="onNameInput" type="text" required
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input v-model="form.slug" type="text"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Danh mục <span class="text-red-500">*</span></label>
                  <select v-model="form.category_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Chọn danh mục</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Thương hiệu</label>
                  <select v-model="form.brand_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Chọn thương hiệu</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                  </select>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Giá gốc (₫) *</label>
                  <input v-model.number="form.base_price" type="number" min="0" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Giá sale (₫)</label>
                  <input v-model.number="form.sale_price" type="number" min="0"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Tồn kho</label>
                  <input v-model.number="form.stock_qty" type="number" min="0"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                  <select v-model="form.status"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="active">Đang bán</option>
                    <option value="inactive">Ngừng bán</option>
                  </select>
                </div>
              </div>
              <div>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="form.is_featured" type="checkbox" class="rounded text-blue-600" />
                  <span class="text-sm font-medium text-gray-700">Sản phẩm nổi bật</span>
                </label>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả sản phẩm</label>
                <textarea v-model="form.description" rows="3" placeholder="Nhập mô tả sản phẩm..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y" />
              </div>

              <!-- Specs management -->
              <ProductSpecManager v-model:specs="form.specs" />

              <!-- Image management (extracted component) -->
              <ProductImageManager
                :existing-images="images.existingImages.value"
                :pending-files="images.pendingFiles.value"
                :pending-image-urls="images.pendingImageUrls.value"
                :new-image-url="images.newImageUrl.value"
                :is-dragging="images.isDragging.value"
                :deleting-image-id="images.deletingImageId.value"
                :editing-id="editingId"
                @update:new-image-url="images.newImageUrl.value = $event"
                @add-url="images.addImageUrl"
                @remove-pending-url="images.removePendingUrl"
                @remove-pending-file="images.removePendingFile"
                @delete-existing-image="images.deleteExistingImage"
                @files-selected="images.addFiles"
                @dragover="images.onDragOver"
                @dragleave="images.onDragLeave"
                @drop="images.onDrop"
              />

              <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="showModal = false"
                  class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                  Hủy
                </button>
                <button type="submit" :disabled="saving"
                  class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60 flex items-center gap-2">
                  <LoadingSpinner v-if="saving" size="sm" color="white" />
                  {{ editingId ? 'Cập nhật' : 'Tạo mới' }}
                </button>
              </div>
            </form>
          </div>
        </DialogPanel>
      </div>
    </Dialog>

    <!-- Delete confirm -->
    <ConfirmModal
      :show="deleteConfirm.show"
      title="Xóa sản phẩm"
      :message="'Bạn có chắc muốn xóa sản phẩm ' + deleteConfirm.name + '?'"
      confirm-text="Xóa"
      @confirm="deleteProduct"
      @cancel="deleteConfirm.show = false"
    />
  </div>
</template>
