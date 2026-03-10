<script setup>
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  specs: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:specs'])

function addSpec() {
  emit('update:specs', [...props.specs, { spec_group: '', spec_key: '', spec_value: '' }])
}

function removeSpec(index) {
  const updated = [...props.specs]
  updated.splice(index, 1)
  emit('update:specs', updated)
}

function updateField(index, field, value) {
  const updated = [...props.specs]
  updated[index] = { ...updated[index], [field]: value }
  emit('update:specs', updated)
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-2">
      <label class="block text-sm font-medium text-gray-700">Thông số kỹ thuật</label>
      <button
        type="button"
        @click="addSpec"
        class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium"
      >
        <PlusIcon class="w-3.5 h-3.5" />
        Thêm thông số
      </button>
    </div>

    <div v-if="specs.length === 0" class="text-sm text-gray-400 italic py-2">
      Chưa có thông số. Nhấn "Thêm thông số" để bắt đầu.
    </div>

    <div v-else class="space-y-2">
      <div v-for="(spec, idx) in specs" :key="idx" class="flex items-start gap-2">
        <input
          :value="spec.spec_group"
          @input="updateField(idx, 'spec_group', $event.target.value)"
          type="text"
          placeholder="Nhóm (VD: Hiển thị)"
          class="w-1/4 border border-gray-300 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <input
          :value="spec.spec_key"
          @input="updateField(idx, 'spec_key', $event.target.value)"
          type="text"
          placeholder="Tên (VD: Kích thước)"
          class="w-1/3 border border-gray-300 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <input
          :value="spec.spec_value"
          @input="updateField(idx, 'spec_value', $event.target.value)"
          type="text"
          placeholder="Giá trị (VD: 15.6 inch)"
          class="flex-1 border border-gray-300 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button
          type="button"
          @click="removeSpec(idx)"
          class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg shrink-0"
        >
          <TrashIcon class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>
