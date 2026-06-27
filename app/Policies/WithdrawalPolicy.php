<?php

namespace App\Policies;

use App\Models\User;

class WithdrawalPolicy
{
    /**
     * Determine whether the user can create withdrawals.
     */
    public function create(User $user): bool
    {
        return $user->shop !== null;
    }
}
