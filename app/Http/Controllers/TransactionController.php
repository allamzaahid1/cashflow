<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService,
    ) {}

    /**
     * Display the catat transaksi view.
     */
    public function index(Request $request): View
    {
        $shop = auth()->user()->shop;

        $incomeCategories = $shop ? $shop->categories()->where('type', 'income')->orderBy('name')->get() : collect();
        $expenseCategories = $shop ? $shop->categories()->where('type', 'expense')->orderBy('name')->get() : collect();

        $nonCashPaymentMethods = $shop ? $shop->paymentMethods()
            ->where('type', '!=', 'cash')
            ->where('is_active', true)
            ->orderBy('name')
            ->get() : collect();

        return view('transactions.index', compact(
            'incomeCategories',
            'expenseCategories',
            'nonCashPaymentMethods',
            'shop'
        ));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $shop = auth()->user()->shop;

        if (! $shop) {
            return redirect()
                ->route('shop.create')
                ->with('error', 'Anda harus membuat toko terlebih dahulu.');
        }

        $this->transactionService->create($shop, $request->validated());

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil dicatat.');
    }

    /**
     * Serve transaction proof securely.
     */
    public function showProof(Transaction $transaction)
    {
        $shop = auth()->user()->shop;

        if (! $shop || $transaction->shop_id !== $shop->id) {
            abort(403, 'Unauthorized.');
        }

        if (! $transaction->proof_image || ! Storage::disk('public')->exists($transaction->proof_image)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->response($transaction->proof_image);
    }
}
