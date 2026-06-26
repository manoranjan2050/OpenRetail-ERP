<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\Auth;

class LedgerService
{
    public function debit(Customer $customer, float $amount, string $source, int $sourceId, string $narration): LedgerEntry
    {
        $balance = $customer->balance + $amount;
        return LedgerEntry::create([
            'customer_id' => $customer->id,
            'type' => 'debit',
            'source' => $source,
            'source_id' => $sourceId,
            'amount' => $amount,
            'balance_after' => $balance,
            'narration' => $narration,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);
    }

    public function credit(Customer $customer, float $amount, string $source, int $sourceId, string $narration): LedgerEntry
    {
        $balance = $customer->balance - $amount;
        return LedgerEntry::create([
            'customer_id' => $customer->id,
            'type' => 'credit',
            'source' => $source,
            'source_id' => $sourceId,
            'amount' => $amount,
            'balance_after' => $balance,
            'narration' => $narration,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);
    }
}
