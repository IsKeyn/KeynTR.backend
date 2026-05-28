<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BgPlayerPositionRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|integer|sometimes',
            'position' => 'required|integer|sometimes',
            'board_game_id' => 'required|integer|sometimes',
            'has_use_effect' => 'sometimes|boolean|nullable',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean|nullable',
            'created_by' => 'sometimes|integer|nullable',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'user_id обязательно для заполнения.',
            'position.required' => 'position обязателен для заполнения.',
            'board_game_id.required' => 'board_game_id обязателен для заполнения.',
        ];
    }
}
