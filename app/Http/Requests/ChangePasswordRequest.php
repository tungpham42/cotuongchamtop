<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8'],
            'new_confirm_password' => ['required', 'same:new_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => __('Mật khẩu hiện tại bắt buộc điền.'),
            'new_password.required' => __('Mật khẩu mới bắt buộc điền.'),
            'new_password.min' => __('Mật khẩu mới phải ít nhất 8 ký tự.'),
            'new_confirm_password.required' => __('Mật khẩu lặp lại bắt buộc điền.'),
            'new_confirm_password.same' => __('Mật khẩu lặp lại và mật khẩu mới phải giống nhau.'),
        ];
    }
}
