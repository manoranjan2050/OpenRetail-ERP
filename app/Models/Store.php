<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Store extends Model
{
    protected $fillable = [
        'name', 'store_category_id', 'address', 'phone', 'license_fields', 'is_default', 'is_active',
    ];

    protected $casts = [
        'license_fields' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(StoreCategory::class, 'store_category_id');
    }
}
