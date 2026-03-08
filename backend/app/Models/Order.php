<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'status',
        'subtotal', 'shipping_fee', 'discount_amount', 'total_amount',
        'payment_method', 'payment_status', 'payment_reference', 'paid_at',
        'shipping_address', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'         => 'decimal:2',
            'shipping_fee'     => 'decimal:2',
            'discount_amount'  => 'decimal:2',
            'total_amount'     => 'decimal:2',
            'shipping_address' => 'array',
            'paid_at'          => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
