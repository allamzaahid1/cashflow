<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Shop;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get aggregated metrics for today.
     */
    public function getMetrics(Shop $shop): array
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();        // 1. Today's Income
        $todayIncome = (float) Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.shop_id', $shop->id)
            ->whereDate('transactions.transaction_date', $today)
            ->where('categories.type', 'income')
            ->sum('transactions.amount');

        // 2. Today's Transaction Count
        $todayTransactionCount = Transaction::where('shop_id', $shop->id)
            ->whereDate('transaction_date', $today)
            ->count();

        // 3. Today's Cash Income
        $cashIncome = (float) Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
            ->where('transactions.shop_id', $shop->id)
            ->whereDate('transactions.transaction_date', $today)
            ->where('categories.type', 'income')
            ->where('payment_methods.type', 'cash')
            ->sum('transactions.amount');

        // 4. Today's QRIS (Non-Cash) Income
        $qrisIncome = (float) Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->join('payment_methods', 'transactions.payment_method_id', '=', 'payment_methods.id')
            ->where('transactions.shop_id', $shop->id)
            ->whereDate('transactions.transaction_date', $today)
            ->where('categories.type', 'income')
            ->where('payment_methods.type', '!=', 'cash')
            ->sum('transactions.amount');

        // 5. Yesterday's Income (for percentage calculation)
        $yesterdayIncome = (float) Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.shop_id', $shop->id)
            ->whereDate('transactions.transaction_date', $yesterday)
            ->where('categories.type', 'income')
            ->sum('transactions.amount');
        // Calculations
        $percentageDiff = 0.0;
        if ($yesterdayIncome > 0) {
            $percentageDiff = (($todayIncome - $yesterdayIncome) / $yesterdayIncome) * 100;
        } elseif ($todayIncome > 0) {
            $percentageDiff = 100.0;
        }

        $cashPercentage = 0;
        $qrisPercentage = 0;
        if ($todayIncome > 0) {
            $cashPercentage = (int) round(($cashIncome / $todayIncome) * 100);
            $qrisPercentage = (int) round(($qrisIncome / $todayIncome) * 100);
        }

        return [
            'today_income' => $todayIncome,
            'today_transaction_count' => $todayTransactionCount,
            'cash_income' => $cashIncome,
            'qris_income' => $qrisIncome,
            'percentage_diff' => (float) round($percentageDiff, 1),
            'cash_percentage' => $cashPercentage,
            'qris_percentage' => $qrisPercentage,
        ];
    }

    /**
     * Get the 5 most recent transactions.
     */
    public function getRecentTransactions(Shop $shop): Collection
    {
        return Transaction::with(['category', 'paymentMethod'])
            ->where('shop_id', $shop->id)
            ->latest('transaction_date')
            ->latest('id')
            ->take(5)
            ->get();
    }

    /**
     * Get weekly chart data (Monday - Sunday).
     */
    public function getWeeklyChartData(Shop $shop): array
    {
        $startOfWeek = now()->startOfWeek(); // Carbon Monday
        $endOfWeek = now()->endOfWeek();     // Carbon Sunday

        $pluckedIncomes = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.shop_id', $shop->id)
            ->whereDate('transactions.transaction_date', '>=', $startOfWeek->toDateString())
            ->whereDate('transactions.transaction_date', '<=', $endOfWeek->toDateString())
            ->where('categories.type', 'income')
            ->groupBy('transactions.transaction_date')
            ->selectRaw('transactions.transaction_date, sum(transactions.amount) as total')
            ->get();

        $dailyIncomes = [];
        foreach ($pluckedIncomes as $row) {
            $formattedDate = Carbon::parse($row->transaction_date)->toDateString();
            $dailyIncomes[$formattedDate] = (float) $row->total;
        }

        $days = [
            'Monday'    => 'Sen',
            'Tuesday'   => 'Sel',
            'Wednesday' => 'Rab',
            'Thursday'  => 'Kam',
            'Friday'    => 'Jum',
            'Saturday'  => 'Sab',
            'Sunday'    => 'Min',
        ];

        $chartData = [];
        $maxIncome = 0.0;

        foreach ($days as $englishName => $indonesianName) {
            $date = Carbon::parse($startOfWeek)->modify($englishName)->toDateString();
            $amount = (float) ($dailyIncomes[$date] ?? 0.0);
            $chartData[] = [
                'day' => $indonesianName,
                'amount' => $amount,
                'date' => $date,
            ];

            if ($amount > $maxIncome) {
                $maxIncome = $amount;
            }
        }

        // Add visual percentage representation for UI layout bar scaling
        foreach ($chartData as &$dayData) {
            $dayData['percentage'] = $maxIncome > 0 ? (int) round(($dayData['amount'] / $maxIncome) * 100) : 0;
        }

        return [
            'chart_data' => $chartData,
            'start_date' => Carbon::parse($startOfWeek)->format('d M'),
            'end_date' => Carbon::parse($endOfWeek)->format('d M Y'),
        ];
    }
}
