<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $category = $this->route('category');

        return auth()->check() && 
            auth()->user()->shop !== null && 
            $category && 
            $category->shop_id === auth()->user()->shop->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shopId = auth()->user()->shop?->id ?? 0;
        $category = $this->route('category');
        $categoryId = $category?->id ?? 0;
        $type = $this->input('type') ?? $category?->type ?? 'income';

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')
                    ->ignore($categoryId)
                    ->where(fn ($query) => $query->where('shop_id', $shopId)->where('type', $type)),
            ],
            'type' => [
                'nullable',
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
