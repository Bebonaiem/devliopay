<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_amount',
        'max_uses',
        'used_count',
        'is_active',
        'starts_at',
        'expires_at',
        'applicable_products',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'applicable_products' => 'array',
        ];
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if (! $this->isValid()) {
            return 0;
        }
        if ($amount < $this->min_amount) {
            return 0;
        }

        if ($this->type === 'percentage') {
            $discount = $amount * ($this->value / 100);
        } else {
            $discount = min($this->value, $amount);
        }

        return round($discount, 2);
    }

    public function apply(): void
    {
        static::where('id', $this->id)->increment('used_count');
    }
}
