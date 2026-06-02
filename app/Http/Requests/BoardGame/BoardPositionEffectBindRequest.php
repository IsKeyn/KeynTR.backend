<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BoardPositionEffectBindRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'position_effect_id' => 'required|integer|sometimes',
            'board_game_id' => 'required|integer|sometimes',
            'position' => 'required|integer|sometimes',
            'active' => 'sometimes|boolean|nullable',
            'created_by' => 'sometimes|integer|nullable',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'position_effect_id.required' => 'position_effect_id обязательно для заполнения.',
            'board_game_id.required' => 'board_game_id обязателен для заполнения.',
            'position.required' => 'position обязателен для заполнения.',
        ];
    }
}
