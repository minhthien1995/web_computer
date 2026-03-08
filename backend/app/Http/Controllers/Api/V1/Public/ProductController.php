<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images' => fn($q) => $q->where('is_primary', true)])
            ->active();

        // Filter by category slug
        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        // Filter by brand slug
        if ($request->filled('brand')) {
            $query->whereHas('brand', fn($q) => $q->where('slug', $request->brand));
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sorting
        match ($request->input('sort', 'newest')) {
            'price_asc'  => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'name'       => $query->orderBy('name', 'asc'),
            default      => $query->orderBy('created_at', 'desc'),
        };

        $products = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Danh sách sản phẩm',
            'data'    => $products,
        ]);
    }

    public function show($slug)
    {
        $product = Product::with(['category', 'brand', 'variants', 'images', 'specs'])
            ->where('slug', $slug)
            ->active()
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chi tiết sản phẩm',
            'data'    => $product,
        ]);
    }

    public function featured()
    {
        $products = Product::with(['category', 'brand', 'images' => fn($q) => $q->where('is_primary', true)])
            ->active()
            ->featured()
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Sản phẩm nổi bật',
            'data'    => $products,
        ]);
    }

    public function search(Request $request)
    {
        $q = $request->input('q', '');

        if (strlen($q) < 2) {
            return response()->json([
                'success' => true,
                'message' => 'Kết quả tìm kiếm',
                'data'    => [],
            ]);
        }

        $products = Product::with(['brand', 'images' => fn($q) => $q->where('is_primary', true)])
            ->active()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Kết quả tìm kiếm',
            'data'    => $products,
        ]);
    }
}
