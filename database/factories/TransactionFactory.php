<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
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

            'category_id' => Category::factory(),

            'payment_method_id' => PaymentMethod::factory(),

            'transaction_code' => fake()->unique()->bothify('TRX######'),

            'amount' => fake()->numberBetween(10000, 500000),

            'description' => fake()->sentence(),

            'proof_image' => null,

            'transaction_date' => fake()->date(),
        ];
    }
}
