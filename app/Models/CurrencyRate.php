<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'fetched_at',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'fetched_at' => 'datetime',
    ];

    public function isStale(int $hours = 24): bool
    {
        return ! $this->fetched_at || $this->fetched_at->diffInHours(now()) > $hours;
    }

    public function scopeForPair($query, string $from, string $to)
    {
        return $query->where('from_currency', $from)->where('to_currency', $to);
    }
}
