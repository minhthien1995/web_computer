<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairService extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'category',
        'estimated_days_min', 'estimated_days_max',
        'base_price', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active'  => 'boolean',
        ];
    }

    public function repairOrders()
    {
        return $this->hasMany(RepairOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
