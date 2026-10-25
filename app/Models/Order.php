<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'loading_plan_date' => 'datetime',
        'unloading_plan_date' => 'datetime',
        'selling_price' => 'decimal:2',
        'tax_percent' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(ClientInvoice::class);
    }
}
