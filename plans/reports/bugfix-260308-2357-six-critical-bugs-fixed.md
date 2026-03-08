# Bugfix Report — 6 Critical Bugs Fixed
**Date:** 2026-03-08 | **Session:** 23:20–23:57

---

## Summary
Fixed 6 critical bugs identified in the implementation plan (`plans/260308-2327-remaining-features-and-stabilization/`). All fixes verified with a clean `npm run build` (zero errors).

---

## BUG-01 — OrderController.php: Validation field mismatch + missing payment method

**File:** `backend/app/Http/Controllers/Api/V1/Customer/OrderController.php`

**Problem:**
- Backend validated `shipping_address.full_name` and `shipping_address.street`
- Frontend sent `shipping_address.name` and `shipping_address.address`
- Backend enum did not include `bank_transfer`; frontend had it as a payment option

**Fix:**
```php
// Before
'shipping_address.full_name'   => 'required|string',
'shipping_address.street'      => 'required|string',
'shipping_address.ward'        => 'required|string',
'shipping_address.district'    => 'required|string',
'shipping_address.province'    => 'required|string',
'payment_method'               => 'required|in:cod,momo,zalopay,vnpay,sepay',

// After
'shipping_address.name'        => 'required|string',
'shipping_address.address'     => 'required|string',
'shipping_address.ward'        => 'nullable|string',
'shipping_address.district'    => 'nullable|string',
'shipping_address.province'    => 'nullable|string',
'payment_method'               => 'required|in:cod,bank_transfer,momo,zalopay,vnpay,sepay',
```

---

## BUG-02 — BookRepairPage.vue: Wrong field names sent to API

**File:** `frontend/src/pages/customer/BookRepairPage.vue`

**Problem:**
- Form used `service_id` but backend validates `repair_service_id`
- Form used `notes` but backend validates `customer_notes`
- Success handler read `data.data?.repair_order_number` but API returns `order_number`

**Fix:**
- `service_id` → `repair_service_id` (form ref, fetchServices default, template v-model, template :class)
- `notes` → `customer_notes` (form ref, template v-model)
- `data.data?.repair_order_number` → `data.data?.order_number`

---

## BUG-03 — MyRepairsPage.vue: Wrong endpoint + wrong field names

**File:** `frontend/src/pages/customer/MyRepairsPage.vue`

**Problem:**
- Called `GET /repair-bookings` — route doesn't exist for authenticated list
- Displayed `repair.repair_order_number` — field is `order_number`
- Displayed `repair.service?.name` — relation is `repair_service`

**Fix:**
- `api.get('/repair-bookings')` → `api.get('/my-repairs')`
- `repair.repair_order_number` → `repair.order_number`
- `repair.service` → `repair.repair_service` (condition + accessor)

---

## BUG-04 — AdminOrdersPage.vue: Wrong ID in API calls + wrong update URL + broken pagination

**File:** `frontend/src/pages/admin/OrdersPage.vue`

**Problem:**
- `openOrder` used `order.order_number || order.id` — backend `findOrFail($id)` needs numeric ID
- `updateStatus` called `PUT /admin/orders/{order_number}/status` — no `/status` route exists for orders
- Pagination read `data.data?.meta` — Laravel paginator returns flat keys (`current_page`, `total`, etc.)

**Fix:**
```js
// openOrder — use numeric ID
api.get(`/admin/orders/${order.id}`)

// updateStatus — correct endpoint, no /status suffix
api.put(`/admin/orders/${selectedOrder.value.id}`, { status: newStatus })

// fetchOrders — correct paginator extraction
const paginator = data.data || {}
orders.value = paginator.data || []
if (paginator.current_page !== undefined) {
  meta.value = { current_page: paginator.current_page, last_page: paginator.last_page, total: paginator.total, per_page: paginator.per_page }
}
```

---

## BUG-05 — AdminRepairOrdersPage.vue: Multiple field/endpoint errors + broken pagination

**File:** `frontend/src/pages/admin/RepairOrdersPage.vue`

**Problems:**
1. Pagination used `data.data?.meta` (wrong pattern)
2. Table displayed `repair.repair_order_number` — field is `order_number`
3. Table displayed `repair.service?.name` — relation is `repair_service`
4. Modal title used `selectedRepair.repair_order_number`
5. PUT payload included `status` — backend `update()` does NOT accept status
6. PUT payload used `actual_cost` — backend accepts `quoted_price` / `final_price`
7. Status update had no separate POST to `/status` endpoint

**Fix:**
- Renamed `newActualCost` → `newQuotedPrice`
- Added `originalStatus` ref to track status before modal open
- Split update logic: PUT for details, POST to `/status` only if status changed
- Fixed all field name references in template

```js
// updateRepair — separated concerns
await api.put(`/admin/repair-orders/${selectedRepair.value.id}`, {
  diagnosis_notes: newDiagnosisNotes.value,
  quoted_price: newQuotedPrice.value || null,
  technician_id: newTechnicianId.value || null,
})
if (selectedRepair.value.status !== originalStatus.value) {
  await api.post(`/admin/repair-orders/${selectedRepair.value.id}/status`, {
    status: selectedRepair.value.status,
  })
}
```

---

## BUG-06 — ProductsPage.vue + MyOrdersPage.vue: Broken pagination

**Files:**
- `frontend/src/pages/admin/ProductsPage.vue`
- `frontend/src/pages/customer/MyOrdersPage.vue`

**Problem:** Both used `if (data.data?.meta) meta.value = data.data.meta` — Laravel `LengthAwarePaginator` serializes flat (no nested `meta` object).

**Fix:** Applied same correct extraction pattern as ProductListingPage.vue:
```js
const paginator = data.data || {}
[items].value = paginator.data || []
if (paginator.current_page !== undefined) {
  meta.value = { current_page: paginator.current_page, last_page: paginator.last_page, total: paginator.total, per_page: paginator.per_page }
}
```

---

## Files Changed

| File | Type | Change |
|------|------|--------|
| `backend/app/Http/Controllers/Api/V1/Customer/OrderController.php` | Backend | Validation fields + enum |
| `frontend/src/pages/customer/BookRepairPage.vue` | Frontend | Field names, order_number |
| `frontend/src/pages/customer/MyRepairsPage.vue` | Frontend | Endpoint, field names |
| `frontend/src/pages/admin/OrdersPage.vue` | Frontend | ID usage, URL, pagination |
| `frontend/src/pages/admin/RepairOrdersPage.vue` | Frontend | Field names, status endpoint, pagination |
| `frontend/src/pages/admin/ProductsPage.vue` | Frontend | Pagination |
| `frontend/src/pages/customer/MyOrdersPage.vue` | Frontend | Pagination |

**Build result:** ✅ `npm run build` — 0 errors, all chunks compiled successfully.
