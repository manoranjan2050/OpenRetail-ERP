<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'change_qty', 'balance_qty', 'type', 'ref_id', 'note', 'created_by', 'created_at',
    ];

    protected $casts = [
        'change_qty' => 'decimal:3',
        'balance_qty' => 'decimal:3',
        'created_at' => 'datetime',
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
