<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    /**
     * Generate default categories for a shop.
     */
    public function generateDefaultCategories(Shop $shop): void
    {
        DB::transaction(function () use ($shop) {
            $defaults = [
                ['name' => 'Penjualan', 'type' => 'income'],
                ['name' => 'Modal', 'type' => 'income'],
                ['name' => 'Gaji', 'type' => 'expense'],
                ['name' => 'Transport', 'type' => 'expense'],
                ['name' => 'Internet', 'type' => 'expense'],
                ['name' => 'Operasional', 'type' => 'expense'],
            ];

            foreach ($defaults as $category) {
                Category::create([
                    'shop_id' => $shop->id,
                    'name' => $category['name'],
                    'type' => $category['type'],
                ]);
            }
        });
    }

    /**
     * Create a new category for a shop.
     */
    public function create(Shop $shop, array $data): Category
    {
        return DB::transaction(function () use ($shop, $data) {
            return Category::create([
                'shop_id' => $shop->id,
                'name' => $data['name'],
                'type' => $data['type'],
            ]);
        });
    }

    /**
     * Update an existing category.
     */
    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $category->update([
                'name' => $data['name'],
                'type' => $data['type'] ?? $category->type,
            ]);

            return $category;
        });
    }

    /**
     * Delete a category if it has no transactions.
     *
     * @throws ValidationException
     */
    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category) {
            if ($category->transactions()->exists()) {
                throw ValidationException::withMessages([
                    'category' => 'Kategori ini tidak dapat dihapus karena memiliki transaksi terkait.',
                ]);
            }

            $category->delete();
        });
    }
}
