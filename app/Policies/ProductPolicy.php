<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermissionTo('products.view'); }
    public function view(User $user, Product $product): bool { return $user->hasPermissionTo('products.view'); }
    public function create(User $user): bool { return $user->hasPermissionTo('products.create'); }
    public function update(User $user, Product $product): bool { return $user->hasPermissionTo('products.edit'); }
    public function delete(User $user, Product $product): bool { return $user->hasPermissionTo('products.delete'); }
}
