import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useCartStore = defineStore('cart', () => {
  const items = ref([])
  const loading = ref(false)

  const itemCount = computed(() => items.value.reduce((sum, item) => sum + (item.quantity || 0), 0))
  const subtotal = computed(() => items.value.reduce((sum, item) => sum + parseFloat(item.unit_price || 0) * (item.quantity || 0), 0))

  // API returns { data: { cart: { items: [...] } } }
  function extractItems(data) {
    return data.data?.cart?.items ?? data.data?.items ?? []
  }

  async function fetchCart() {
    const { data } = await api.get('/cart')
    items.value = extractItems(data)
  }

  async function addItem(productId, variantId, quantity = 1) {
    loading.value = true
    try {
      const { data } = await api.post('/cart/items', { product_id: productId, variant_id: variantId, quantity })
      items.value = extractItems(data)
    } finally {
      loading.value = false
    }
  }

  async function updateItem(itemId, quantity) {
    const { data } = await api.put(`/cart/items/${itemId}`, { quantity })
    items.value = extractItems(data)
  }

  async function removeItem(itemId) {
    const { data } = await api.delete(`/cart/items/${itemId}`)
    items.value = extractItems(data)
  }

  async function clearCart() {
    await api.delete('/cart')
    items.value = []
  }

  // Merge guest cart into user cart after login
  async function mergeCart() {
    const { data } = await api.post('/cart/merge')
    items.value = extractItems(data)
  }

  return { items, loading, itemCount, subtotal, fetchCart, addItem, updateItem, removeItem, clearCart, mergeCart }
})
