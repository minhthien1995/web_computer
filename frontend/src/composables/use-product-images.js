import { ref } from 'vue'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'

/**
 * Composable for managing product images (file upload, URL, delete).
 * Supports both multipart file uploads and URL-based image addition.
 */
export function useProductImages() {
  const ui = useUiStore()

  const existingImages = ref([])
  const pendingImageUrls = ref([])
  const pendingFiles = ref([])
  const newImageUrl = ref('')
  const deletingImageId = ref(null)
  const uploadingImages = ref(false)
  const isDragging = ref(false)

  const VALID_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
  const MAX_FILE_SIZE = 5 * 1024 * 1024 // 5MB

  function reset() {
    pendingFiles.value.forEach(f => URL.revokeObjectURL(f.preview))
    existingImages.value = []
    pendingImageUrls.value = []
    pendingFiles.value = []
    newImageUrl.value = ''
    deletingImageId.value = null
  }

  function loadExisting(images) {
    existingImages.value = images ? [...images] : []
    pendingImageUrls.value = []
    pendingFiles.value = []
    newImageUrl.value = ''
  }

  function addImageUrl() {
    const url = newImageUrl.value.trim()
    if (!url) return
    if (pendingImageUrls.value.includes(url)) {
      ui.error('URL ảnh đã tồn tại')
      return
    }
    pendingImageUrls.value.push(url)
    newImageUrl.value = ''
  }

  function removePendingUrl(index) {
    pendingImageUrls.value.splice(index, 1)
  }

  function addFiles(files) {
    for (const file of files) {
      if (!VALID_TYPES.includes(file.type)) {
        ui.error(`${file.name}: Chỉ chấp nhận JPG, PNG, GIF, WebP`)
        continue
      }
      if (file.size > MAX_FILE_SIZE) {
        ui.error(`${file.name}: Dung lượng tối đa 5MB`)
        continue
      }
      const preview = URL.createObjectURL(file)
      pendingFiles.value.push({ file, preview, name: file.name })
    }
  }

  function removePendingFile(index) {
    URL.revokeObjectURL(pendingFiles.value[index].preview)
    pendingFiles.value.splice(index, 1)
  }

  function onDragOver(e) {
    e.preventDefault()
    isDragging.value = true
  }

  function onDragLeave() {
    isDragging.value = false
  }

  function onDrop(e) {
    e.preventDefault()
    isDragging.value = false
    addFiles(Array.from(e.dataTransfer?.files || []))
  }

  async function deleteExistingImage(productId, imageId) {
    deletingImageId.value = imageId
    try {
      await api.delete(`/admin/products/${productId}/images/${imageId}`)
      existingImages.value = existingImages.value.filter(img => img.id !== imageId)
    } catch {
      ui.error('Không thể xóa ảnh')
    } finally {
      deletingImageId.value = null
    }
  }

  async function uploadAll(productId) {
    if (!productId) return
    if (pendingFiles.value.length === 0 && pendingImageUrls.value.length === 0) return

    uploadingImages.value = true
    try {
      // Upload files via FormData
      for (const item of pendingFiles.value) {
        try {
          const formData = new FormData()
          formData.append('image', item.file)
          await api.post(`/admin/products/${productId}/images`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
          })
        } catch { /* skip failed uploads */ }
      }
      // Upload URLs
      for (const url of pendingImageUrls.value) {
        try {
          await api.post(`/admin/products/${productId}/images`, { url })
        } catch { /* skip failed uploads */ }
      }
    } finally {
      pendingFiles.value.forEach(f => URL.revokeObjectURL(f.preview))
      pendingFiles.value = []
      pendingImageUrls.value = []
      uploadingImages.value = false
    }
  }

  /** Whether there are any pending uploads (files or URLs) */
  function hasPending() {
    return pendingFiles.value.length > 0 || pendingImageUrls.value.length > 0
  }

  return {
    existingImages,
    pendingImageUrls,
    pendingFiles,
    newImageUrl,
    deletingImageId,
    uploadingImages,
    isDragging,
    reset,
    loadExisting,
    addImageUrl,
    removePendingUrl,
    addFiles,
    removePendingFile,
    onDragOver,
    onDragLeave,
    onDrop,
    deleteExistingImage,
    uploadAll,
    hasPending,
  }
}
