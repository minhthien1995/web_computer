<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'description',
        'base_price', 'sale_price', 'stock_qty', 'sku', 'is_featured',
        'status', 'warranty_months', 'weight_grams',
    ];

    protected function casts(): array
    {
        return [
            'base_price'  => 'decimal:2',
            'sale_price'  => 'decimal:2',
            'is_featured' => 'boolean',
            'stock_qty'   => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function specs()
    {
        return $this->hasMany(ProductSpec::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Effective price: sale_price if set, otherwise base_price
    public function getEffectivePriceAttribute()
    {
        return $this->sale_price ?? $this->base_price;
    }
}
