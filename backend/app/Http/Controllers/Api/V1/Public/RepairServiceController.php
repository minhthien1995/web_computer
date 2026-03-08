<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\RepairService;

class RepairServiceController extends Controller
{
    public function index()
    {
        $services = RepairService::active()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dịch vụ sửa chữa',
            'data'    => $services,
        ]);
    }

    public function show($slug)
    {
        $service = RepairService::where('slug', $slug)->active()->first();

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Dịch vụ không tồn tại',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chi tiết dịch vụ',
            'data'    => $service,
        ]);
    }
}
