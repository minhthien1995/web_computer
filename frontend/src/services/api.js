import axios from 'axios'

const api = axios.create({
  baseURL: '/api/v1',
  withCredentials: true, // required for Sanctum cookie auth
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

// Note: using Bearer token auth (set by auth store), no CSRF cookie needed

// On 401: skip auto-redirect for auth-check routes (let router guard handle it)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const url = error.config?.url || ''
    const is401 = error.response?.status === 401
    const isAuthCheck = url.includes('/auth/me') || url.includes('/auth/logout')
    if (is401 && !isAuthCheck && window.location.pathname !== '/dang-nhap') {
      window.location.href = '/dang-nhap'
    }
    return Promise.reject(error)
  }
)

export default api
