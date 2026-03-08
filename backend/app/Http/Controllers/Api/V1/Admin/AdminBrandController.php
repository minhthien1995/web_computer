<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class AdminBrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $brands,
            'message' => 'OK',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'slug'      => 'required|string|max:255|unique:brands,slug',
            'is_active' => 'boolean',
        ]);

        $brand = Brand::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $brand,
            'message' => 'Brand created',
        ], 201);
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'slug'      => 'required|string|max:255|unique:brands,slug,' . $brand->id,
            'is_active' => 'boolean',
        ]);

        $brand->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $brand,
            'message' => 'Brand updated',
        ]);
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Cannot delete brand with products',
            ], 422);
        }

        $brand->delete();

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Brand deleted',
        ]);
    }
}
