<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\RepairOrder;
use App\Models\RepairStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminRepairOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = RepairOrder::with(['customer', 'technician'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
        $repairOrder = RepairOrder::with(['customer', 'technician', 'statusLogs', 'repairService'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $repairOrder,
            'message' => 'OK',
        ]);
    }

    public function update(Request $request, $id)
    {
        $repairOrder = RepairOrder::findOrFail($id);

        $validated = $request->validate([
            'technician_id'      => 'nullable|exists:users,id',
            'quoted_price'       => 'nullable|numeric|min:0',
            'final_price'        => 'nullable|numeric|min:0',
            'estimated_ready_at' => 'nullable|date',
            'diagnosis_notes'    => 'nullable|string',
            'repair_notes'       => 'nullable|string',
        ]);

        // Auto-set assigned_at when technician is assigned
        if (isset($validated['technician_id']) && $validated['technician_id'] && !$repairOrder->technician_id) {
            $validated['assigned_at'] = Carbon::now();
        }

        $repairOrder->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $repairOrder->fresh(),
            'message' => 'Repair order updated',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $repairOrder = RepairOrder::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:received,diagnosing,waiting_parts,repairing,quality_check,ready,delivered,cancelled',
            'notes'  => 'nullable|string',
        ]);

        $oldStatus = $repairOrder->status;
        $newStatus = $validated['status'];

        $timestamps = [];

        // Auto-set timestamps based on status
        if ($newStatus === 'ready' && !$repairOrder->ready_at) {
            $timestamps['ready_at'] = Carbon::now();
        }

        if ($newStatus === 'delivered' && !$repairOrder->delivered_at) {
            $timestamps['delivered_at'] = Carbon::now();
        }

        $repairOrder->update(array_merge(['status' => $newStatus], $timestamps));

        // Log the status change
        RepairStatusLog::create([
            'repair_order_id' => $repairOrder->id,
            'status'          => $newStatus,
            'notes'           => $validated['notes'] ?? "Status changed from {$oldStatus} to {$newStatus}",
            'changed_by'      => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $repairOrder->fresh(['statusLogs']),
            'message' => 'Status updated',
        ]);
    }
}
