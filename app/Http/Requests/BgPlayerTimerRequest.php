<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BgPlayerTimerRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'timer_id' => 'required|integer',
            'time_start' => 'sometimes|date|nullable',
            'time_stop' => 'sometimes|date|nullable',
            'created_by' => 'sometimes|integer|nullable',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Название обязательно для заполнения.',
            'slug.required' => 'Слаг обязателен для заполнения.',
        ];
    }
}
