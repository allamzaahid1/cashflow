<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            'cash',
            'qris',
            'transfer',
            'ewallet',
        ]);

        return [

            'shop_id' => Shop::factory(),

            'name' => strtoupper($type),

            'type' => $type,

            'account_name' => fake()->name(),

            'account_number' => fake()->numerify('##########'),

            'qr_image' => null,

            'is_active' => true,
        ];
    }
}
