<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producer extends Model
{
    protected $fillable = [
        'name',
        'category',
        'distance_km',
        'bio_certified',
        'avatar',
        'cover_image',
        'description',
        'fair_share_percent',
        'farmers_name',
        'quote',
        'varieties_count',
        'hectares',
    ];

    protected $casts = [
        'bio_certified' => 'boolean',
        'distance_km' => 'decimal:1',
        'fair_share_percent' => 'decimal:1',
        'hectares' => 'decimal:1',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
