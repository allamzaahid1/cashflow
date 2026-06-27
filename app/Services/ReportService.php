<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReportService
{
    public function __construct(
        protected BalanceService $balanceService,
    ) {}

    /**
     * Get the transaction query builder (kept for backward compatibility and testing).
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
     * Get all report items (transactions & withdrawals) with pre-calculated running balance.
     */
    public function getReportItems(Shop $shop, array $filters): Collection
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();

        // 1. Fetch ALL transactions up to end date to build running balance correctly
        $transactions = Transaction::with(['category', 'paymentMethod'])
            ->where('shop_id', $shop->id)
            ->whereDate('transaction_date', '<=', $endDate)
            ->get();

        // 2. Fetch ALL withdrawals up to end date
        $withdrawals = Withdrawal::with('paymentMethod')
            ->where('shop_id', $shop->id)
            ->whereDate('withdrawal_date', '<=', $endDate)
            ->get();

        // 3. Map to common structures
        $mappedTransactions = $transactions->map(function ($tx) {
            return (object) [
                'id' => $tx->id,
                'date' => $tx->transaction_date,
                'created_at' => $tx->created_at,
                'transaction_code' => $tx->transaction_code,
                'type' => $tx->category->type, // 'income' or 'expense'
                'category_name' => $tx->category->name,
                'category_id' => $tx->category_id,
                'payment_method_name' => $tx->paymentMethod->name,
                'payment_method_id' => $tx->payment_method_id,
                'description' => $tx->description,
                'proof_image' => $tx->proof_image,
                'amount' => (float) $tx->amount,
                'admin_fee' => 0.0,
                'is_withdrawal' => false,
                'status' => null,
            ];
        });

        $mappedWithdrawals = $withdrawals->map(function ($w) {
            return (object) [
                'id' => $w->id,
                'date' => $w->withdrawal_date,
                'created_at' => $w->created_at,
                'transaction_code' => '',
                'type' => 'withdrawal',
                'category_name' => 'Penarikan Dana',
                'category_id' => null,
                'payment_method_name' => $w->paymentMethod->name,
                'payment_method_id' => $w->payment_method_id,
                'description' => $w->notes,
                'proof_image' => null,
                'amount' => (float) $w->amount,
                'admin_fee' => (float) $w->admin_fee,
                'is_withdrawal' => true,
                'status' => $w->status,
            ];
        });

        $merged = $mappedTransactions->concat($mappedWithdrawals);

        // 4. Sort chronologically (oldest first) to calculate running balance
        $sorted = $merged->sort(function ($a, $b) {
            $timeA = $a->created_at;
            $timeB = $b->created_at;
            if ($timeA == $timeB) {
                return $a->id <=> $b->id;
            }

            return $timeA <=> $timeB;
        })->values();

        // 5. Compute running balance
        $balance = 0.0;
        foreach ($sorted as $item) {
            if ($item->is_withdrawal) {
                if ($item->status !== 'rejected') {
                    $balance -= ($item->amount + $item->admin_fee);
                }
            } else {
                if ($item->type === 'income') {
                    $balance += $item->amount;
                } else {
                    $balance -= $item->amount;
                }
            }
            $item->running_balance = $balance;
        }

        // 6. Filter final collection based on dates and parameters
        $filtered = $sorted->filter(function ($item) use ($filters, $startDate) {
            // Date filter
            if ($item->date < $startDate) {
                return false;
            }

            // Type filter
            if (isset($filters['type']) && $filters['type'] !== 'all') {
                if ($filters['type'] === 'income') {
                    if ($item->is_withdrawal || $item->type !== 'income') {
                        return false;
                    }
                } elseif ($filters['type'] === 'expense') {
                    if (! $item->is_withdrawal && $item->type !== 'expense') {
                        return false;
                    }
                }
            }

            // Category filter
            if (isset($filters['category_id']) && $filters['category_id'] !== '') {
                if ($item->is_withdrawal || $item->category_id != $filters['category_id']) {
                    return false;
                }
            }

            // Payment method filter
            if (isset($filters['payment_method_id']) && $filters['payment_method_id'] !== '') {
                if ($item->payment_method_id != $filters['payment_method_id']) {
                    return false;
                }
            }

            return true;
        });

        // 7. Sort newest first (Date & Time desc, then ID desc)
        return $filtered->sort(function ($a, $b) {
            $timeA = $a->created_at;
            $timeB = $b->created_at;
            if ($timeA == $timeB) {
                return $b->id <=> $a->id;
            }

            return $timeB <=> $timeA;
        })->values();
    }

    /**
     * Get paginated report items.
     */
    public function getPaginatedReport(Shop $shop, array $filters, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        $items = $this->getReportItems($shop, $filters);
        $offset = ($page - 1) * $perPage;
        $sliced = $items->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $sliced,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    /**
     * Get the summary aggregates for the filtered query.
     */
    public function getSummary(Shop $shop, array $filters): array
    {
        $items = $this->getReportItems($shop, $filters);

        $totalIncome = 0.0;
        $totalExpense = 0.0;

        foreach ($items as $item) {
            if ($item->is_withdrawal) {
                if ($item->status !== 'rejected') {
                    $totalExpense += ($item->amount + $item->admin_fee);
                }
            } else {
                if ($item->type === 'income') {
                    $totalIncome += $item->amount;
                } else {
                    $totalExpense += $item->amount;
                }
            }
        }

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_cash_flow' => $totalIncome - $totalExpense,
            'available_balance' => (float) $this->balanceService->getAvailableBalance($shop),
            'transaction_count' => $items->count(),
        ];
    }
}
