# Plan: Remaining Features & Stabilization
**Date:** 2026-03-08 | **Branch:** master
**Status:** Pending implementation

---

## Tình trạng hiện tại

| Hạng mục | Tình trạng |
|----------|-----------|
| Backend models/controllers/routes/seeders | ✅ Done |
| Homepage, product listing, product detail | ✅ Working |
| Auth (login/logout/register) | ✅ Working |
| Cart (add/update/remove/count badge) | ✅ Working |
| Admin dashboard, admin products list | ✅ Working |
| **Checkout flow** | ❌ Broken (field mismatch) |
| **Repair booking** | ❌ Broken (field mismatch) |
| **My repairs page** | ❌ Broken (wrong endpoint + field names) |
| **Admin order management** | ❌ Broken (wrong URL pattern) |
| **Admin repair management** | ❌ Broken (wrong field names + status update) |
| **Admin pagination** | ❌ Broken (meta extraction wrong) |
| Email notifications | ⚠️ Not implemented |
| Payment webhooks | ⚠️ Stub only |
| Image upload | ⚠️ URL-only (no real upload) |

---

## Phase 01 — Critical Bug Fixes (P0) — Est. 2h

### BUG-01: Checkout payload field mismatch (CRITICAL)

**File:** `frontend/src/pages/customer/CheckoutPage.vue` + `backend/.../OrderController.php`

Frontend gửi:
```js
shipping_address: { name, phone, address_detail, ward, district, province }
payment_method: 'bank_transfer'
```

Backend expects:
```php
'shipping_address.full_name' => 'required'
'shipping_address.street'    => 'required'
payment_method: in:cod,momo,zalopay,vnpay,sepay  // không có bank_transfer
```

**Fix (backend):** Sửa OrderController validator chấp nhận `name`/`address` + thêm `bank_transfer`:
- `full_name` → accept cả `name`
- `street` → accept cả `address`
- Thêm `bank_transfer` vào enum

---

### BUG-02: Repair booking field name mismatch

**File:** `frontend/src/pages/customer/BookRepairPage.vue`

| Frontend gửi | Backend expects |
|-------------|----------------|
| `service_id` | `repair_service_id` |
| `notes` | `customer_notes` |

**Fix:** Sửa field name trong submit payload của BookRepairPage.

---

### BUG-03: MyRepairsPage wrong endpoint + field name

**File:** `frontend/src/pages/customer/MyRepairsPage.vue`

- Line 34: `GET /repair-bookings` → phải là `GET /my-repairs`
- Line 83: `repair.repair_order_number` → phải là `repair.order_number`
- Relation name: `repair.service?.name` → phải là `repair.repair_service?.name`

---

### BUG-04: AdminOrdersPage wrong URL pattern

**File:** `frontend/src/pages/admin/OrdersPage.vue`

- Show detail: dùng `order.order_number` → phải dùng `order.id` (numeric)
- Update URL: `PUT /admin/orders/${id}/status` → phải là `PUT /admin/orders/${id}` (không có `/status`)

---

### BUG-05: AdminRepairOrdersPage wrong field names + status endpoint

**File:** `frontend/src/pages/admin/RepairOrdersPage.vue`

- `repair.repair_order_number` → `repair.order_number`
- `repair.service?.name` → `repair.repair_service?.name`
- Status update: `PUT /admin/repair-orders/{id}` không nhận `status` → phải dùng `POST /admin/repair-orders/{id}/status`
- `actual_cost` → backend expects `quoted_price` / `final_price`

---

### BUG-06: Admin pagination meta extraction sai (áp dụng nhiều pages)

**Files:**
- `frontend/src/pages/admin/ProductsPage.vue`
- `frontend/src/pages/admin/OrdersPage.vue`
- `frontend/src/pages/admin/RepairOrdersPage.vue`
- `frontend/src/pages/customer/MyOrdersPage.vue`

Backend trả Laravel paginator: `data.data.current_page`, `data.data.total`, ...
Frontend check `data.data?.meta` → **không tồn tại** → pagination never works.

**Fix pattern** (áp dụng đồng nhất cho tất cả):
```js
const paginator = data.data || {}
items.value = paginator.data || []
meta.value = {
  current_page: paginator.current_page || 1,
  last_page:    paginator.last_page    || 1,
  total:        paginator.total        || 0,
  per_page:     paginator.per_page     || 15,
}
```

---

## Phase 02 — Admin CRUD Stabilization (P1) — Est. 1.5h

### Admin ProductsPage — Thiếu image URL input

`uploadImage` endpoint nhận URL string, không phải file. Form tạo/sửa sản phẩm chưa có field nhập URL ảnh.

**Fix:** Thêm image URL input vào modal create/edit trong `ProductsPage.vue`

### Admin ProductsPage — Thiếu client-side validation

Backend yêu cầu `category_id` required nhưng form không validate.

---

## Phase 03 — Storage & Image Upload (P2) — Est. 1h

Hiện tại `uploadImage` chỉ lưu URL string. Nếu muốn upload thực sự:

1. Tạo endpoint `POST /admin/products/{id}/upload-image` nhận multipart file
2. Chạy `php artisan storage:link`
3. Dùng `Storage::disk('public')->put()`
4. Frontend dùng `<input type="file">` + FormData

*Quyết định: URL-only (đơn giản) hay file upload thực sự?*

---

## Phase 04 — Email Notifications (P3) — Est. 2h

1. Config `MAIL_MAILER=log` trong `.env` (dev)
2. Tạo `OrderConfirmationMailable`
3. Tạo `RepairBookingConfirmationMailable`
4. Dispatch sau khi tạo order/booking thành công

---

## Phase 05 — Payment Webhooks (P4) — Est. 3h+

Hiện là stubs. Để implement thật cần:
- Verify signature từ từng gateway (MoMo, VNPay, ZaloPay, SePay)
- Cập nhật `Payment.status` → `paid`
- Trigger order fulfillment

*Low priority — có thể để sau MVP*

---

## Todo Checklist

### Phase 01 — Critical ✅ DONE
- [x] BUG-01: Fix OrderController validator (name/address/bank_transfer)
- [x] BUG-02: Fix BookRepairPage field names (repair_service_id, customer_notes)
- [x] BUG-03: Fix MyRepairsPage endpoint URL + field names
- [x] BUG-04: Fix AdminOrdersPage URL pattern (id not order_number, no /status)
- [x] BUG-05: Fix AdminRepairOrdersPage field names + status endpoint
- [x] BUG-06: Fix pagination meta extraction (4 pages)

### Phase 02 ✅ DONE
- [x] Thêm image URL field vào AdminProductsPage modal
- [x] Thêm client-side validation cho category_id
- [x] Fix stock_quantity → stock_qty field mismatch
- [x] Thêm image thumbnail vào product table
- [x] Hiển thị existing images khi edit + delete capability

### Phase 03 ✅ DONE
- [x] Storage symlink: `php artisan storage:link`
- [x] Backend: `uploadImage` accepts both file (multipart) and URL string
- [x] Backend: `deleteImage` cleans up local files from storage disk
- [x] Frontend: Drag-and-drop + file input (`<input type="file" multiple>`)
- [x] Frontend: File preview thumbnails before upload
- [x] Frontend: FormData upload with `Content-Type: multipart/form-data`
- [x] Vite proxy: `/storage` → backend for serving uploaded images
- [x] Modularized: `use-product-images.js` composable + `product-image-manager.vue` component

### Phase 04
- [ ] Config mail + tạo Mailables + wire to controllers

### Phase 05
- [ ] Implement real webhook handlers (post-MVP)

---

## Risk Assessment

| Bug | Impact | Ưu tiên |
|-----|--------|---------|
| BUG-01 Checkout | Checkout 100% broken | P0 |
| BUG-03 My Repairs | Page 404 luôn | P0 |
| BUG-04 Admin Orders | Update order fail | P0 |
| BUG-06 Pagination | Admin không phân trang được | P0 |
| BUG-02 Repair booking | Submit không lưu đúng field | P0 |
| BUG-05 Admin Repairs | Status update fail | P0 |

---

## Unresolved Questions

1. Image upload: URL-only hay multipart file upload?
2. `bank_transfer`: thêm vào backend enum hay xóa khỏi frontend?
3. Email dev: `log` driver hay SMTP (Mailtrap)?
4. Webhooks: cần real integration cho MVP hay stub đủ?
