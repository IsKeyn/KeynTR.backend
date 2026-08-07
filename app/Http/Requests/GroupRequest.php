<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupRequest extends FormRequest
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
            'slug' => 'sometimes|string|nullable',
            'description' => 'sometimes|string|nullable',
            'entity_type' => 'sometimes|string|nullable',
            'type' => 'nullable|integer',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'title_image' => 'sometimes|nullable',
            'additional_fields' => 'sometimes|nullable',
            'tags' => 'sometimes|nullable',
            'blocks' => 'sometimes|nullable',
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
