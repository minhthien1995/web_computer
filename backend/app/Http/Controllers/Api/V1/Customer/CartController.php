<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getOrCreateCart($userId)
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart($request->user()->id);
        $cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'success' => true,
            'message' => 'Gio hang',
            'data'    => [
                'cart'        => $cart,
                'total_items' => $cart->total_items,
                'subtotal'    => $cart->subtotal,
            ],
        ]);
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($product->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'San pham khong con kinh doanh', 'data' => null], 422);
        }

        $price = $product->effective_price;
        $stock = $product->stock_qty;

        if (!empty($data['variant_id'])) {
            $variant = ProductVariant::findOrFail($data['variant_id']);
            $price   = $product->base_price + $variant->price_modifier;
            $stock   = $variant->stock_qty;
        }

        if ($stock < $data['quantity']) {
            return response()->json(['success' => false, 'message' => 'So luong vuot qua ton kho', 'data' => null], 422);
        }

        $cart = $this->getOrCreateCart($request->user()->id);

        $item = $cart->items()
            ->where('product_id', $data['product_id'])
            ->where('variant_id', $data['variant_id'] ?? null)
            ->first();

        if ($item) {
            $newQty = $item->quantity + $data['quantity'];
            if ($newQty > $stock) {
                return response()->json(['success' => false, 'message' => 'So luong vuot qua ton kho', 'data' => null], 422);
            }
            $item->update(['quantity' => $newQty]);
        } else {
            $cart->items()->create([
                'product_id' => $data['product_id'],
                'variant_id' => $data['variant_id'] ?? null,
                'quantity'   => $data['quantity'],
                'unit_price' => $price,
            ]);
        }

        $cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'success' => true,
            'message' => 'Da them vao gio hang',
            'data'    => ['cart' => $cart, 'total_items' => $cart->total_items, 'subtotal' => $cart->subtotal],
        ]);
    }

    public function updateItem(Request $request, $id)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1']);
        $cart = $this->getOrCreateCart($request->user()->id);
        $item = $cart->items()->findOrFail($id);

        $stock = $item->variant_id
            ? ProductVariant::find($item->variant_id)?->stock_qty ?? 0
            : $item->product->stock_qty;

        if ($data['quantity'] > $stock) {
            return response()->json(['success' => false, 'message' => 'So luong vuot qua ton kho', 'data' => null], 422);
        }

        $item->update(['quantity' => $data['quantity']]);
        $cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'success' => true,
            'message' => 'Da cap nhat gio hang',
            'data'    => ['cart' => $cart, 'total_items' => $cart->total_items, 'subtotal' => $cart->subtotal],
        ]);
    }

    public function removeItem(Request $request, $id)
    {
        $cart = $this->getOrCreateCart($request->user()->id);
        $cart->items()->findOrFail($id)->delete();
        $cart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'success' => true,
            'message' => 'Da xoa khoi gio hang',
            'data'    => ['cart' => $cart, 'total_items' => $cart->total_items, 'subtotal' => $cart->subtotal],
        ]);
    }

    public function clear(Request $request)
    {
        $cart = $this->getOrCreateCart($request->user()->id);
        $cart->items()->delete();

        return response()->json(['success' => true, 'message' => 'Da xoa gio hang', 'data' => null]);
    }

    public function merge(Request $request)
    {
        $data      = $request->validate(['session_id' => 'required|string']);
        $guestCart = Cart::where('session_id', $data['session_id'])->whereNull('user_id')->with('items')->first();

        if (!$guestCart || $guestCart->items->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'Khong co gio hang khach de gop', 'data' => null]);
        }

        $userCart = $this->getOrCreateCart($request->user()->id);

        foreach ($guestCart->items as $guestItem) {
            $existing = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->where('variant_id', $guestItem->variant_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $guestItem->quantity);
            } else {
                $userCart->items()->create([
                    'product_id' => $guestItem->product_id,
                    'variant_id' => $guestItem->variant_id,
                    'quantity'   => $guestItem->quantity,
                    'unit_price' => $guestItem->unit_price,
                ]);
            }
        }

        $guestCart->delete();
        $userCart->load(['items.product.images', 'items.variant']);

        return response()->json([
            'success' => true,
            'message' => 'Da gop gio hang',
            'data'    => ['cart' => $userCart, 'total_items' => $userCart->total_items, 'subtotal' => $userCart->subtotal],
        ]);
    }
}
