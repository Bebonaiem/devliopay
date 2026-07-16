<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'image',
        'server_extension',
        'config_options',
        'stock',
        'per_user_limit',
        'allow_quantity',
        'email_template',
        'is_hidden',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'config_options' => 'array',
            'stock' => 'integer',
            'per_user_limit' => 'integer',
            'is_hidden' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }

    public function configOptions(): HasMany
    {
        return $this->hasMany(ProductConfig::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    public function getBasePriceAttribute(): float
    {
        return (float) $this->pricing->flatMap(fn ($p) => $p->currencies->pluck('pivot.amount'))->min() ?? 0;
    }
}
