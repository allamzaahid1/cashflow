<?php

namespace App\Policies;

use App\Models\PaymentMethod;
use App\Models\User;

class PaymentMethodPolicy
{
    /**
     * Determine whether the user can update the payment method.
     */
    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->shop !== null && $paymentMethod->shop_id === $user->shop->id;
    }

    /**
     * Determine whether the user can delete the payment method.
     */
    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->shop !== null && $paymentMethod->shop_id === $user->shop->id;
    }
}
