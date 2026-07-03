<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BgShopItemRequest extends FormRequest
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
            'board_game_id' => 'required|integer|exists:board_games,id',
            'bg_item_bind_id' => 'required|integer|exists:board_game_game_lists,id',
            'status' => 'required|integer',
            'bought_by_player_id' => 'nullable|integer|exists:board_game_players,id',
            'sort' => 'nullable|integer',
            'active' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'bg_player_id.required' => 'bg_player_id обязателен для заполнения.',
            'user_id.required' => 'user_id обязателен для заполнения.',
            'board_game_id.required' => 'board_game_id обязателен для заполнения.',
            'bg_item_bind_id.required' => 'bg_item_bind_id обязателен для заполнения.',
            'status.required' => 'status обязателен для заполнения.',
        ];
    }
}
