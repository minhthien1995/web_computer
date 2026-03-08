<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['items'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Danh sach don hang',
            'data'    => $orders,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shipping_address'             => 'required|array',
            'shipping_address.name'        => 'required|string',
            'shipping_address.phone'       => 'required|string',
            'shipping_address.address'     => 'required|string',
            'shipping_address.ward'        => 'nullable|string',
            'shipping_address.district'    => 'nullable|string',
            'shipping_address.province'    => 'nullable|string',
            'payment_method'               => 'required|in:cod,bank_transfer,momo,zalopay,vnpay,sepay',
            'notes'                        => 'nullable|string',
        ]);

        $cart = Cart::with(['items.product', 'items.variant'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Gio hang trong', 'data' => null], 422);
        }

        // Check stock for all items
        foreach ($cart->items as $item) {
            $stock = $item->variant_id ? $item->variant?->stock_qty : $item->product->stock_qty;
            if ($item->quantity > $stock) {
                return response()->json([
                    'success' => false,
                    'message' => "San pham '{$item->product->name}' khong du hang",
                    'data'    => null,
                ], 422);
            }
        }

        $subtotal    = $cart->subtotal;
        $shippingFee = $subtotal >= 500000 ? 0 : 30000; // Free ship for orders >= 500k VND
        $total       = $subtotal + $shippingFee;

        // Generate order number
        $date        = now()->format('Ymd');
        $count       = Order::whereDate('created_at', today())->count() + 1;
        $orderNumber = sprintf('VN-%s-%04d', $date, $count);

        $order = DB::transaction(function () use ($request, $data, $cart, $subtotal, $shippingFee, $total, $orderNumber) {
            $order = Order::create([
                'user_id'          => $request->user()->id,
                'order_number'     => $orderNumber,
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'shipping_fee'     => $shippingFee,
                'discount_amount'  => 0,
                'total_amount'     => $total,
                'payment_method'   => $data['payment_method'],
                'payment_status'   => 'pending',
                'shipping_address' => $data['shipping_address'],
                'notes'            => $data['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id'   => $item->product_id,
                    'variant_id'   => $item->variant_id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->variant?->name,
                    'sku'          => $item->variant?->sku ?? $item->product->sku,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $item->unit_price,
                    'subtotal'     => $item->quantity * $item->unit_price,
                ]);
            }

            // Deduct stock
            foreach ($cart->items as $item) {
                if ($item->variant_id) {
                    $item->variant->decrement('stock_qty', $item->quantity);
                } else {
                    $item->product->decrement('stock_qty', $item->quantity);
                }
            }

            // Clear cart
            $cart->items()->delete();

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'Dat hang thanh cong',
            'data'    => $order->load('items'),
        ], 201);
    }

    public function show(Request $request, $orderNumber)
    {
        $order = Order::with(['items.product', 'items.variant', 'payment'])
            ->where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Don hang khong ton tai', 'data' => null], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chi tiet don hang',
            'data'    => $order,
        ]);
    }

    public function cancel(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Don hang khong ton tai', 'data' => null], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Chi co the huy don hang o trang thai cho xu ly',
                'data'    => null,
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        // Restore stock
        foreach ($order->items as $item) {
            if ($item->variant_id) {
                \App\Models\ProductVariant::find($item->variant_id)?->increment('stock_qty', $item->quantity);
            } else {
                \App\Models\Product::find($item->product_id)?->increment('stock_qty', $item->quantity);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Da huy don hang',
            'data'    => $order,
        ]);
    }
}
