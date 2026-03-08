# Web Computer Store — Session Report
**Date:** 2026-03-08 | **Branch:** master

---

## 1. Tổng quan

Triển khai đầy đủ ứng dụng **Web Computer Store** (Laravel 11 backend + Vue 3 frontend + MySQL + Redis via Docker). Bao gồm toàn bộ backend API, frontend pages, và fix nhiều bugs phát sinh trong quá trình integration.

---

## 2. Cấu trúc dự án

```
web_computer/
├── backend/         Laravel 11 + Sanctum + Spatie Permission
├── frontend/        Vue 3 + Pinia + Tailwind CSS v4 + Vite
├── docker-compose.yml  MySQL 8.0 + Redis Alpine
└── docs/
```

**Khởi động:**
- Backend: `php artisan serve` → http://localhost:8000
- Frontend: `npm run dev` → http://localhost:5173
- Docker: `docker-compose up -d` → MySQL :3306, Redis :6379

---

## 3. Backend — Những gì đã tạo

### 3.1 Models (16 models)
| Model | Quan hệ chính |
|-------|--------------|
| User | HasRoles (Spatie), HasApiTokens (Sanctum) |
| Product | belongsTo Category, Brand; hasMany Images, Specs, Variants, CartItems, OrderItems |
| Category | hasMany Products |
| Brand | hasMany Products |
| Cart | belongsTo User; hasMany CartItems |
| CartItem | belongsTo Cart, Product |
| Order | belongsTo User; hasMany OrderItems, Payment |
| OrderItem | belongsTo Order, Product |
| Payment | belongsTo Order |
| RepairOrder | belongsTo User, RepairService; hasMany StatusLogs |
| RepairService | hasMany RepairOrders |
| RepairStatusLog | belongsTo RepairOrder |
| Setting | key-value store |
| Address | belongsTo User |
| ProductVariant | belongsTo Product |
| ProductImage | belongsTo Product |
| ProductSpec | belongsTo Product |

### 3.2 Controllers & Routes

**Auth** (`/api/v1/auth/`):
- `POST /register` — Đăng ký, tự gán role `customer`
- `POST /login` — Đăng nhập, trả về Bearer token
- `POST /logout` — Đăng xuất, revoke token
- `GET /me` — Lấy thông tin user hiện tại

**Public** (`/api/v1/`):
- `GET /products` — Danh sách sản phẩm (paginated, filter, sort)
- `GET /products/{slug}` — Chi tiết sản phẩm
- `GET /featured-products` — Sản phẩm nổi bật
- `GET /categories` — Danh mục
- `GET /brands` — Thương hiệu
- `GET /repair-services` — Dịch vụ sửa chữa
- `GET /repair-tracking/{code}` — Tra cứu đơn sửa

**Customer** (`/api/v1/` + auth:sanctum):
- Cart: CRUD (`/cart`, `/cart/items`, `/cart/items/{id}`, `/cart/merge`)
- Orders: `GET /orders`, `POST /orders`, `GET /orders/{orderNo}`
- Repair: `POST /repair-bookings`, `GET /repair-bookings`
- Profile: `GET/PUT /profile`, Address CRUD

**Admin** (`/api/v1/admin/` + auth:sanctum + role:admin):
- AdminProductController — CRUD sản phẩm + upload image
- AdminCategoryController — CRUD danh mục
- AdminBrandController — CRUD thương hiệu
- AdminOrderController — Xem/cập nhật đơn hàng
- AdminRepairOrderController — Xem/cập nhật/đổi trạng thái đơn sửa
- AdminUserController — Danh sách/chi tiết/cập nhật user
- AdminAnalyticsController — Dashboard stats, sales, repairs
- AdminSettingsController — Xem/cập nhật cài đặt hệ thống

**Webhooks** (`/api/v1/webhooks/`):
- Stub cho MoMo, ZaloPay, VNPay, SePay (log + return `{received: true}`)

### 3.3 Seeders
| Seeder | Dữ liệu |
|--------|---------|
| RolesPermissionsSeeder | Roles: admin, customer, technician |
| AdminUserSeeder | admin@webcomputer.vn / Admin@123456 |
| CategorySeeder | Laptop, PC Bàn, Linh Kiện, Màn Hình, Phụ Kiện, VGA, RAM, CPU, Mainboard, Storage, Chuột, Bàn Phím |
| BrandSeeder | Apple, MSI, ASUS, HP, Dell, Corsair, AMD, Intel, ... |
| ProductSeeder | 20 sản phẩm với ảnh, thông số kỹ thuật, giá có/không giảm |
| RepairServiceSeeder | 6 dịch vụ sửa chữa |
| SettingsSeeder | store_name, currency, shipping fee, ... |

---

## 4. Frontend — Những gì đã tạo

### 4.1 Cấu trúc Router (sau khi fix)

```
/ (AppLayout)          ← Header + Footer bao ngoài TẤT CẢ trang public
├── ''                 → HomePage
├── san-pham           → ProductListingPage
├── san-pham/:slug     → ProductDetailPage
├── danh-muc/:slug     → CategoryPage
├── gio-hang           → CartPage
├── thanh-toan         → CheckoutPage (requiresAuth)
├── thanh-toan/thanh-cong/:orderNo → OrderSuccessPage
├── sua-chua           → RepairServicesPage
├── sua-chua/dat-lich  → BookRepairPage
├── sua-chua/tra-cuu   → TrackRepairPage
├── dang-nhap          → LoginPage (guestOnly)
├── dang-ky            → RegisterPage (guestOnly)
└── tai-khoan (AccountLayout, requiresAuth)
    ├── ''             → redirect /tai-khoan/don-hang
    ├── don-hang       → MyOrdersPage
    ├── don-hang/:no   → OrderDetailPage
    ├── sua-chua       → MyRepairsPage
    └── ho-so          → ProfilePage

/admin (AdminLayout, requiresAdmin)
├── ''         → DashboardPage
├── san-pham   → ProductsPage
├── don-hang   → OrdersPage
├── sua-chua   → RepairOrdersPage
├── nguoi-dung → UsersPage
├── bao-cao    → AnalyticsPage
└── cai-dat    → SettingsPage
```

### 4.2 Stores (Pinia)
- `auth.js` — User state, Bearer token (localStorage), isAdmin (Spatie roles)
- `cart.js` — Cart items, itemCount, subtotal, CRUD actions
- `ui.js` — Toast notifications (success/error/info)

### 4.3 Services
- `api.js` — Axios instance, Bearer token header, 401 redirect handler

### 4.4 Layouts
- `AppLayout.vue` — Header (logo, nav, cart badge, user), Footer
- `AccountLayout.vue` — Sidebar (avatar, nav: đơn hàng/sửa chữa/hồ sơ)
- `AdminLayout.vue` — Dark sidebar (nav: dashboard/sản phẩm/đơn hàng/...)

### 4.5 Components
- `ProductCard.vue` — Card sản phẩm (ảnh, tên, giá, badge nổi bật/giảm giá, thêm giỏ)
- `Pagination.vue` — Phân trang
- `LoadingSpinner.vue` — Loading indicator
- `EmptyState.vue` — Empty state với icon + message
- `ConfirmModal.vue` — Modal xác nhận
- `ProductImageGallery.vue` — Gallery ảnh sản phẩm

---

## 5. Bugs đã fix

### 5.1 Migration ordering
**Vấn đề:** `payments` table tạo trước `repair_orders` → foreign key fail
**Fix:** Đổi tên file từ `070815` → `070826` để chạy sau `repair_orders`

### 5.2 Header không hiển thị (quan trọng nhất)
**Vấn đề:** Customer routes là flat routes, không wrap trong `AppLayout`
**Fix:** Nest tất cả public routes dưới `AppLayout` làm parent component:
```js
// Trước (sai)
{ path: '/', component: HomePage }

// Sau (đúng)
{ path: '/', component: AppLayout, children: [
  { path: '', component: HomePage }, ...
]}
```

### 5.3 AccountLayout thiếu header
**Vấn đề:** `/tai-khoan` dùng `AccountLayout` riêng, không có site header
**Fix:** Nest `AccountLayout` dưới `AppLayout` → header + sidebar cùng tồn tại

### 5.4 "0 sản phẩm" trong product listing
**Vấn đề:** Code đọc `data.data.meta.total` nhưng Laravel paginator trả về `data.data.total` trực tiếp
**Fix:**
```js
// Trước
if (data.data?.meta) meta.value = data.data.meta
// Sau
const paginator = data.data || {}
meta.value = { current_page: paginator.current_page, total: paginator.total, ... }
```

### 5.5 Spatie role middleware 500 error
**Vấn đề:** `role` middleware chưa được đăng ký → Target class [role] does not exist
**Fix:** Thêm vào `bootstrap/app.php`:
```php
$middleware->alias([
    'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
]);
```

### 5.6 Admin dashboard stat keys sai
**Vấn đề:** Frontend dùng `total_repairs`, `total_customers` nhưng API trả `total_repair_orders`, `total_users`
**Fix:** Cập nhật `statCards` trong `DashboardPage.vue` dùng đúng key

### 5.7 Cart count không cập nhật
**Vấn đề:** Cart store đọc `data.data?.items` nhưng API trả `data.data.cart.items`
**Fix:**
```js
function extractItems(data) {
  return data.data?.cart?.items ?? data.data?.items ?? []
}
```

### 5.8 Cart subtotal tính sai
**Vấn đề:** `unit_price` từ API là string `"11490000.00"` → string concatenation thay vì số học
**Fix:** `parseFloat(item.unit_price || 0) * (item.quantity || 0)`

### 5.9 isAdmin luôn false
**Vấn đề:** Code check `user.role === 'admin'` (DB column) nhưng Spatie lưu trong `user.roles[]` array
**Fix:**
```js
const primaryRole = computed(() => {
  if (Array.isArray(user.value.roles) && user.value.roles.length > 0)
    return user.value.roles[0].name
  return user.value.role ?? null
})
const isAdmin = computed(() => primaryRole.value === 'admin')
```

### 5.10 Infinite redirect loop khi 401
**Vấn đề:** `fetchUser()` → 401 → axios interceptor redirect `/dang-nhap` → page reload → `fetchUser()` → loop
**Fix:** Skip redirect cho auth-check endpoints:
```js
const isAuthCheck = url.includes('/auth/me') || url.includes('/auth/logout')
if (is401 && !isAuthCheck && window.location.pathname !== '/dang-nhap') {
  window.location.href = '/dang-nhap'
}
```

### 5.11 Bearer token không được gửi
**Vấn đề:** Frontend dùng cookie-based auth nhưng Sanctum config trả token
**Fix:** Lưu token vào localStorage, restore khi init, set `Authorization: Bearer` header

### 5.12 Featured products 404
**Vấn đề:** Frontend gọi `/products/featured` (match route `show($slug)`) thay vì `/featured-products`
**Fix:** Sửa URL trong `HomePage.vue`

---

## 6. Kết quả kiểm thử

| Trang | URL | Kết quả |
|-------|-----|---------|
| Homepage | `/` | ✅ Hero, categories, featured products, why-us |
| Product listing | `/san-pham` | ✅ 20 sản phẩm, filter, sort, pagination |
| Product detail | `/san-pham/:slug` | ✅ Breadcrumb, giá/discount, add-to-cart |
| Add to cart | (click button) | ✅ Toast + cart badge count cập nhật |
| Cart page | `/gio-hang` | ✅ Items, qty controls, tổng tiền, Thanh toán button |
| Login | `/dang-nhap` | ✅ Form, redirect sau login |
| Account orders | `/tai-khoan/don-hang` | ✅ Header + sidebar |
| Repair services | `/sua-chua` | ✅ Hero, features, services list |
| Admin dashboard | `/admin` | ✅ Stats (0đ, 0 orders - DB trống), no error |
| Admin products | `/admin/san-pham` | ✅ 20 sản phẩm với actions |

---

## 7. Thông tin hệ thống

**Admin account:** admin@webcomputer.vn / Admin@123456
**API base:** http://localhost:8000/api/v1
**Auth:** Bearer token (Sanctum), lưu localStorage key `auth_token`
**Roles:** admin, customer, technician (Spatie Laravel Permission)

---

## 8. Unresolved / Chưa test

- Checkout flow (POST /orders) — cần test end-to-end
- Repair booking form — cần test submit
- Admin CRUD operations (tạo/sửa/xóa sản phẩm) — cần test
- Thanh toán webhook (MoMo, VNPay, ...) — còn là stub
- Email notifications — chưa cài đặt
- Image upload thực tế — chưa có storage config
