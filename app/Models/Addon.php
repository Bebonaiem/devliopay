<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'price',
        'billing_interval',
        'billing_period',
        'is_active',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_required' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function serviceAddons()
    {
        return $this->hasMany(ServiceAddon::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_addons')
            ->withPivot(['price', 'status', 'activated_at', 'next_billing_at'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
