<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:99999999.99',
                'regex:/^\d{1,8}(\.\d{1,2})?$/',
            ],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'image' => [
                'nullable',
                File::image(),
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=2400,max_height=2400',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'price.regex' => 'Enter a price with no more than two decimal places.',
            'image.mimes' => 'Choose a JPG, PNG, or WebP image.',
            'image.extensions' => 'The image filename must end in .jpg, .jpeg, .png, or .webp.',
            'image.max' => 'The image must be 2 MB or smaller.',
            'image.dimensions' => 'The image must be no larger than 2400 by 2400 pixels.',
        ];
    }
}