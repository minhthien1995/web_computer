<script setup>
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: 'Xác nhận' },
  message: { type: String, default: 'Bạn có chắc muốn thực hiện hành động này?' },
  confirmText: { type: String, default: 'Xác nhận' },
  confirmClass: { type: String, default: 'bg-red-600 hover:bg-red-700 text-white' },
})

const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
  <Dialog :open="show" @close="$emit('cancel')" class="relative z-50">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/40" aria-hidden="true" />

    <div class="fixed inset-0 flex items-center justify-center p-4">
      <DialogPanel class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex gap-4">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon class="w-6 h-6 text-red-500" />
          </div>
          <div>
            <DialogTitle class="text-lg font-semibold text-gray-900 mb-2">{{ title }}</DialogTitle>
            <p class="text-gray-600 text-sm">{{ message }}</p>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button
            @click="$emit('cancel')"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
          >
            Hủy
          </button>
          <button
            @click="$emit('confirm')"
            class="px-4 py-2 text-sm font-medium rounded-lg"
            :class="confirmClass"
          >
            {{ confirmText }}
          </button>
        </div>
      </DialogPanel>
    </div>
  </Dialog>
</template>
