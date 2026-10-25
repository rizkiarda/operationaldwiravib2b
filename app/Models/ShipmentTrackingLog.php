<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentTrackingLog extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'logged_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function loggedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by_user_id');
    }
}
