<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBill extends Model
{
    protected $guarded = ['id'];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class);
    }
}
