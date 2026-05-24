<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimerRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'slug' => 'required|string',
            'description' => 'sometimes|string|nullable',
            'limit' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'user_id' => 'required|integer',
            'board_game_id' => 'sometimes|integer|nullable',
            'created_by' => 'sometimes|integer|nullable',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Название обязательно для заполнения.',
            'slug.required' => 'Слаг обязателен для заполнения.',
            'user_id.required' => 'user_id обязателен для заполнения.',
            'user_id.integer' => 'user_id должен быть id пользователя',
        ];
    }
}
