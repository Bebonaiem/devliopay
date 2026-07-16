<?php

namespace App\Models;

use App\Notifications\AdminPaymentReceived;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'invoice_id',
        'status',
        'amount',
        'currency_id',
        'gateway',
        'gateway_id',
        'gateway_data',
        'description',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_data' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Transaction $transaction) {
            if (empty($transaction->uuid)) {
                $transaction->uuid = Str::uuid();
            }
        });

        static::created(function (Transaction $transaction) {
            if ($transaction->status === 'completed') {
                $admins = User::where('is_admin', true)->get();
                Notification::send($admins, new AdminPaymentReceived($transaction));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
