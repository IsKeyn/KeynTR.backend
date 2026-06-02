<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
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
            'url' => 'required|string',
            'sort' => 'sometimes|integer|nullable',
            'target' => 'sometimes|string|nullable',
            'menu_type_id' => 'sometimes|integer|nullable',
            'link_type' => 'sometimes|string|nullable',
            'icon' => 'sometimes|string|nullable',
            'active' => 'sometimes|boolean',
            'title_image' => 'sometimes|nullable',
            'additional_fields' => 'sometimes|nullable',
            'tags' => 'sometimes|nullable',
            'blocks' => 'sometimes|nullable',
            'permissions' => 'sometimes|nullable',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Название обязательно для заполнения.',
        ];
    }
}
