<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'producer_id',
        'name',
        'subtitle',
        'price',
        'unit',
        'distance_km',
        'image',
        'category',
        'in_stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'distance_km' => 'decimal:1',
        'in_stock' => 'boolean',
    ];

    public function producer(): BelongsTo
    {
        return $this->belongsTo(Producer::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
