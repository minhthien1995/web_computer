import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  // All public + customer routes share AppLayout (header + footer)
  {
    path: '/',
    component: () => import('@/layouts/AppLayout.vue'),
    children: [
      // Public pages
      { path: '', component: () => import('@/pages/customer/HomePage.vue') },
      { path: 'san-pham', component: () => import('@/pages/customer/ProductListingPage.vue') },
      { path: 'san-pham/:slug', component: () => import('@/pages/customer/ProductDetailPage.vue') },
      { path: 'danh-muc/:slug', component: () => import('@/pages/customer/CategoryPage.vue') },
      { path: 'gio-hang', component: () => import('@/pages/customer/CartPage.vue') },
      { path: 'thanh-toan', component: () => import('@/pages/customer/CheckoutPage.vue'), meta: { requiresAuth: true } },
      { path: 'thanh-toan/thanh-cong/:orderNo', component: () => import('@/pages/customer/OrderSuccessPage.vue') },
      // Repair service routes
      { path: 'sua-chua', component: () => import('@/pages/customer/RepairServicesPage.vue') },
      { path: 'sua-chua/dat-lich', component: () => import('@/pages/customer/BookRepairPage.vue') },
      { path: 'sua-chua/tra-cuu', component: () => import('@/pages/customer/TrackRepairPage.vue') },
      // Auth routes
      { path: 'dang-nhap', component: () => import('@/pages/auth/LoginPage.vue'), meta: { guestOnly: true } },
      { path: 'dang-ky', component: () => import('@/pages/auth/RegisterPage.vue'), meta: { guestOnly: true } },
      // Customer account (sidebar layout, still inside AppLayout header)
      {
        path: 'tai-khoan',
        component: () => import('@/layouts/AccountLayout.vue'),
        meta: { requiresAuth: true },
        children: [
          { path: '', redirect: '/tai-khoan/don-hang' },
          { path: 'don-hang', component: () => import('@/pages/customer/MyOrdersPage.vue') },
          { path: 'don-hang/:orderNo', component: () => import('@/pages/customer/OrderDetailPage.vue') },
          { path: 'sua-chua', component: () => import('@/pages/customer/MyRepairsPage.vue') },
          { path: 'ho-so', component: () => import('@/pages/customer/ProfilePage.vue') },
        ],
      },
    ],
  },
  // Admin routes (separate layout, no AppLayout header)
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAdmin: true },
    children: [
      { path: '', component: () => import('@/pages/admin/DashboardPage.vue') },
      { path: 'san-pham', component: () => import('@/pages/admin/ProductsPage.vue') },
      { path: 'don-hang', component: () => import('@/pages/admin/OrdersPage.vue') },
      { path: 'sua-chua', component: () => import('@/pages/admin/RepairOrdersPage.vue') },
      { path: 'nguoi-dung', component: () => import('@/pages/admin/UsersPage.vue') },
      { path: 'bao-cao', component: () => import('@/pages/admin/AnalyticsPage.vue') },
      { path: 'cai-dat', component: () => import('@/pages/admin/SettingsPage.vue') },
    ],
  },
  { path: '/:pathMatch(.*)*', component: () => import('@/pages/NotFoundPage.vue') },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

// Navigation guards
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (!auth.user) {
    await auth.fetchUser()
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { path: '/dang-nhap', query: { redirect: to.fullPath } }
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return { path: '/' }
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { path: '/' }
  }
})

export default router
