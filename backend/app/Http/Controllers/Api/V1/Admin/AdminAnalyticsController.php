<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RepairOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    public function dashboard()
    {
        $now           = Carbon::now();
        $startOfMonth  = $now->copy()->startOfMonth();
        $startLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endLastMonth  = $now->copy()->subMonth()->endOfMonth();

        $totalRevenue = Order::where('status', 'delivered')->sum('total_amount');

        $totalOrders = Order::count();

        $totalRepairs = RepairOrder::count();

        $totalCustomers = User::role('customer')->count();

        $revenueThisMonth = Order::where('status', 'delivered')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        $revenueLastMonth = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
            ->sum('total_amount');

        $recentOrders = Order::with(['user'])
            ->latest()
            ->limit(10)
            ->get();

        $recentRepairs = RepairOrder::with(['customer', 'technician'])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'total_revenue'        => $totalRevenue,
                'total_orders'         => $totalOrders,
                'total_repair_orders'  => $totalRepairs,
                'total_users'          => $totalCustomers,
                'revenue_this_month'   => $revenueThisMonth,
                'revenue_last_month'   => $revenueLastMonth,
                'recent_orders'        => $recentOrders,
                'recent_repairs'       => $recentRepairs,
            ],
            'message' => 'OK',
        ]);
    }

    public function sales(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        $sales = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'sales'      => $sales,
            ],
            'message' => 'OK',
        ]);
    }

    public function repairs(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        $byStatus = RepairOrder::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $byDate = RepairOrder::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'by_status'  => $byStatus,
                'by_date'    => $byDate,
            ],
            'message' => 'OK',
        ]);
    }
}
