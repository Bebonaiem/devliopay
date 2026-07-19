<?php

namespace App\Models;

use App\Models\Invoice;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'product_id',
        'pricing_id',
        'order_id',
        'status',
        'config_options',
        'server_properties',
        'server_extension',
        'external_id',
        'activated_at',
        'suspended_at',
        'terminated_at',
        'next_billing_at',
    ];

    protected function casts(): array
    {
        return [
            'config_options' => 'array',
            'server_properties' => 'array',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'terminated_at' => 'datetime',
            'next_billing_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Service $service) {
            if (empty($service->uuid)) {
                $service->uuid = Str::uuid();
            }
        });

        static::updated(function (Service $service) {
            if ($service->isDirty('status')) {
                $old = $service->getOriginal('status');
                $new = $service->status;
                ActivityLogService::log("service_{$new}", $service, "Service status changed from {$old} to {$new}");
            }
        });

        static::created(function (Service $service) {
            ActivityLogService::log('service_created', $service, 'New service created for '.($service->product?->name ?? 'product'));
        });

        static::deleting(function (Service $service) {
            Invoice::where('service_id', $service->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->update(['status' => 'cancelled']);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(ProductPricing::class, 'pricing_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(TicketThread::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function addons(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'service_addons')
            ->withPivot(['price', 'status', 'activated_at', 'next_billing_at'])
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isPterodactyl(): bool
    {
        return $this->server_extension === 'pterodactyl';
    }

    public function hasServer(): bool
    {
        return ! empty($this->server_properties['server_id']);
    }
}
