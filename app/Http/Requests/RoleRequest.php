<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
            'system_name' => 'required|string',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'title_image' => 'sometimes|nullable',
            'additional_fields' => 'sometimes|nullable',
            'permissions' => 'sometimes|nullable',
            'tags' => 'sometimes|nullable',
            'blocks' => 'sometimes|nullable',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Название обязательно для заполнения.',
            'system_name.required' => 'System name обязателен для заполнения.',
        ];
    }
}
