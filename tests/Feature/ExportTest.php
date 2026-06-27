<?php

use App\Models\User;
use App\Models\Shop;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Transaction;

test('authenticated user without shop is redirected to shop setup when exporting', function () {
    $user = User::factory()->create();

    $responsePdf = $this
        ->actingAs($user)
        ->get('/reports/export/pdf');
    $responsePdf->assertRedirect('/shop/create');

    $responseExcel = $this
        ->actingAs($user)
        ->get('/reports/export/excel');
    $responseExcel->assertRedirect('/shop/create');
});

test('user can download transaction report as PDF', function () {
    Carbon\Carbon::setTestNow('2026-06-27 12:00:00');

    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $incomeCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);

    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 50000,
        'transaction_date' => now()->toDateString(),
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/reports/export/pdf');

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertHeader('content-disposition', 'attachment; filename=laporan-arus-kas-20260627120000.pdf');

    Carbon\Carbon::setTestNow();
});

test('user can download transaction report as Excel sheet', function () {
    Carbon\Carbon::setTestNow('2026-06-27 12:00:00');

    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $incomeCat = Category::factory()->create(['shop_id' => $shop->id, 'type' => 'income']);
    $cashMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'cash']);

    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $incomeCat->id,
        'payment_method_id' => $cashMethod->id,
        'amount' => 50000,
        'transaction_date' => now()->toDateString(),
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/reports/export/excel');

    $response->assertOk();
    // Excel attachment checking
    $response->assertHeader('content-disposition', 'attachment; filename="laporan-arus-kas-20260627120000.xlsx"');

    Carbon\Carbon::setTestNow();
});

