<?php

use App\Models\User;
use App\Models\Shop;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Services\ShopService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('default Cash payment method is automatically generated when a shop is created', function () {
    $user = User::factory()->create();
    $shopService = app(ShopService::class);

    $shop = $shopService->create($user, [
        'name' => 'Warung Test',
        'phone' => '081234567890',
        'address' => 'Jl. Test No. 1',
    ]);

    $this->assertDatabaseHas('payment_methods', [
        'shop_id' => $shop->id,
        'name' => 'Tunai',
        'type' => 'cash',
        'is_active' => true,
    ]);
});

test('user can register a custom payment method', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->post('/payment-methods', [
            'name' => 'Gopay Warung',
            'type' => 'qris',
            'account_name' => 'Warung Barokah',
            'account_number' => '081234567890',
            'qr_image' => UploadedFile::fake()->create('qris.png', 100),
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/settings');

    $paymentMethod = PaymentMethod::where('name', 'Gopay Warung')->first();
    expect($paymentMethod)->not->toBeNull();
    expect($paymentMethod->qr_image)->not->toBeNull();

    Storage::disk('public')->assertExists($paymentMethod->qr_image);
});

test('duplicate payment method name is rejected for the same type and shop', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    PaymentMethod::factory()->create([
        'shop_id' => $shop->id,
        'name' => 'BCA Kasir',
        'type' => 'transfer',
        'account_name' => 'Budi',
        'account_number' => '12345678',
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/settings')
        ->post('/payment-methods', [
            'name' => 'BCA Kasir',
            'type' => 'transfer',
            'account_name' => 'Budi Baru',
            'account_number' => '87654321',
        ]);

    $response->assertSessionHasErrors('name');
});

test('default Cash payment method cannot be edited', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $cashMethod = PaymentMethod::factory()->create([
        'shop_id' => $shop->id,
        'name' => 'Tunai',
        'type' => 'cash',
    ]);

    $response = $this
        ->actingAs($user)
        ->put("/payment-methods/{$cashMethod->id}", [
            'name' => 'Uang Fisik',
            'type' => 'transfer',
            'account_name' => 'Test',
            'account_number' => '1234',
        ]);

    $response->assertSessionHasErrors('payment_method');
    expect($cashMethod->fresh()->name)->toBe('Tunai');
});

test('default Cash payment method cannot be deleted', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $cashMethod = PaymentMethod::factory()->create([
        'shop_id' => $shop->id,
        'name' => 'Tunai',
        'type' => 'cash',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete("/payment-methods/{$cashMethod->id}");

    $response->assertSessionHasErrors('payment_method');
    $this->assertDatabaseHas('payment_methods', ['id' => $cashMethod->id]);
});

test('default Cash payment method cannot be deactivated', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $cashMethod = PaymentMethod::factory()->create([
        'shop_id' => $shop->id,
        'name' => 'Tunai',
        'type' => 'cash',
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/payment-methods/{$cashMethod->id}/toggle");

    $response->assertSessionHasErrors('payment_method');
    expect((bool) $cashMethod->fresh()->is_active)->toBeTrue();
});

test('user can toggle active status of non-cash payment methods', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $paymentMethod = PaymentMethod::factory()->create([
        'shop_id' => $shop->id,
        'type' => 'transfer',
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch("/payment-methods/{$paymentMethod->id}/toggle");

    $response->assertSessionHasNoErrors();
    expect((bool) $paymentMethod->fresh()->is_active)->toBeFalse();
});

test('payment method cannot be deleted if linked to transactions', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $paymentMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'transfer']);

    Transaction::factory()->create([
        'shop_id' => $shop->id,
        'payment_method_id' => $paymentMethod->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete("/payment-methods/{$paymentMethod->id}");

    $response->assertSessionHasErrors('payment_method');
    $this->assertDatabaseHas('payment_methods', ['id' => $paymentMethod->id]);
});

test('payment method cannot be deleted if linked to withdrawals', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);
    $paymentMethod = PaymentMethod::factory()->create(['shop_id' => $shop->id, 'type' => 'transfer']);

    Withdrawal::factory()->create([
        'shop_id' => $shop->id,
        'payment_method_id' => $paymentMethod->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete("/payment-methods/{$paymentMethod->id}");

    $response->assertSessionHasErrors('payment_method');
    $this->assertDatabaseHas('payment_methods', ['id' => $paymentMethod->id]);
});

test('payment method deletion removes qr_image file from disk', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $shop = Shop::factory()->create(['user_id' => $user->id]);

    $file = UploadedFile::fake()->create('qris.png', 100);
    $path = $file->store('qris', 'public');

    $paymentMethod = PaymentMethod::factory()->create([
        'shop_id' => $shop->id,
        'type' => 'qris',
        'qr_image' => $path,
    ]);

    Storage::disk('public')->assertExists($path);

    $response = $this
        ->actingAs($user)
        ->delete("/payment-methods/{$paymentMethod->id}");

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('payment_methods', ['id' => $paymentMethod->id]);
    Storage::disk('public')->assertMissing($path);
});

test('user cannot access or modify payment methods belonging to another shop', function () {
    $user1 = User::factory()->create();
    $shop1 = Shop::factory()->create(['user_id' => $user1->id]);
    $paymentMethod = PaymentMethod::factory()->create(['shop_id' => $shop1->id, 'type' => 'transfer']);

    $user2 = User::factory()->create();
    $shop2 = Shop::factory()->create(['user_id' => $user2->id]);

    // Try update
    $responseUpdate = $this
        ->actingAs($user2)
        ->put("/payment-methods/{$paymentMethod->id}", [
            'name' => 'Hacked BCA',
            'type' => 'transfer',
            'account_name' => 'Hack',
            'account_number' => '1234',
        ]);
    $responseUpdate->assertForbidden();

    // Try toggle
    $responseToggle = $this
        ->actingAs($user2)
        ->patch("/payment-methods/{$paymentMethod->id}/toggle");
    $responseToggle->assertForbidden();

    // Try delete
    $responseDelete = $this
        ->actingAs($user2)
        ->delete("/payment-methods/{$paymentMethod->id}");
    $responseDelete->assertForbidden();
});
