<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BgItemBindRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'item_id' => 'required|integer|sometimes',
            'board_game_id' => 'required|integer|sometimes',
            'active' => 'sometimes|boolean|nullable',
            'created_by' => 'sometimes|integer|nullable',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'item_id.required' => 'item_id обязательно для заполнения.',
            'board_game_id.required' => 'board_game_id обязателен для заполнения.',
        ];
    }
}
