<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'repair_order_id', 'gateway',
        'gateway_transaction_id', 'amount', 'status',
        'gateway_response', 'webhook_payload', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'gateway_response' => 'array',
            'webhook_payload'  => 'array',
            'processed_at'     => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }
}
