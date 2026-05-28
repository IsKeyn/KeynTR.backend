<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BgGameListRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'game_id' => 'required|integer',
            'board_game_id' => 'required|integer',
            'gaming_platform_id' => 'sometimes|integer|nullable',
            'points' => 'sometimes|integer|nullable',
            'difficult' => 'sometimes|integer|nullable',
            'game_completion_time' => 'sometimes|integer|nullable',
            'coop' => 'sometimes|boolean|nullable',
            'list_type' => 'sometimes|integer|nullable',
            'description' => 'sometimes|string|nullable',
            'source' => 'sometimes|string|nullable',
            'added_by' => 'sometimes|integer|nullable',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'created_by' => 'sometimes|integer|nullable',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'game_id.required' => 'user_id обязательно для заполнения.',
            'board_game_id.required' => 'board_game_id обязателен для заполнения.',
        ];
    }
}
