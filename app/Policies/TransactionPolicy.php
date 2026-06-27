<?php

namespace App\Policies;

use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine whether the user can create transactions.
     */
    public function create(User $user): bool
    {
        return $user->shop !== null;
    }
}
