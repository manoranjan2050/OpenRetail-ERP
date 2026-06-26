<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = [
        'business_name', 'gstin', 'pan', 'address', 'phone', 'email',
        'logo', 'currency', 'timezone', 'invoice_prefix', 'invoice_next_number',
        'terms', 'upi_id', 'payee_name', 'bank_account', 'ifsc',
        'show_qr_on_invoice', 'installed',
    ];

    protected $casts = [
        'show_qr_on_invoice' => 'boolean',
        'installed' => 'boolean',
        'invoice_next_number' => 'integer',
        'bank_account' => 'encrypted',
        'upi_id' => 'encrypted',
    ];

    public static function instance(): self
    {
        return self::firstOrCreate([], ['business_name' => 'My Business', 'installed' => false]);
    }
}
