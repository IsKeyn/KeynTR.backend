<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BoardCellReviewSetRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям
        return true;
    }

    public function rules()
    {
        return [
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
            'completion_time_seconds' => 'nullable|integer',
            'comment' => 'required|string|max:5000',
        ];
    }

    public function messages()
    {
        return [
            'entity_type.required' => 'Не получено обязательное поле entity_type',
            'entity_id.required'   => 'Не получено обязательное поле entity_id',
            'comment.required' => 'Текст комментария обязателен для заполнения',
            'comment.max' => 'Текст комментария не должен превышать 5000 символов. Сейчас :count символов',
        ];
    }
}
