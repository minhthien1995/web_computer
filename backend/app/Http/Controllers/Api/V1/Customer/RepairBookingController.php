<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\RepairOrder;
use App\Models\RepairStatusLog;
use Illuminate\Http\Request;

class RepairBookingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'repair_service_id' => 'nullable|exists:repair_services,id',
            'customer_name'     => 'required|string|max:255',
            'customer_phone'    => 'required|string|max:20',
            'customer_email'    => 'nullable|email',
            'device_type'       => 'required|string|max:100',
            'device_brand'      => 'required|string|max:100',
            'device_model'      => 'required|string|max:100',
            'device_serial'     => 'nullable|string|max:100',
            'issue_description' => 'required|string',
            'customer_notes'    => 'nullable|string',
        ]);

        $date        = now()->format('Ymd');
        $count       = RepairOrder::whereDate('created_at', today())->count() + 1;
        $orderNumber = sprintf('RP-%s-%04d', $date, $count);

        $repairOrder = RepairOrder::create([
            'customer_id'       => $request->user()->id,
            'repair_service_id' => $data['repair_service_id'] ?? null,
            'order_number'      => $orderNumber,
            'customer_name'     => $data['customer_name'],
            'customer_phone'    => $data['customer_phone'],
            'customer_email'    => $data['customer_email'] ?? null,
            'device_type'       => $data['device_type'],
            'device_brand'      => $data['device_brand'],
            'device_model'      => $data['device_model'],
            'device_serial'     => $data['device_serial'] ?? null,
            'issue_description' => $data['issue_description'],
            'customer_notes'    => $data['customer_notes'] ?? null,
            'status'            => 'received',
            'received_at'       => now(),
        ]);

        // Log initial status
        RepairStatusLog::create([
            'repair_order_id' => $repairOrder->id,
            'status'          => 'received',
            'notes'           => 'Tiep nhan thiet bi',
            'changed_by'      => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dat lich sua chua thanh cong',
            'data'    => $repairOrder->load(['repairService', 'statusLogs']),
        ], 201);
    }

    public function index(Request $request)
    {
        $orders = RepairOrder::with(['repairService'])
            ->where('customer_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Danh sach don sua chua',
            'data'    => $orders,
        ]);
    }

    public function show(Request $request, $orderNumber)
    {
        $order = RepairOrder::with(['repairService', 'statusLogs.creator'])
            ->where('order_number', $orderNumber)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Don sua chua khong ton tai', 'data' => null], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chi tiet don sua chua',
            'data'    => $order,
        ]);
    }
}
