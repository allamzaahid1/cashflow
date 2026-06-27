<?php

use App\Models\User;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\ShopService;

test('default categories are automatically generated when a shop is created', function () {
    $user = User::factory()->create();
    $shopService = app(ShopService::class);

    $shop = $shopService->create($user, [
        'name' => 'Warung Test',
        'phone' => '081234567890',
        'address' => 'Jl. Test No. 1',
    ]);

    $this->assertDatabaseHas('shops', ['id' => $shop->id, 'name' => 'Warung Test']);

    // Check 2 default income categories
    $this->assertDatabaseHas('categories', ['shop_id' => $shop->id, 'name' => 'Penjualan', 'type' => 'income']);
    $this->assertDatabaseHas('categories', ['shop_id' => $shop->id, 'name' => 'Modal', 'type' => 'income']);

    // Check 4 default expense categories
    $this->assertDatabaseHas('categories', ['shop_id' => $shop->id, 'name' => 'Gaji', 'type' => 'expense']);
    $this->assertDatabaseHas('categories', ['shop_id' => $shop->id, 'name' => 'Transport', 'type' => 'expense']);
    $this->assertDatabaseHas('categories', ['shop_id' => $shop->id, 'name' => 'Internet', 'type' => 'expense']);
    $this->assertDatabaseHas('categories', ['shop_id' => $shop->id, 'name' => 'Operasional', 'type' => 'expense']);

    expect($shop->categories()->count())->toBe(6);
});

test('user can create a custom category', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->post('/categories', [
            'name' => 'Catering Baru',
            'type' => 'income',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/settings');

    $this->assertDatabaseHas('categories', [
        'shop_id' => $shop->id,
        'name' => 'Catering Baru',
        'type' => 'income',
    ]);
});

test('category name must be unique per shop and type', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    Category::factory()->create(['shop_id' => $shop->id, 'name' => 'Catering', 'type' => 'income']);

    $response = $this
        ->actingAs($user)
        ->from('/settings')
        ->post('/categories', [
            'name' => 'Catering',
            'type' => 'income',
        ]);

    $response->assertSessionHasErrors('name');
    $response->assertRedirect('/settings');
});

test('different shops can have categories with the same name', function () {
    $user1 = User::factory()->create();
    $shop1 = Shop::factory()->create(['user_id' => $user1->id]);

    $user2 = User::factory()->create();
    $shop2 = Shop::factory()->create(['user_id' => $user2->id]);

    Category::factory()->create(['shop_id' => $shop1->id, 'name' => 'Catering', 'type' => 'income']);

    $response = $this
        ->actingAs($user2)
        ->post('/categories', [
            'name' => 'Catering',
            'type' => 'income',
        ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('categories', ['shop_id' => $shop2->id, 'name' => 'Catering', 'type' => 'income']);
});

test('user can update a category name', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create(['shop_id' => $shop->id, 'name' => 'Old Name', 'type' => 'income']);

    $response = $this
        ->actingAs($user)
        ->put("/categories/{$category->id}", [
            'name' => 'New Name',
            'type' => 'income',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/settings');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'New Name',
    ]);
});

test('user cannot update a category to a duplicate name within the same shop and type', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $category1 = Category::factory()->create(['shop_id' => $shop->id, 'name' => 'Kategori 1', 'type' => 'income']);
    $category2 = Category::factory()->create(['shop_id' => $shop->id, 'name' => 'Kategori 2', 'type' => 'income']);

    $response = $this
        ->actingAs($user)
        ->put("/categories/{$category2->id}", [
            'name' => 'Kategori 1',
            'type' => 'income',
        ]);

    $response->assertSessionHasErrors('name');
});

test('updating category name without changing it is allowed', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create(['shop_id' => $shop->id, 'name' => 'Unchanged', 'type' => 'income']);

    $response = $this
        ->actingAs($user)
        ->put("/categories/{$category->id}", [
            'name' => 'Unchanged',
            'type' => 'income',
        ]);

    $response->assertSessionHasNoErrors();
});

test('user cannot update category belonging to another shop', function () {
    $user1 = User::factory()->create();
    $shop1 = Shop::factory()->create(['user_id' => $user1->id]);
    $category = Category::factory()->create(['shop_id' => $shop1->id, 'name' => 'Secret Category', 'type' => 'income']);

    $user2 = User::factory()->create();
    $shop2 = Shop::factory()->create(['user_id' => $user2->id]);

    $response = $this
        ->actingAs($user2)
        ->put("/categories/{$category->id}", [
            'name' => 'Hacked Name',
            'type' => 'income',
        ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Secret Category']);
});

test('user cannot delete category belonging to another shop', function () {
    $user1 = User::factory()->create();
    $shop1 = Shop::factory()->create(['user_id' => $user1->id]);
    $category = Category::factory()->create(['shop_id' => $shop1->id, 'name' => 'Secret Category', 'type' => 'income']);

    $user2 = User::factory()->create();
    $shop2 = Shop::factory()->create(['user_id' => $user2->id]);

    $response = $this
        ->actingAs($user2)
        ->delete("/categories/{$category->id}");

    $response->assertForbidden();
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('category cannot be deleted if it has transactions', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create(['shop_id' => $shop->id, 'name' => 'Used Category', 'type' => 'income']);
    
    // Create a transaction linked to the category
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $category->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/settings')
        ->delete("/categories/{$category->id}");

    $response->assertSessionHasErrors('category');
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('category can be deleted if it has no transactions', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create(['shop_id' => $shop->id, 'name' => 'Unused Category', 'type' => 'income']);

    $response = $this
        ->actingAs($user)
        ->from('/settings')
        ->delete("/categories/{$category->id}");

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/settings');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});
