<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')
                    ->where(fn ($query) => $query->where('shop_id', $shopId)->where('type', $this->input('type'))),
            ],
            'type' => [
                'required',
                'string',
                'in:income,expense',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Nama kategori sudah digunakan untuk tipe ini.',
        ];
    }
}
