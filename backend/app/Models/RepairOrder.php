<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairOrder extends Model
{
    protected $fillable = [
        'customer_id', 'repair_service_id', 'order_number',
        'customer_name', 'customer_phone', 'customer_email',
        'device_type', 'device_brand', 'device_model', 'device_serial',
        'issue_description', 'customer_notes',
        'status', 'technician_id', 'assigned_at',
        'diagnosis_notes', 'repair_notes',
        'quoted_price', 'final_price', 'deposit_paid',
        'estimated_ready_at', 'received_at', 'ready_at', 'delivered_at',
        'device_images',
    ];

    protected function casts(): array
    {
        return [
            'quoted_price'       => 'decimal:2',
            'final_price'        => 'decimal:2',
            'deposit_paid'       => 'decimal:2',
            'device_images'      => 'array',
            'assigned_at'        => 'datetime',
            'estimated_ready_at' => 'datetime',
            'received_at'        => 'datetime',
            'ready_at'           => 'datetime',
            'delivered_at'       => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function repairService()
    {
        return $this->belongsTo(RepairService::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(RepairStatusLog::class)->orderBy('created_at');
    }
}
