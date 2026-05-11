<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'public_name' => 'sometimes|string|nullable',
            'email' => 'required|string',
            'email_verified_at' => 'sometimes|date|nullable',
            'password' => 'sometimes|string|nullable',
            'settings' => 'sometimes|string|nullable',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'title_image' => 'sometimes|nullable',
            'additional_fields' => 'sometimes|nullable',
            'roles' => 'sometimes|nullable',
            'tags' => 'sometimes|nullable',
            'blocks' => 'sometimes|nullable',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Название обязательно для заполнения.',
            'email.required' => 'email обязателен для заполнения.',
        ];
    }
}
