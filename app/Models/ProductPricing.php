<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductPricing extends Model
{
    use HasFactory;

    protected $table = 'product_pricing';

    protected $fillable = [
        'product_id',
        'name',
        'type',
        'interval',
        'billing_period',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'billing_period' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'pricing_currency', 'pricing_id', 'currency_id')
            ->withPivot('amount', 'setup_fee');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'pricing_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'pricing_id');
    }

    public function getPriceAttribute(): float
    {
        return (float) ($this->currencies->first()?->pivot->amount ?? 0);
    }

    public function getCycleAttribute(): string
    {
        return match ($this->interval) {
            'day' => 'Daily',
            'week' => 'Weekly',
            'month' => 'Monthly',
            'year' => 'Annually',
            default => ucfirst($this->interval ?? 'Monthly'),
        };
    }

    public function getFrequencyAttribute(): string
    {
        return $this->interval === 'year' ? '/yr' : '/mo';
    }
}
