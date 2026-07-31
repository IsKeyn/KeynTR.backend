<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BgPlayerStatusEffectRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или другая логика)
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'sometimes|integer|nullable',
            'bg_player_id' => 'required|integer|nullable',
            'board_game_id' => 'sometimes|integer|nullable',
            'status_effect_id' => 'sometimes|integer|nullable',
            'status_effect_bind_id' => 'required|integer|nullable',
            'active' => 'sometimes|boolean|nullable',
            'created_by' => 'sometimes|integer|nullable',
        ];
    }

    public function messages()
    {
        return [
            'board_game_player_id.required' => 'board_game_player_id обязательно для заполнения.',
            'status_effect_bind_id.required' => 'status_effect_bind_id обязателен для заполнения.',
        ];
    }
}
