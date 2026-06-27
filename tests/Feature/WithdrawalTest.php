<?php

use App\Models\User;
use App\Models\Shop;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;

test('authenticated user without shop is redirected to shop setup wizard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/withdrawals');

    $response->assertRedirect('/shop/create');
});

test('authenticated user with shop can view withdrawals page', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->get('/withdrawals');

    $response->assertOk();
    $response->assertViewHasAll(['availableBalance', 'monthlyQuotaUsed', 'paymentMethods', 'withdrawals', 'shop']);
});

test('available balance calculates dynamically and subtracts pending/approved withdrawals', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $incomeCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $expenseCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'expense']);

    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);
    $qrisMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'qris']);

    // Total income = 150000
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 150000,
        'transaction_date' => now()->toDateString(),
    ]);

    // Total expense = 30000
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $expenseCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 30000,
        'transaction_date' => now()->toDateString(),
    ]);

    // Pending withdrawal deduction = 50000 + 2500 admin fee = 52500
    Withdrawal::factory()->create([
        'shop_id' => $shop->id,
        'payment_method_id' => $qrisMethod->id,
        'amount' => 50000,
        'admin_fee' => 2500,
        'status' => 'pending',
        'withdrawal_date' => now()->toDateString(),
    ]);

    // Rejected withdrawal (should NOT deduct from balance)
    Withdrawal::factory()->create([
        'shop_id' => $shop->id,
        'payment_method_id' => $qrisMethod->id,
        'amount' => 100000,
        'admin_fee' => 2500,
        'status' => 'rejected',
        'withdrawal_date' => now()->toDateString(),
    ]);

    $service = app(WithdrawalService::class);
    $balance = $service->getAvailableBalance($shop);

    // Available: 150000 (income) - 30000 (expense) - 52500 (pending withdrawal) = 67500.00
    expect((float) $balance)->toEqual(67500.00);
});

test('user can submit a valid withdrawal request', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $incomeCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);
    $qrisMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'qris', 'is_active' => true]);

    // Seed sufficient balance (Rp 200.000)
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 200000,
        'transaction_date' => now()->toDateString(),
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/withdrawals', [
            'amount' => 100000,
            'payment_method_id' => $qrisMethod->id,
            'notes' => 'Tarik dana cab.',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/withdrawals');

    $withdrawal = Withdrawal::first();
    expect($withdrawal)->not->toBeNull();
    expect($withdrawal->amount)->toEqual('100000.00');
    expect($withdrawal->admin_fee)->toEqual('2500.00');
    expect($withdrawal->status)->toBe('pending');
});

test('withdrawal limits prevent invalid requests', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $incomeCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);
    $qrisMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'qris', 'is_active' => true]);

    // Seed Rp 50.000 balance
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 50000,
        'transaction_date' => now()->toDateString(),
    ]);

    // 1. Minimum limit check (Rp 5.000 fails minimum Rp 10.000)
    $responseMin = $this
        ->actingAs($user)
        ->from('/withdrawals')
        ->post('/withdrawals', [
            'amount' => 5000,
            'payment_method_id' => $qrisMethod->id,
        ]);
    $responseMin->assertSessionHasErrors('amount');

    // 2. Insufficient balance check (Rp 50.000 request + Rp 2.500 admin fee > Rp 50.000 available balance)
    $responseSuff = $this
        ->actingAs($user)
        ->from('/withdrawals')
        ->post('/withdrawals', [
            'amount' => 50000,
            'payment_method_id' => $qrisMethod->id,
        ]);
    $responseSuff->assertSessionHasErrors('amount');

    // 3. Quota limit check (exceeding 5 requests in a month)
    // Create 5 mock pending withdrawals
    for ($i = 0; $i < 5; $i++) {
        Withdrawal::factory()->create([
            'shop_id' => $shop->id,
            'payment_method_id' => $qrisMethod->id,
            'amount' => 1000,
            'admin_fee' => 100,
            'status' => 'pending',
            'withdrawal_date' => now()->toDateString(),
        ]);
    }

    $responseQuota = $this
        ->actingAs($user)
        ->from('/withdrawals')
        ->post('/withdrawals', [
            'amount' => 10000,
            'payment_method_id' => $qrisMethod->id,
        ]);
    $responseQuota->assertSessionHasErrors('amount');
});

test('user cannot withdraw to cash or inactive payment method', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);
    $inactiveMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'qris', 'is_active' => false]);

    $responseCash = $this
        ->actingAs($user)
        ->from('/withdrawals')
        ->post('/withdrawals', [
            'amount' => 15000,
            'payment_method_id' => $cashMethod->id,
        ]);
    $responseCash->assertSessionHasErrors('payment_method_id');

    $responseInactive = $this
        ->actingAs($user)
        ->from('/withdrawals')
        ->post('/withdrawals', [
            'amount' => 15000,
            'payment_method_id' => $inactiveMethod->id,
        ]);
    $responseInactive->assertSessionHasErrors('payment_method_id');
});

test('user cannot withdraw using another user shop payment method', function () {
    $user1 = User::factory()->create();
    $shop1 = Shop::factory()->create(['user_id' => $user1->id]);
    $qris1 = PaymentMethod::factory()->create(['shop_id' => $shop1->id, 'type' => 'qris', 'is_active' => true]);

    $user2 = User::factory()->create();
    $shop2 = Shop::factory()->create(['user_id' => $user2->id]);

    $response = $this
        ->actingAs($user2)
        ->from('/withdrawals')
        ->post('/withdrawals', [
            'amount' => 15000,
            'payment_method_id' => $qris1->id, // belongs to shop1
        ]);

    $response->assertSessionHasErrors('payment_method_id');
});
