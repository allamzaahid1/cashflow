<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $paymentMethod = $this->route('payment_method');

        return auth()->check() && 
            auth()->user()->shop !== null && 
            $paymentMethod && 
            $paymentMethod->shop_id === auth()->user()->shop->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shopId = auth()->user()->shop?->id ?? 0;
        $paymentMethod = $this->route('payment_method');
        $paymentMethodId = $paymentMethod?->id ?? 0;
        $type = $this->input('type') ?? $paymentMethod?->type ?? 'qris';

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('payment_methods')
                    ->ignore($paymentMethodId)
                    ->where(fn ($query) => $query->where('shop_id', $shopId)->where('type', $type)),
            ],
            'type' => [
                'required',
                'string',
                'in:qris,transfer,ewallet',
            ],
            'account_name' => [
                'required',
                'string',
                'max:100',
            ],
            'account_number' => [
                'required',
                'string',
                'max:50',
            ],
            'qr_image' => [
                'nullable',
                'image',
                'max:2048',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Nama metode pembayaran sudah digunakan untuk tipe ini.',
        ];
    }
}
