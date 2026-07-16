<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceUpgrade extends Model
{
    protected $fillable = [
        'service_id',
        'user_id',
        'from_pricing_id',
        'to_pricing_id',
        'price_difference',
        'credit_applied',
        'amount_due',
        'status',
        'type',
        'processed_at',
    ];

    protected $casts = [
        'price_difference' => 'decimal:2',
        'credit_applied' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromPricing()
    {
        return $this->belongsTo(ProductPricing::class, 'from_pricing_id');
    }

    public function toPricing()
    {
        return $this->belongsTo(ProductPricing::class, 'to_pricing_id');
    }

    public function isUpgrade(): bool
    {
        return $this->type === 'upgrade';
    }

    public function isDowngrade(): bool
    {
        return $this->type === 'downgrade';
    }
}
