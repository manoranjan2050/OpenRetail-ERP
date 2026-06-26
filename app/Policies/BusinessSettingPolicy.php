<?php

namespace App\Policies;

use App\Models\BusinessSetting;
use App\Models\User;

class BusinessSettingPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermissionTo('settings.view'); }
    public function update(User $user, BusinessSetting $setting = null): bool { return $user->hasPermissionTo('settings.edit'); }
}
