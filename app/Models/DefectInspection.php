<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefectInspection extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'inspection_time' => 'datetime',
        'is_approved' => 'boolean',
        'temperature_celsius' => 'decimal:2',
        'discrepancy_qty' => 'decimal:2',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
