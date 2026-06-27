<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->shop !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shopId = auth()->user()->shop?->id ?? 0;

        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('shop_id', $shopId)),
            ],
            'transaction_date' => [
                'required',
                'date',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'payment_method_type' => [
                'required',
                'string',
                'in:cash,nontunai',
            ],
            'payment_method_id' => [
                'required_if:payment_method_type,nontunai',
                'nullable',
                Rule::exists('payment_methods', 'id')->where(fn ($query) => $query->where('shop_id', $shopId)->where('type', '!=', 'cash')->where('is_active', true)),
            ],
            'proof_image' => [
                'required',
                'file',
                'mimes:jpeg,png,jpg,pdf',
                'max:10240',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.min' => 'Nominal transaksi harus lebih besar dari 0.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'payment_method_id.required_if' => 'Metode pembayaran non-tunai wajib dipilih.',
            'payment_method_id.exists' => 'Metode pembayaran non-tunai yang dipilih tidak valid atau tidak aktif.',
            'proof_image.required' => 'Bukti transaksi wajib diunggah.',
            'proof_image.mimes' => 'Format bukti transaksi harus berupa JPEG, PNG, atau PDF.',
            'proof_image.max' => 'Ukuran bukti transaksi tidak boleh melebihi 10 MB.',
        ];
    }
}
