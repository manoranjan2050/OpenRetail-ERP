<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'number', 'store_id', 'customer_id', 'salesman_id',
        'subtotal', 'tax_total', 'discount', 'grand_total', 'paid_amount',
        'payment_mode', 'status', 'invoice_date', 'due_date', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['number', 'status', 'grand_total'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function salesman(): BelongsTo { return $this->belongsTo(User::class, 'salesman_id'); }
    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }
}
