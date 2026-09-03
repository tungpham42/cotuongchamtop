<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserGameRequest extends FormRequest
{
    /**
     * Chỉ cần user đã đăng nhập là được phép gửi request này — việc giới
     * hạn chỉ admin mới CRUD được ván cờ đã nằm ở middleware của route
     * (nhóm 'admin' dùng ['auth', IsAdmin::class]), giống cách các Request
     * khác trong khu quản trị không tự kiểm tra quyền lần nữa ở đây.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'initial_fen' => ['required', 'string', 'max:255'],
            // 'moves' được gửi lên dưới dạng chuỗi JSON từ trình soạn thảo
            // ván cờ phía client (xem GameController::store/update, nơi nó
            // được json_decode trước khi lưu). max:65535 chặn payload bất
            // thường lớn trước khi chạm tới DB.
            'moves' => ['nullable', 'string', 'max:65535', function ($attribute, $value, $fail) {
                if ($value !== null && $value !== '' && json_decode($value, true) === null && json_last_error() !== JSON_ERROR_NONE) {
                    $fail('Dữ liệu nước đi (moves) không hợp lệ.');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề ván cờ.',
            'initial_fen.required' => 'Thiếu dữ liệu bàn cờ ban đầu (FEN).',
        ];
    }
}
