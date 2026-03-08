<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images'])
            ->withCount('variants');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        return response()->json([
            'success' => true,
            'data'    => $products,
            'message' => 'OK',
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'variants', 'images', 'specs']);

        return response()->json([
            'success' => true,
            'data'    => $product,
            'message' => 'OK',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'     => 'required|exists:categories,id',
            'brand_id'        => 'nullable|exists:brands,id',
            'name'            => 'required|string|max:255',
            'slug'            => 'required|string|max:255|unique:products,slug',
            'sku'             => 'nullable|string|max:100|unique:products,sku',
            'description'     => 'nullable|string',
            'base_price'      => 'required|numeric|min:0',
            'sale_price'      => 'nullable|numeric|min:0|lt:base_price',
            'stock_qty'       => 'integer|min:0',
            'is_featured'     => 'boolean',
            'status'          => 'in:active,inactive,out_of_stock',
            'warranty_months' => 'integer|min:0',
            'weight_grams'    => 'nullable|integer|min:0',
            'specs'           => 'nullable|array',
            'specs.*.spec_group' => 'required_with:specs|string',
            'specs.*.spec_key'   => 'required_with:specs|string',
            'specs.*.spec_value' => 'required_with:specs|string',
        ]);

        $specs = $validated['specs'] ?? [];
        unset($validated['specs']);

        $product = Product::create($validated);

        foreach ($specs as $index => $spec) {
            $product->specs()->create(array_merge($spec, ['sort_order' => $index]));
        }

        $product->load(['category', 'brand', 'specs']);

        return response()->json([
            'success' => true,
            'data'    => $product,
            'message' => 'Product created',
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id'     => 'sometimes|exists:categories,id',
            'brand_id'        => 'nullable|exists:brands,id',
            'name'            => 'sometimes|string|max:255',
            'slug'            => 'sometimes|string|max:255|unique:products,slug,' . $product->id,
            'sku'             => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'description'     => 'nullable|string',
            'base_price'      => 'sometimes|numeric|min:0',
            'sale_price'      => 'nullable|numeric|min:0',
            'stock_qty'       => 'integer|min:0',
            'is_featured'     => 'boolean',
            'status'          => 'in:active,inactive,out_of_stock',
            'warranty_months' => 'integer|min:0',
            'weight_grams'    => 'nullable|integer|min:0',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $product->fresh(['category', 'brand']),
            'message' => 'Product updated',
        ]);
    }

    public function destroy(Product $product)
    {
        // Deactivate instead of hard delete to preserve order history
        $product->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Product deactivated',
        ]);
    }

    public function uploadImage(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'url'      => 'required|string|max:500',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $isPrimary = $product->images()->count() === 0;
        $sortOrder = $product->images()->max('sort_order') + 1;

        $image = $product->images()->create([
            'url'        => $request->url,
            'alt_text'   => $request->alt_text ?? $product->name,
            'sort_order' => $sortOrder,
            'is_primary' => $isPrimary,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $image,
            'message' => 'Image uploaded',
        ], 201);
    }

    public function deleteImage($id, $imageId)
    {
        $product = Product::findOrFail($id);
        $image   = ProductImage::findOrFail($imageId);

        if ($image->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Image does not belong to this product',
            ], 403);
        }

        $wasPrimary = $image->is_primary;
        $image->delete();

        // Reassign primary if needed
        if ($wasPrimary) {
            $first = $product->images()->orderBy('sort_order')->first();
            if ($first) {
                $first->update(['is_primary' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Image deleted',
        ]);
    }
}
