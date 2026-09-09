<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BoardCellReviewGetRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям
        return true;
    }

    public function rules()
    {
        return [
            'board_position_effects_id' => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'board_position_effects_id.required'   => 'Не получено обязательное поле board_position_effects_id',
        ];
    }
}
