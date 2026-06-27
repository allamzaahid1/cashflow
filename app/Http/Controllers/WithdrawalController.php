<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function __construct(
        protected WithdrawalService $withdrawalService,
    ) {
    }

    /**
     * Display a listing of withdrawals and request form.
     */
    public function index(): View|RedirectResponse
    {
        $shop = auth()->user()->shop;

        if (! $shop) {
            return redirect()
                ->route('shop.create')
                ->with('error', 'Anda harus membuat toko terlebih dahulu.');
        }

        $availableBalance = $this->withdrawalService->getAvailableBalance($shop);
        $monthlyQuotaUsed = $this->withdrawalService->getMonthlyWithdrawalCount($shop);
        $paymentMethods = $shop->paymentMethods()
            ->where('type', '!=', 'cash')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $withdrawals = $shop->withdrawals()
            ->with('paymentMethod')
            ->orderBy('withdrawal_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('withdrawals.index', compact(
            'availableBalance',
            'monthlyQuotaUsed',
            'paymentMethods',
            'withdrawals',
            'shop'
        ));
    }

    /**
     * Request a new withdrawal.
     */
    public function store(StoreWithdrawalRequest $request): RedirectResponse
    {
        $shop = auth()->user()->shop;

        if (! $shop) {
            return redirect()
                ->route('shop.create')
                ->with('error', 'Anda harus membuat toko terlebih dahulu.');
        }

        $this->withdrawalService->create($shop, $request->validated());

        return redirect()
            ->route('withdrawals.index')
            ->with('success', 'Permintaan penarikan dana berhasil dikirim.');
    }
}
