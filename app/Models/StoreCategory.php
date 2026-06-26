<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreCategory extends Model
{
    protected $fillable = ['slug', 'label', 'license_field_schema'];

    protected $casts = ['license_field_schema' => 'array'];

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }
}
