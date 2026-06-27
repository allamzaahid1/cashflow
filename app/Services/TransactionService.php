<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Shop;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;

class TransactionService
{
    /**
     * Create a new transaction.
     */
    public function create(Shop $shop, array $data): Transaction
    {
        return DB::transaction(function () use ($shop, $data) {
            $paymentMethod = null;

            if ($data['payment_method_type'] === 'cash') {
                $paymentMethod = $shop->paymentMethods()->where('type', 'cash')->first();
                if (! $paymentMethod) {
                    throw ValidationException::withMessages([
                        'payment_method' => 'Metode pembayaran Tunai tidak ditemukan untuk toko ini.',
                    ]);
                }
            } else {
                $paymentMethodId = $data['payment_method_id'] ?? null;
                if (! $paymentMethodId) {
                    throw ValidationException::withMessages([
                        'payment_method_id' => 'Metode pembayaran non-tunai wajib dipilih.',
                    ]);
                }

                $paymentMethod = $shop->paymentMethods()
                    ->where('id', $paymentMethodId)
                    ->where('type', '!=', 'cash')
                    ->where('is_active', true)
                    ->first();

                if (! $paymentMethod) {
                    throw ValidationException::withMessages([
                        'payment_method_id' => 'Metode pembayaran non-tunai tidak valid atau tidak aktif.',
                    ]);
                }
            }

            // Generate transaction code: TRX-YYYYMMDD-XXXXXX
            $dateStr = now()->format('Ymd');
            $prefix = 'TRX-' . $dateStr . '-';

            // Retrieve last transaction code globally for today to determine sequential number
            $lastTx = Transaction::where('transaction_code', 'like', $prefix . '%')
                ->orderBy('transaction_code', 'desc')
                ->lockForUpdate()
                ->first();

            $seq = 1;
            if ($lastTx) {
                $lastSeq = (int) substr($lastTx->transaction_code, -6);
                $seq = $lastSeq + 1;
            }

            $transactionCode = $prefix . str_pad($seq, 6, '0', STR_PAD_LEFT);

            // Handle file upload
            $proofPath = null;
            if (isset($data['proof_image']) && $data['proof_image'] instanceof UploadedFile) {
                $proofPath = $data['proof_image']->store('proofs', 'public');
            }

            return Transaction::create([
                'shop_id' => $shop->id,
                'category_id' => $data['category_id'],
                'payment_method_id' => $paymentMethod->id,
                'transaction_code' => $transactionCode,
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'proof_image' => $proofPath,
                'transaction_date' => $data['transaction_date'],
            ]);
        });
    }
}
