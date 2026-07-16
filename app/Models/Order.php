<?php

namespace App\Models;

use App\Notifications\AdminNewOrder;
use App\Notifications\OrderCompleted;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'user_id',
        'status',
        'total',
        'subtotal',
        'tax',
        'setup_fee',
        'currency_id',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'setup_fee' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->number)) {
                $order->number = 'ORD-'.strtoupper(Str::random(4)).strtoupper(dechex((int) (microtime(true) * 1000) % 0xFFFFFF));
            }
        });

        static::created(function (Order $order) {
            ActivityLogService::log('order_created', $order, "New order #{$order->number} created", [
                'total' => $order->total,
                'user_id' => $order->user_id,
            ]);
        });

        static::updated(function (Order $order) {
            if ($order->isDirty('status') && in_array($order->status, ['completed', 'paid']) && ! in_array($order->getOriginal('status'), ['completed', 'paid'])) {
                $admins = User::where('is_admin', true)->get();
                Notification::send($admins, new AdminNewOrder($order));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, Service::class, 'order_id', 'service_id');
    }

    public function markPaidIfComplete(): void
    {
        if ($this->status === 'completed') {
            return;
        }

        $allPaid = $this->invoices()->count() > 0 &&
            $this->invoices()->where('invoices.status', '!=', 'paid')->count() === 0;

        if ($allPaid) {
            $this->update(['status' => 'completed', 'paid_at' => now()]);
            $this->user->notify(new OrderCompleted($this));
        }
    }
}
