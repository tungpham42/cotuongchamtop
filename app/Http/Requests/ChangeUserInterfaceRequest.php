<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeUserInterfaceRequest extends FormRequest
{
    // Kept in sync with the <option> values in app/changeUi.blade.php.
    public const BOARD_THEMES = ['xiangqi-board', 'ban-co-go', 'wood-board', 'ban-co', 'banco', 'chess-board'];
    public const PIECES_THEMES = ['wiki', 'tung', 'do-den', 'graphic', 'co', 'wikimedia', 'quan', 'traditional'];

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Fix: previously unvalidated — any string was saved straight to
            // the user row and later used to build image paths, so a bad
            // value silently broke board/piece rendering.
            'board_theme' => ['required', Rule::in(self::BOARD_THEMES)],
            'pieces_theme' => ['required', Rule::in(self::PIECES_THEMES)],
        ];
    }

    public function messages(): array
    {
        return [
            'board_theme.required' => __('Vui lòng chọn bàn cờ.'),
            'board_theme.in' => __('Bàn cờ không hợp lệ.'),
            'pieces_theme.required' => __('Vui lòng chọn quân cờ.'),
            'pieces_theme.in' => __('Quân cờ không hợp lệ.'),
        ];
    }
}
