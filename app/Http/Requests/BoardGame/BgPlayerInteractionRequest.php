<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BgPlayerInteractionRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'required|string',
            'status' => 'required|integer',
            'description' => 'nullable|string',
            'board_game_id' => 'required|integer|exists:board_games,id',
            'bg_player_id' => 'required|integer|exists:board_game_players,id',
            'with_player' => 'required|integer|exists:users,id',
            'created_by' => 'required|integer|exists:users,id',
            'entity_id' => 'nullable|integer',
            'entity_type' => 'nullable|string',
            'active' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'type обязательно для заполнения.',
            'status.required' => 'status обязателен для заполнения.',
            'board_game_id.required' => 'board_game_id обязателен для заполнения.',
            'bg_player_id.required' => 'bg_player_id обязателен для заполнения.',
            'with_player.required' => 'with_player обязателен для заполнения.',
            'created_by.required' => 'created_by обязателен для заполнения.',
        ];
    }
}
