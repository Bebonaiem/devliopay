<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingCurrency extends Model
{
    protected $table = 'pricing_currency';

    protected $fillable = [
        'pricing_id',
        'currency_id',
        'amount',
        'setup_fee',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'setup_fee' => 'decimal:2',
        ];
    }

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(ProductPricing::class, 'pricing_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
