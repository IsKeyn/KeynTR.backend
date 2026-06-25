<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BgLogRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'bg_player_id' => 'sometimes|integer|nullable',
            'message' => 'required|string',
            'board_game_id' => 'required|integer',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'created_by' => 'sometimes|integer|nullable',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'message.required' => 'user_id обязательно для заполнения.',
            'board_game_id.required' => 'board_game_id обязателен для заполнения.',
        ];
    }
}
