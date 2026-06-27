<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::controller(ShopController::class)->group(function () {

        Route::get('/shop/create', 'create')
            ->name('shop.create');

        Route::post('/shop', 'store')
            ->name('shop.store');

    });

    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');

    Route::post('/transactions', [TransactionController::class, 'store'])
        ->name('transactions.store');

    Route::get('/transactions/{transaction}/proof', [TransactionController::class, 'showProof'])
        ->name('transactions.proof');

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])
        ->name('reports.export.pdf');

    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])
        ->name('reports.export.excel');

    Route::get('/withdrawals', [WithdrawalController::class, 'index'])
        ->name('withdrawals.index');

    Route::post('/withdrawals', [WithdrawalController::class, 'store'])
        ->name('withdrawals.store');

    Route::get('/settings', SettingController::class)
        ->name('settings.index');

    Route::resource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
    Route::resource('payment-methods', PaymentMethodController::class)->only(['store', 'update', 'destroy']);
    Route::patch('payment-methods/{payment_method}/toggle', [PaymentMethodController::class, 'toggleStatus'])->name('payment-methods.toggle');
});

require __DIR__.'/auth.php';
