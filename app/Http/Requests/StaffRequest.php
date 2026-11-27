<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web')?->can('manage-staff') === true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        $staff = $this->route('staff');

        return [
            'email' => [
                'required',
                'email:rfc',
                'max:100',
                Rule::unique('users', 'email')->ignore($staff),
                Rule::unique('customers', 'email'),
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
            'password.confirmed' => 'The password confirmation does not match.',
            'avatar.mimes' => 'Choose a JPG, PNG, or WebP image.',
            'avatar.extensions' => 'The image filename must end in .jpg, .jpeg, .png, or .webp.',
            'avatar.max' => 'The avatar must be 2 MB or smaller.',
            'avatar.dimensions' => 'The avatar must be no larger than 2400 by 2400 pixels.',
        ];
    }
}
