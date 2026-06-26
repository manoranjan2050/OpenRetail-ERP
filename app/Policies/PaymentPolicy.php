<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermissionTo('payments.view'); }
    public function create(User $user): bool { return $user->hasPermissionTo('payments.record'); }
}
