<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BgPlayerGameRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|integer',
            'board_game_game_list_id' => 'required|integer',
            'status' => 'sometimes|integer|nullable',
            'board_game_id' => 'required|integer',
            'type' => 'sometimes|integer|nullable',
            'from_user_id' => 'sometimes|integer|nullable',
            'comment_id' => 'sometimes|integer|nullable',
            'time' => 'sometimes|integer|nullable',
            'points' => 'sometimes|integer|nullable',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'created_by' => 'sometimes|integer|nullable',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'user_id обязательно для заполнения.',
            'board_game_game_list_id.required' => 'board_game_id обязателен для заполнения.',
            'board_game_id.required' => 'board_game_id обязателен для заполнения.',
        ];
    }
}
