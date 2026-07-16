<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductConfig extends Model
{
    protected $table = 'product_config';

    protected $fillable = [
        'product_id',
        'name',
        'label',
        'type',
        'options',
        'default',
        'required',
        'is_checkout_field',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'required' => 'boolean',
            'is_checkout_field' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
