<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $staff = $this->route('staff');

        return [
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($staff),
            ],
            'full_name' => ['required', 'string', 'max:100'],
            'password' => [
                $staff ? 'nullable' : 'required',
                'string',
                'confirmed',
                'min:12',
            ],
            'avatar' => [
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
            'username.alpha_dash' => 'Use letters, numbers, dashes, or underscores for the username.',
            'password.confirmed' => 'The password confirmation does not match.',
            'avatar.mimes' => 'Choose a JPG, PNG, or WebP image.',
            'avatar.extensions' => 'The image filename must end in .jpg, .jpeg, .png, or .webp.',
            'avatar.max' => 'The avatar must be 2 MB or smaller.',
            'avatar.dimensions' => 'The avatar must be no larger than 2400 by 2400 pixels.',
        ];
    }
}