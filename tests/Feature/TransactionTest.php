<?php

use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('authenticated user with shop can view transactions index', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->get('/transactions');

    $response->assertOk();
    $response->assertViewHasAll(['incomeCategories', 'expenseCategories', 'nonCashPaymentMethods']);
});

test('user can record a transaction using Cash payment method', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $cashMethod = PaymentMethod::factory()->create([
        'shop_id' => $shop->id,
        'type' => 'cash',
        'name' => 'Tunai',
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/transactions', [
            'amount' => 50000,
            'category_id' => $category->id,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Penjualan Nasi Goreng',
            'payment_method_type' => 'cash',
            'proof_image' => UploadedFile::fake()->create('proof.png', 100),
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/transactions');

    $transaction = Transaction::first();
    expect($transaction)->not->toBeNull();
    expect($transaction->payment_method_id)->toBe($cashMethod->id);
    expect($transaction->amount)->toEqual('50000.00');

    Storage::disk('public')->assertExists($transaction->proof_image);
});

test('user can record a transaction using custom Non-Cash payment method', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $nonCashMethod = PaymentMethod::factory()->create([
        'shop_id' => $shop->id,
        'type' => 'qris',
        'name' => 'QRIS Gopay',
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/transactions', [
            'amount' => 35000,
            'category_id' => $category->id,
            'transaction_date' => now()->format('Y-m-d'),
            'payment_method_type' => 'nontunai',
            'payment_method_id' => $nonCashMethod->id,
            'proof_image' => UploadedFile::fake()->create('proof.pdf', 200),
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/transactions');

    $transaction = Transaction::first();
    expect($transaction)->not->toBeNull();
    expect($transaction->payment_method_id)->toBe($nonCashMethod->id);

    Storage::disk('public')->assertExists($transaction->proof_image);
});

test('transaction code is generated sequentially for each day', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    PaymentMethod::factory()->create([
        'shop_id' => $shop->id,
        'type' => 'cash',
        'name' => 'Tunai',
        'is_active' => true,
    ]);

    $dateStr = now()->format('Ymd');

    // First transaction
    $response1 = $this
        ->actingAs($user)
        ->post('/transactions', [
            'amount' => 10000,
            'category_id' => $category->id,
            'transaction_date' => now()->format('Y-m-d'),
            'payment_method_type' => 'cash',
            'proof_image' => UploadedFile::fake()->create('proof1.png', 100),
        ]);
    $response1->assertSessionHasNoErrors();
    $tx1 = Transaction::orderBy('id', 'desc')->first();
    expect($tx1->transaction_code)->toBe("TRX-{$dateStr}-000001");

    // Second transaction
    $response2 = $this
        ->actingAs($user)
        ->post('/transactions', [
            'amount' => 20000,
            'category_id' => $category->id,
            'transaction_date' => now()->format('Y-m-d'),
            'payment_method_type' => 'cash',
            'proof_image' => UploadedFile::fake()->create('proof2.png', 100),
        ]);
    $response2->assertSessionHasNoErrors();
    $tx2 = Transaction::orderBy('id', 'desc')->first();
    expect($tx2->transaction_code)->toBe("TRX-{$dateStr}-000002");
});

test('validation fails when parameters are missing or invalid', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->from('/transactions')
        ->post('/transactions', [
            'amount' => -100, // Negative amount
            'category_id' => 9999, // Non-existent category
            'transaction_date' => 'invalid-date',
            'payment_method_type' => 'nontunai',
            'payment_method_id' => 9999, // Non-existent payment method
        ]);

    $response->assertSessionHasErrors(['amount', 'category_id', 'transaction_date', 'payment_method_id', 'proof_image']);
});

test('user cannot record a transaction using another shop category or payment method', function () {
    $user1 = User::factory()->create();
    $shop1 = Shop::factory()->create(['user_id' => $user1->id]);
    $categoryOther = Category::factory()->create(['shop_id' => $shop1->id, 'type' => 'income']);

    $user2 = User::factory()->create();
    $shop2 = Shop::factory()->create(['user_id' => $user2->id]);

    $response = $this
        ->actingAs($user2)
        ->post('/transactions', [
            'amount' => 5000,
            'category_id' => $categoryOther->id, // Belongs to shop1, but actingAs user2/shop2
            'transaction_date' => now()->format('Y-m-d'),
            'payment_method_type' => 'cash',
            'proof_image' => UploadedFile::fake()->create('proof.png', 100),
        ]);

    $response->assertSessionHasErrors('category_id');
});

test('only authenticated shop owner can access transaction proof', function () {
    Storage::fake('public');

    $user1 = User::factory()->create();
    $shop1 = Shop::factory()->create(['user_id' => $user1->id]);
    $category1 = Category::factory()->create(['shop_id' => $shop1->id, 'type' => 'income']);
    $cashMethod1 = PaymentMethod::factory()->create(['shop_id' => $shop1->id, 'type' => 'cash']);

    $tx = Transaction::factory()->create([
        'shop_id' => $shop1->id,
        'category_id' => $category1->id,
        'payment_method_id' => $cashMethod1->id,
        'amount' => 5000,
        'proof_image' => UploadedFile::fake()->create('myproof.png', 50)->store('proofs', 'public'),
    ]);

    // 1. Unauthenticated guest should be redirected to login
    $this->get("/transactions/{$tx->id}/proof")
        ->assertRedirect('/login');

    // 2. Authenticated user from another shop should get 403 Forbidden
    $user2 = User::factory()->create();
    $shop2 = Shop::factory()->create(['user_id' => $user2->id]);

    $this->actingAs($user2)
        ->get("/transactions/{$tx->id}/proof")
        ->assertStatus(403);

    // 3. Authenticated owner of the shop should get 200 OK
    $this->actingAs($user1)
        ->get("/transactions/{$tx->id}/proof")
        ->assertOk();
});
