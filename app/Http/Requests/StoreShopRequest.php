<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'logo' => [
                'nullable',
                'image',
                'max:2048',
            ],

        ];
    }
}