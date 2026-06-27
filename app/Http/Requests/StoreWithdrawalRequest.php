<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWithdrawalRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $shopId = auth()->user()->shop?->id ?? 0;

        return [
            'amount' => [
                'required',
                'numeric',
                'min:10000',
            ],
            'payment_method_id' => [
                'required',
                Rule::exists('payment_methods', 'id')->where(fn ($query) => $query->where('shop_id', $shopId)->where('type', '!=', 'cash')->where('is_active', true)),
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal penarikan dana wajib diisi.',
            'amount.numeric' => 'Nominal penarikan dana harus berupa angka.',
            'amount.min' => 'Nominal penarikan minimal Rp 10.000.',
            'payment_method_id.required' => 'Bank tujuan transfer wajib dipilih.',
            'payment_method_id.exists' => 'Akun bank tujuan transfer tidak valid atau tidak aktif.',
        ];
    }
}
