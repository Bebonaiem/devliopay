<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceAddon extends Model
{
    protected $fillable = [
        'service_id',
        'addon_id',
        'price',
        'status',
        'activated_at',
        'next_billing_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'activated_at' => 'datetime',
        'next_billing_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }
}
