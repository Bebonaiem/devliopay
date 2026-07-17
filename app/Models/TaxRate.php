<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = [
        'name',
        'country_code',
        'state_code',
        'zip_code',
        'rate',
        'is_inclusive',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_inclusive' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function calculateTax(float $amount): float
    {
        if ($this->is_inclusive) {
            return round($amount - ($amount / (1 + $this->rate / 100)), 2);
        }

        return round($amount * ($this->rate / 100), 2);
    }

    public function getTotalWithTax(float $amount): float
    {
        if ($this->is_inclusive) {
            return $amount;
        }

        return round($amount + ($amount * ($this->rate / 100)), 2);
    }

    public static function findByLocation(?string $country = null, ?string $state = null, ?string $zip = null): ?self
    {
        $query = self::active();

        if ($zip) {
            $rate = $query->where('zip_code', $zip)->first();
            if ($rate) {
                return $rate;
            }
        }

        if ($state) {
            $rate = $query->where('state_code', $state)->first();
            if ($rate) {
                return $rate;
            }
        }

        if ($country) {
            $rate = $query->where('country_code', $country)->first();
            if ($rate) {
                return $rate;
            }
        }

        return $query->where(function ($q) {
            $q->whereNull('country_code')->orWhere('country_code', '');
        })->first();
    }
}
