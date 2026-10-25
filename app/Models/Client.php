<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $guarded = ['id'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function mouContracts(): HasMany
    {
        return $this->hasMany(MouContract::class, 'party_id')->where('party_type', 'CLIENT');
    }
}
