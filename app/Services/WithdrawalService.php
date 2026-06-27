<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalService
{
    public function __construct(protected BalanceService $balanceService) {}

    /**
     * Compute current available balance of the shop.
     */
    public function getAvailableBalance(Shop $shop): string
    {
        return $this->balanceService->getAvailableBalance($shop);
    }

    /**
     * Count the number of active/pending withdrawals in the current calendar month.
     */
    public function getMonthlyWithdrawalCount(Shop $shop): int
    {
        return $shop->withdrawals()
            ->whereYear('withdrawal_date', now()->year)
            ->whereMonth('withdrawal_date', now()->month)
            ->where('status', '!=', 'rejected')
            ->count();
    }

    /**
     * Request a new withdrawal.
     */
    public function create(Shop $shop, array $data): Withdrawal
    {
        return DB::transaction(function () use ($shop, $data) {
            $amount = (float) $data['amount'];
            $adminFee = 2500.00;
            $totalDeduction = $amount + $adminFee;

            // 1. Minimum check
            if ($amount < 10000) {
                throw ValidationException::withMessages([
                    'amount' => 'Nominal penarikan minimal Rp 10.000.',
                ]);
            }

            // 2. Quota check (max 5 per month)
            $count = $this->getMonthlyWithdrawalCount($shop);
            if ($count >= 5) {
                throw ValidationException::withMessages([
                    'amount' => 'Batas maksimal penarikan dana adalah 5 kali per bulan.',
                ]);
            }

            // 3. Balance check
            $available = (float) $this->getAvailableBalance($shop);
            if ($totalDeduction > $available) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo tidak mencukupi untuk melakukan penarikan.',
                ]);
            }

            // 4. Verify payment method belongs to shop and is active non-cash
            $paymentMethod = $shop->paymentMethods()
                ->where('id', $data['payment_method_id'])
                ->where('type', '!=', 'cash')
                ->where('is_active', true)
                ->first();

            if (! $paymentMethod) {
                throw ValidationException::withMessages([
                    'payment_method_id' => 'Metode pembayaran tujuan tidak valid atau tidak aktif.',
                ]);
            }

            return Withdrawal::create([
                'shop_id' => $shop->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => number_format($amount, 2, '.', ''),
                'admin_fee' => number_format($adminFee, 2, '.', ''),
                'status' => 'pending',
                'withdrawal_date' => now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}
