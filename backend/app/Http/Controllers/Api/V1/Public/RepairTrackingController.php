<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\RepairOrder;
use Illuminate\Http\Request;

class RepairTrackingController extends Controller
{
    public function track(Request $request, $orderNumber)
    {
        $phone = $request->query('phone');

        if (empty($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp số điện thoại để tra cứu',
                'data'    => null,
            ], 422);
        }

        $order = RepairOrder::with(['statusLogs', 'repairService'])
            ->where('order_number', $orderNumber)
            ->where('customer_phone', $phone)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn sửa chữa với thông tin trên',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thông tin đơn sửa chữa',
            'data'    => [
                'order_number'    => $order->order_number,
                'status'          => $order->status,
                'device_type'     => $order->device_type,
                'device_brand'    => $order->device_brand,
                'device_model'    => $order->device_model,
                'service'         => $order->repairService,
                'quoted_price'    => $order->quoted_price,
                'final_price'     => $order->final_price,
                'estimated_ready_at' => $order->estimated_ready_at,
                'ready_at'        => $order->ready_at,
                'status_logs'     => $order->statusLogs,
            ],
        ]);
    }
}
