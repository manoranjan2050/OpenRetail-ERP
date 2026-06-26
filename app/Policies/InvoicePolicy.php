<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool { return $user->hasPermissionTo('invoices.view'); }
    public function view(User $user, Invoice $invoice): bool { return $user->hasPermissionTo('invoices.view'); }
    public function create(User $user): bool { return $user->hasPermissionTo('invoices.create'); }
    public function void(User $user, Invoice $invoice): bool { return $user->hasPermissionTo('invoices.void'); }
}
