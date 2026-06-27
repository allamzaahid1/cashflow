<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Requests\UpdatePaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Services\PaymentMethodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentMethodController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected PaymentMethodService $paymentMethodService,
    ) {
    }

    /**
     * Store a newly created payment method.
     */
    public function store(StorePaymentMethodRequest $request): RedirectResponse
    {
        $shop = auth()->user()->shop;

        $this->paymentMethodService->create($shop, $request->validated());

        return redirect()
            ->route('settings.index')
            ->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    /**
     * Update the specified payment method.
     */
    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('update', $paymentMethod);

        $this->paymentMethodService->update($paymentMethod, $request->validated());

        return redirect()
            ->route('settings.index')
            ->with('success', 'Metode pembayaran berhasil diubah.');
    }

    /**
     * Remove the specified payment method.
     */
    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('delete', $paymentMethod);

        $this->paymentMethodService->delete($paymentMethod);

        return redirect()
            ->route('settings.index')
            ->with('success', 'Metode pembayaran berhasil dihapus.');
    }

    /**
     * Toggle status of the payment method.
     */
    public function toggleStatus(PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('update', $paymentMethod);

        $this->paymentMethodService->toggleStatus($paymentMethod);

        return redirect()
            ->route('settings.index')
            ->with('success', 'Status metode pembayaran berhasil diperbarui.');
    }
}
