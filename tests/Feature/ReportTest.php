<?php

use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\ReportService;

test('authenticated user without shop is redirected to shop setup wizard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/reports');

    $response->assertRedirect('/shop/create');
});

test('authenticated user with shop can view reports index page', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->get('/reports');

    $response->assertOk();
    $response->assertViewHasAll(['transactions', 'summary', 'categories', 'paymentMethods', 'filters', 'shop']);
});

test('reports filter correctly narrows transaction queries and aggregates', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $incomeCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $expenseCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'expense']);

    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);
    $qrisMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'qris']);

    // Create a set of test transactions over multiple dates
    // 1. Today's Cash Income
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 50000,
        'transaction_date' => now()->toDateString(),
    ]);

    // 2. Today's QRIS Income
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $qrisMethod->id,
        'amount' => 30000,
        'transaction_date' => now()->toDateString(),
    ]);

    // 3. Today's Expense
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $expenseCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 20000,
        'transaction_date' => now()->toDateString(),
    ]);

    // 4. Yesterday's Cash Income (outside date range if we narrow)
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 90000,
        'transaction_date' => now()->subDay()->toDateString(),
    ]);

    $service = app(ReportService::class);

    // Test Case A: Get all today's transactions
    $filtersA = [
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'type' => 'all',
    ];
    $summaryA = $service->getSummary($shop, $filtersA);
    expect($summaryA['total_income'])->toEqual(80000); // 50000 + 30000
    expect($summaryA['total_expense'])->toEqual(20000);
    expect($summaryA['net_cash_flow'])->toEqual(60000);
    expect($summaryA['transaction_count'])->toEqual(3);

    // Test Case B: Filter by type = income
    $filtersB = [
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'type' => 'income',
    ];
    $summaryB = $service->getSummary($shop, $filtersB);
    expect($summaryB['transaction_count'])->toEqual(2);

    // Test Case C: Filter by payment method = QRIS
    $filtersC = [
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'type' => 'all',
        'payment_method_id' => $qrisMethod->id,
    ];
    $summaryC = $service->getSummary($shop, $filtersC);
    expect($summaryC['transaction_count'])->toEqual(1);
    expect($summaryC['total_income'])->toEqual(30000);
});

test('pagination returns exact number of entries and maintains query arguments', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $incomeCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);

    // Create 15 transactions
    for ($i = 0; $i < 15; $i++) {
        Transaction::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $incomeCat->id,
            'payment_method_id' => $cashMethod->id,
            'amount' => 1000,
            'transaction_date' => now()->toDateString(),
        ]);
    }

    $response = $this
        ->actingAs($user)
        ->get('/reports?type=income&page=2');

    $response->assertOk();
    $transactions = $response->viewData('transactions');
    expect($transactions->count())->toEqual(5); // Page 2 contains 5 entries
    expect($transactions->total())->toEqual(15);
});

test('withdrawals appear in reports, reduce running balance, and affect summary', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $incomeCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);
    $qrisMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'qris']);

    // 1. Record income transaction of 100,000
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 100000,
        'transaction_date' => now()->toDateString(),
    ]);

    // 2. Record approved/pending withdrawal of 40,000 with 2,500 admin fee
    Withdrawal::factory()->create([
        'shop_id' => $shop->id,
        'payment_method_id' => $qrisMethod->id,
        'amount' => 40000,
        'admin_fee' => 2500,
        'status' => 'pending',
        'withdrawal_date' => now()->toDateString(),
    ]);

    $service = app(ReportService::class);
    $reportItems = $service->getReportItems($shop, [
        'start_date' => now()->startOfMonth()->toDateString(),
        'end_date' => now()->toDateString(),
    ]);

    // Merged items should contain 2 elements
    expect($reportItems->count())->toEqual(2);

    // Sorted newest first, so the withdrawal (created second) comes first
    $withdrawalItem = $reportItems->firstWhere('is_withdrawal', true);
    $transactionItem = $reportItems->firstWhere('is_withdrawal', false);

    expect($withdrawalItem)->not->toBeNull();
    expect($transactionItem)->not->toBeNull();

    // Check chronological running balance
    // 1. Transaction (income): 100,000. Balance = 100,000.
    // 2. Withdrawal: reduces balance by 42,500. Balance = 57,500.
    expect($transactionItem->running_balance)->toEqual(100000);
    expect($withdrawalItem->running_balance)->toEqual(57500);

    // Check summary aggregates
    $summary = $service->getSummary($shop, [
        'start_date' => now()->startOfMonth()->toDateString(),
        'end_date' => now()->toDateString(),
        'type' => 'all',
    ]);

    expect($summary['total_income'])->toEqual(100000);
    expect($summary['total_expense'])->toEqual(42500); // 40000 + 2500 admin fee
    expect($summary['net_cash_flow'])->toEqual(57500);
});
