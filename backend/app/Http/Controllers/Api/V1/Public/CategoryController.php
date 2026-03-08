<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['children' => fn($q) => $q->active()])
            ->active()
            ->whereNull('parent_id')
            ->withCount(['products' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Danh mục sản phẩm',
            'data'    => $categories,
        ]);
    }

    public function show($slug)
    {
        $category = Category::with(['children' => fn($q) => $q->active()])
            ->active()
            ->where('slug', $slug)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Danh mục không tồn tại',
                'data'    => null,
            ], 404);
        }

        // Get products for this category (and children)
        $categoryIds = collect([$category->id])
            ->merge($category->children->pluck('id'))
            ->toArray();

        $products = \App\Models\Product::with(['brand', 'images' => fn($q) => $q->where('is_primary', true)])
            ->active()
            ->whereIn('category_id', $categoryIds)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Chi tiết danh mục',
            'data'    => [
                'category' => $category,
                'products' => $products,
            ],
        ]);
    }
}
