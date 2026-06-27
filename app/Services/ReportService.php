<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    /**
     * Get the transaction query builder with applied filters.
     */
    public function getTransactionQuery(Shop $shop, array $filters): Builder
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();

        $query = Transaction::with(['category', 'paymentMethod'])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('transactions.*')
            ->where('transactions.shop_id', $shop->id)
            ->whereDate('transactions.transaction_date', '>=', $startDate)
            ->whereDate('transactions.transaction_date', '<=', $endDate);

        if (isset($filters['type']) && $filters['type'] !== 'all') {
            $query->where('categories.type', $filters['type']);
        }

        if (isset($filters['category_id']) && $filters['category_id'] !== '') {
            $query->where('transactions.category_id', $filters['category_id']);
        }

        if (isset($filters['payment_method_id']) && $filters['payment_method_id'] !== '') {
            $query->where('transactions.payment_method_id', $filters['payment_method_id']);
        }

        return $query->orderBy('transactions.transaction_date', 'desc')
            ->orderBy('transactions.id', 'desc');
    }

    /**
     * Get the summary aggregates for the filtered query.
     */
    public function getSummary(Shop $shop, array $filters): array
    {
        $baseQuery = $this->getTransactionQuery($shop, $filters);

        // We run clean cloned aggregates over the base query builder
        $totalIncome = (float) (clone $baseQuery)
            ->where('categories.type', 'income')
            ->sum('transactions.amount');

        $totalExpense = (float) (clone $baseQuery)
            ->where('categories.type', 'expense')
            ->sum('transactions.amount');

        $netCashFlow = $totalIncome - $totalExpense;
        $transactionCount = (clone $baseQuery)->count();

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_cash_flow' => $netCashFlow,
            'transaction_count' => $transactionCount,
        ];
    }
}
