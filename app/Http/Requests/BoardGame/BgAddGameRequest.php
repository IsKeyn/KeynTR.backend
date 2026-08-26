<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BgAddGameRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'bg_player_id' => 'required|integer|exists:board_game_players,id',
            'user_id' => 'required|integer|exists:users,id',
            'board_game_id' =>  'required|integer|exists:board_games,id',
            'name' => 'nullable|string|max:1000',
            'gaming_platform_id' => 'nullable|integer|exists:gaming_platforms,id',
            'coop' => 'nullable|boolean',
            'game_completion_time' => 'nullable|string|max:1000',
            'difficulty' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string|max:5000',
            'comment_for_moderator' => 'nullable|string|max:5000',
            'moderator_comment' => 'nullable|string|max:5000',
            'status' => 'nullable|integer',
            'sort' => 'nullable|integer',
            'active' => 'nullable|boolean',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'bg_player_id.required' => 'bg_player_id обязательно для заполнения.',
            'user_id.required' => 'user_id обязательно для заполнения.',
            'board_game_id.required' => 'board_game_id обязательно для заполнения.',
        ];
    }
}
