<?php

namespace Database\Factories;

use App\Models\Withdrawal;
use App\Models\PaymentMethod;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),

            'payment_method_id' => PaymentMethod::factory(),

            'amount' => fake()->numberBetween(50000, 500000),

            'admin_fee' => fake()->randomElement([
                0,
                2500,
                5000,
            ]),

            'status' => fake()->randomElement([
                'pending',
                'approved',
                'rejected',
            ]),

            'withdrawal_date' => fake()->date(),

            'notes' => fake()->sentence(),
        ];
    }
}
