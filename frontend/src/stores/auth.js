import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

const TOKEN_KEY = 'auth_token'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!user.value)
  // Support both Spatie roles array and a flat role string
  const primaryRole = computed(() => {
    if (!user.value) return null
    if (Array.isArray(user.value.roles) && user.value.roles.length > 0) {
      return user.value.roles[0].name
    }
    return user.value.role ?? null
  })
  const isAdmin = computed(() => primaryRole.value === 'admin')
  const isTechnician = computed(() => primaryRole.value === 'technician')

  // Set Bearer token on api instance
  function setToken(token) {
    if (token) {
      localStorage.setItem(TOKEN_KEY, token)
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`
    } else {
      localStorage.removeItem(TOKEN_KEY)
      delete api.defaults.headers.common['Authorization']
    }
  }

  // Restore token from storage on app init
  const savedToken = localStorage.getItem(TOKEN_KEY)
  if (savedToken) setToken(savedToken)

  async function fetchUser() {
    try {
      const { data } = await api.get('/auth/me')
      user.value = data.data
    } catch {
      user.value = null
      setToken(null)
    }
  }

  async function login(credentials) {
    loading.value = true
    try {
      const { data } = await api.post('/auth/login', credentials)
      user.value = data.data.user
      setToken(data.data.token)
      return data
    } finally {
      loading.value = false
    }
  }

  async function register(payload) {
    loading.value = true
    try {
      const { data } = await api.post('/auth/register', payload)
      user.value = data.data.user
      setToken(data.data.token)
      return data
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try { await api.post('/auth/logout') } catch { /* ignore */ }
    user.value = null
    setToken(null)
  }

  return { user, loading, isAuthenticated, isAdmin, isTechnician, primaryRole, fetchUser, login, register, logout }
})
