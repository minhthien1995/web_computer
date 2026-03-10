<script setup>
import { ref } from 'vue'
import { XMarkIcon, PhotoIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  existingImages: { type: Array, default: () => [] },
  pendingFiles: { type: Array, default: () => [] },
  pendingImageUrls: { type: Array, default: () => [] },
  newImageUrl: { type: String, default: '' },
  isDragging: { type: Boolean, default: false },
  deletingImageId: { type: [Number, null], default: null },
  editingId: { type: [Number, null], default: null },
})

const emit = defineEmits([
  'update:newImageUrl',
  'add-url',
  'remove-pending-url',
  'remove-pending-file',
  'delete-existing-image',
  'files-selected',
  'dragover',
  'dragleave',
  'drop',
])

const fileInputRef = ref(null)

function onFilesSelected(event) {
  const files = Array.from(event.target.files || [])
  emit('files-selected', files)
  if (fileInputRef.value) fileInputRef.value.value = ''
}

function triggerFileInput() {
  fileInputRef.value?.click()
}
</script>

<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
      <PhotoIcon class="w-4 h-4 inline -mt-0.5" /> Hình ảnh sản phẩm
    </label>

    <!-- Existing images (edit mode) -->
    <div v-if="existingImages.length > 0" class="flex flex-wrap gap-2 mb-3">
      <div v-for="img in existingImages" :key="img.id" class="relative group w-16 h-16 rounded-lg overflow-hidden border border-gray-200">
        <img :src="img.url" :alt="img.alt_text" class="w-full h-full object-cover" />
        <button
          type="button"
          @click="$emit('delete-existing-image', editingId, img.id)"
          :disabled="deletingImageId === img.id"
          class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity"
        >
          <XMarkIcon class="w-4 h-4 text-white" />
        </button>
      </div>
    </div>

    <!-- Pending files (with preview) -->
    <div v-if="pendingFiles.length > 0" class="flex flex-wrap gap-2 mb-3">
      <div v-for="(item, idx) in pendingFiles" :key="'file-'+idx" class="relative group w-16 h-16 rounded-lg overflow-hidden border border-blue-300 bg-blue-50">
        <img :src="item.preview" :alt="item.name" class="w-full h-full object-cover" />
        <button
          type="button"
          @click="$emit('remove-pending-file', idx)"
          class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity"
        >
          <XMarkIcon class="w-4 h-4 text-white" />
        </button>
      </div>
    </div>

    <!-- Pending URLs (not yet uploaded) -->
    <div v-if="pendingImageUrls.length > 0" class="flex flex-wrap gap-2 mb-3">
      <div v-for="(url, idx) in pendingImageUrls" :key="'url-'+idx" class="flex items-center gap-1.5 bg-blue-50 border border-blue-200 rounded-lg px-2 py-1 text-xs">
        <span class="truncate max-w-[180px]">{{ url }}</span>
        <button type="button" @click="$emit('remove-pending-url', idx)" class="text-blue-400 hover:text-red-500 shrink-0">
          <XMarkIcon class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>

    <!-- Drag & drop + file input -->
    <div
      @dragover="$emit('dragover', $event)"
      @dragleave="$emit('dragleave')"
      @drop="$emit('drop', $event)"
      :class="[
        'border-2 border-dashed rounded-lg p-4 text-center cursor-pointer transition-colors mb-3',
        isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-400 hover:bg-gray-50'
      ]"
      @click="triggerFileInput"
    >
      <ArrowUpTrayIcon class="w-6 h-6 mx-auto text-gray-400 mb-1" />
      <p class="text-sm text-gray-500">Kéo thả ảnh vào đây hoặc <span class="text-blue-600 font-medium">chọn file</span></p>
      <p class="text-xs text-gray-400 mt-0.5">JPG, PNG, GIF, WebP — tối đa 5MB</p>
      <input
        ref="fileInputRef"
        type="file"
        accept="image/jpeg,image/png,image/gif,image/webp"
        multiple
        class="hidden"
        @change="onFilesSelected"
      />
    </div>

    <!-- Or add image URL input -->
    <div class="flex gap-2">
      <input
        :value="newImageUrl"
        @input="$emit('update:newImageUrl', $event.target.value)"
        type="url"
        placeholder="Hoặc nhập URL ảnh sản phẩm..."
        @keyup.enter.prevent="$emit('add-url')"
        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <button
        type="button"
        @click="$emit('add-url')"
        class="px-3 py-2 text-sm font-medium text-blue-600 border border-blue-300 rounded-lg hover:bg-blue-50 shrink-0"
      >
        Thêm
      </button>
    </div>
  </div>
</template>
