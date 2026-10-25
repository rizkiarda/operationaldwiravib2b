<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transporter extends Model
{
    protected $guarded = ['id'];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function mouContracts(): HasMany
    {
        return $this->hasMany(MouContract::class, 'party_id')->where('party_type', 'TRANSPORTER');
    }
}
