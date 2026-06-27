<?php

use App\Models\User;
use App\Models\Shop;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Services\DashboardService;
use Carbon\Carbon;

test('authenticated user without shop is redirected to shop setup wizard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/dashboard');

    $response->assertRedirect('/shop/create');
});

test('authenticated user with shop can view the dashboard view', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->get('/dashboard');

    $response->assertOk();
    $response->assertViewHasAll(['metrics', 'recentTransactions', 'weeklyData', 'shop']);
});

test('dashboard service calculates correct metrics aggregates for today', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    // Create categories
    $incomeCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $expenseCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'expense']);

    // Create payment methods
    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);
    $qrisMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'qris']);

    // Yesterday's income
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 50000,
        'transaction_date' => now()->subDay()->toDateString(),
    ]);

    // Today's cash income
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 60000,
        'transaction_date' => now()->toDateString(),
    ]);

    // Today's QRIS income
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $qrisMethod->id,
        'amount' => 40000,
        'transaction_date' => now()->toDateString(),
    ]);

    // Today's expense (should not count towards income metrics)
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $expenseCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 20000,
        'transaction_date' => now()->toDateString(),
    ]);

    $service = app(DashboardService::class);
    $metrics = $service->getMetrics($shop);

    expect($metrics['today_income'])->toEqual(100000); // 60000 + 40000
    expect($metrics['today_transaction_count'])->toEqual(3); // 2 income + 1 expense today
    expect($metrics['cash_income'])->toEqual(60000);
    expect($metrics['qris_income'])->toEqual(40000);
    expect($metrics['cash_percentage'])->toEqual(60); // 60000 / 100000
    expect($metrics['qris_percentage'])->toEqual(40); // 40000 / 100000
    expect($metrics['percentage_diff'])->toEqual(100); // (100000 - 50000) / 50000 = 100%
});

test('recent transactions displays only latest 5 records', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $incomeCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);

    // Create 7 transactions
    for ($i = 1; $i <= 7; $i++) {
        Transaction::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $incomeCat->id,
            'payment_method_id' => $cashMethod->id,
            'amount' => $i * 1000,
            'transaction_date' => now()->toDateString(),
        ]);
    }

    $service = app(DashboardService::class);
    $recent = $service->getRecentTransactions($shop);

    expect($recent->count())->toEqual(5);
    // Assert ordering (latest created first)
    expect((float) $recent[0]->amount)->toEqual(7000.0);
    expect((float) $recent[4]->amount)->toEqual(3000.0);
});

test('weekly chart data queries calendar week and calculates bar percentages', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $incomeCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);

    $startOfWeek = now()->startOfWeek(); // Monday

    // Store income on Monday (Day 1)
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 10000,
        'transaction_date' => Carbon::parse($startOfWeek)->toDateString(),
    ]);

    // Store income on Wednesday (Day 3)
    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 20000,
        'transaction_date' => Carbon::parse($startOfWeek)->addDays(2)->toDateString(),
    ]);

    $service = app(DashboardService::class);
    $weeklyData = $service->getWeeklyChartData($shop);

    expect(count($weeklyData['chart_data']))->toEqual(7); // 7 days in a calendar week

    // Monday check
    expect($weeklyData['chart_data'][0]['day'])->toEqual('Sen');
    expect($weeklyData['chart_data'][0]['amount'])->toEqual(10000.0);
    expect($weeklyData['chart_data'][0]['percentage'])->toEqual(50); // 10000 / 20000 max

    // Wednesday check
    expect($weeklyData['chart_data'][2]['day'])->toEqual('Rab');
    expect($weeklyData['chart_data'][2]['amount'])->toEqual(20000.0);
    expect($weeklyData['chart_data'][2]['percentage'])->toEqual(100); // Max daily amount
});
