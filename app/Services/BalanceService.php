<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\Transaction;

class BalanceService
{
    /**
     * Compute current available balance of the shop.
     */
    public function getAvailableBalance(Shop $shop): string
    {
        $income = (float) Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.shop_id', $shop->id)
            ->where('categories.type', 'income')
            ->sum('transactions.amount');

        $expense = (float) Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.shop_id', $shop->id)
            ->where('categories.type', 'expense')
            ->sum('transactions.amount');

        $withdrawn = (float) $shop->withdrawals()
            ->whereIn('status', ['pending', 'approved'])
            ->selectRaw('SUM(amount + admin_fee) as total')
            ->value('total');

        return number_format($income - $expense - $withdrawn, 2, '.', '');
    }
}
