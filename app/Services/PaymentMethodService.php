<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;

class PaymentMethodService
{
    /**
     * Create the default Cash payment method for a shop.
     */
    public function createDefaultCashMethod(Shop $shop): PaymentMethod
    {
        return DB::transaction(function () use ($shop) {
            return PaymentMethod::create([
                'shop_id' => $shop->id,
                'name' => 'Tunai',
                'type' => 'cash',
                'account_name' => null,
                'account_number' => null,
                'qr_image' => null,
                'is_active' => true,
            ]);
        });
    }

    /**
     * Create a new custom payment method.
     */
    public function create(Shop $shop, array $data): PaymentMethod
    {
        if (($data['type'] ?? '') === 'cash') {
            throw ValidationException::withMessages([
                'type' => 'Metode pembayaran Tunai tidak dapat ditambahkan secara manual.',
            ]);
        }

        return DB::transaction(function () use ($shop, $data) {
            $qrPath = null;
            if (isset($data['qr_image']) && $data['qr_image'] instanceof UploadedFile) {
                $qrPath = $data['qr_image']->store('qris', 'public');
            }

            return PaymentMethod::create([
                'shop_id' => $shop->id,
                'name' => $data['name'],
                'type' => $data['type'],
                'account_name' => $data['account_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'qr_image' => $qrPath,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /**
     * Update an existing payment method.
     */
    public function update(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        if ($paymentMethod->type === 'cash') {
            throw ValidationException::withMessages([
                'payment_method' => 'Metode pembayaran Tunai tidak dapat diubah.',
            ]);
        }

        return DB::transaction(function () use ($paymentMethod, $data) {
            $qrPath = $paymentMethod->qr_image;
            if (isset($data['qr_image']) && $data['qr_image'] instanceof UploadedFile) {
                if ($qrPath) {
                    Storage::disk('public')->delete($qrPath);
                }
                $qrPath = $data['qr_image']->store('qris', 'public');
            }

            $paymentMethod->update([
                'name' => $data['name'],
                'type' => $data['type'] ?? $paymentMethod->type,
                'account_name' => $data['account_name'] ?? $paymentMethod->account_name,
                'account_number' => $data['account_number'] ?? $paymentMethod->account_number,
                'qr_image' => $qrPath,
                'is_active' => $data['is_active'] ?? $paymentMethod->is_active,
            ]);

            return $paymentMethod;
        });
    }

    /**
     * Delete a payment method.
     */
    public function delete(PaymentMethod $paymentMethod): void
    {
        if ($paymentMethod->type === 'cash') {
            throw ValidationException::withMessages([
                'payment_method' => 'Metode pembayaran Tunai tidak dapat dihapus.',
            ]);
        }

        DB::transaction(function () use ($paymentMethod) {
            if ($paymentMethod->transactions()->exists() || $paymentMethod->withdrawals()->exists()) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Metode pembayaran ini tidak dapat dihapus karena memiliki riwayat transaksi atau penarikan.',
                ]);
            }

            if ($paymentMethod->qr_image) {
                Storage::disk('public')->delete($paymentMethod->qr_image);
            }

            $paymentMethod->delete();
        });
    }

    /**
     * Toggle the active status of a payment method.
     */
    public function toggleStatus(PaymentMethod $paymentMethod): PaymentMethod
    {
        if ($paymentMethod->type === 'cash') {
            throw ValidationException::withMessages([
                'payment_method' => 'Status metode pembayaran Tunai tidak dapat dinonaktifkan.',
            ]);
        }

        return DB::transaction(function () use ($paymentMethod) {
            $paymentMethod->update([
                'is_active' => ! $paymentMethod->is_active,
            ]);

            return $paymentMethod;
        });
    }
}
