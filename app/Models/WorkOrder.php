<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrder extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'buying_cost' => 'decimal:2',
        'additional_cost' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function deliveryProof(): HasOne
    {
        return $this->hasOne(DeliveryProof::class);
    }

    public function trackingLogs(): HasMany
    {
        return $this->hasMany(ShipmentTrackingLog::class);
    }

    public function vendorBill(): HasOne
    {
        return $this->hasOne(VendorBill::class);
    }

    public function defectInspections(): HasMany
    {
        return $this->hasMany(DefectInspection::class);
    }

    /**
     * Hitung Gross Margin (Keuntungan Kotor) = Harga Jual Klien - Total Biaya Vendor
     */
    public function getGrossProfitAttribute(): float
    {
        $revenue = (float) ($this->order->selling_price ?? 0);
        $cost = (float) $this->buying_cost + (float) $this->additional_cost;
        return $revenue - $cost;
    }
}
