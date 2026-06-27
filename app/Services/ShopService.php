<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ShopService
{
    public function __construct(
        protected CategoryService $categoryService,
        protected PaymentMethodService $paymentMethodService,
    ) {
    }

    public function create(User $user, array $data): Shop
    {
        return DB::transaction(function () use ($user, $data) {

            $shop = Shop::create([

                'user_id' => $user->id,

                'name' => $data['name'],

                'phone' => $data['phone'] ?? null,

                'address' => $data['address'] ?? null,

                'logo' => null,

            ]);

            $this->categoryService->generateDefaultCategories($shop);

            $this->paymentMethodService->createDefaultCashMethod($shop);

            return $shop;

        });
    }
}