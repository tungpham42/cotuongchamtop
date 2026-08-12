<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Fix: ignore the authenticated user's own row so re-submitting
            // (or keeping) their current name doesn't falsely trigger the
            // "name already taken" error.
            'new_name' => [
                'required',
                'min:3',
                'max:15',
                Rule::unique('users', 'name')->ignore(auth()->id()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'new_name.required' => __('Tên mới bắt buộc điền.'),
            'new_name.min' => __('Tên mới phải ít nhất 3 ký tự.'),
            'new_name.max' => __('Tên mới phải ít hơn 16 ký tự.'),
            'new_name.unique' => __('Tên này đã được sử dụng.'),
        ];
    }
}
