<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
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
            'message' => 'required|string',
            'actions' => 'sometimes|nullable|array',
            'viewed' => 'sometimes|nullable|boolean',
            'entity_type' => 'sometimes|nullable|string',
            'entity_id' => 'sometimes|nullable|integer',
            'created_by' => 'sometimes|integer|nullable',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'user_id обязательно для заполнения.',
            'message.required' => 'message обязательно для заполнения.',
        ];
    }
}
