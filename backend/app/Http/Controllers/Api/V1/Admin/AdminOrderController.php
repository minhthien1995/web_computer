<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    // Valid status transitions
    private array $allowedTransitions = [
        'pending'    => ['confirmed', 'cancelled'],
        'confirmed'  => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped'    => ['delivered'],
        'delivered'  => [],
        'cancelled'  => [],
        'refunded'   => [],
    ];

    public function index(Request $request)
    {
        $query = Order::with(['user', 'items'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->paginate(20)->withQueryString();

        return response()->json([
            'success' => true,
            'data'    => $orders,
            'message' => 'OK',
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['user', 'items.product', 'payment'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $order,
            'message' => 'OK',
        ]);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status'         => 'sometimes|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'sometimes|in:pending,paid,failed,refunded',
        ]);

        // Validate status transition
        if (isset($validated['status'])) {
            $current = $order->status;
            $next    = $validated['status'];
            $allowed = $this->allowedTransitions[$current] ?? [];

            if ($next !== $current && !in_array($next, $allowed)) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => "Cannot transition order from [{$current}] to [{$next}]",
                ], 422);
            }
        }

        $order->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $order->fresh(),
            'message' => 'Order updated',
        ]);
    }
}
