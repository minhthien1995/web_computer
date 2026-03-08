<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // Total number of items in the cart
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    // Cart subtotal
    public function getSubtotalAttribute()
    {
        return $this->items->sum(fn ($item) => $item->quantity * $item->unit_price);
    }
}
