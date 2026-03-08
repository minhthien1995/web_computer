<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairStatusLog extends Model
{
    protected $fillable = [
        'repair_order_id', 'status', 'notes', 'changed_by',
    ];

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
