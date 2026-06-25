<?php

namespace App\Services;

use App\Models\User;

class UpgradeService
{
    /**
     * ترقية حساب المستخدم إلى Premium
     */
    public function upgradeToPremium($user)
    {
        $user->update([
            'system_role' => 'premium_user'
        ]);
        $user->refresh();
        return $user;
    }
}