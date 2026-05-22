<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'site_id' => 'required|integer',
            'name' => 'required|string',
            'code' => 'required|string',
            'value' => 'required|string',
            'entity_type' => 'sometimes|nullable|string',
            'entity_id' => 'sometimes|nullable|integer',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'site_id.required' => 'site_id обязательно для заполнения.',
            'name.required' => 'Название обязательно для заполнения.',
            'code.required' => 'System name обязателен для заполнения.',
            'value.required' => 'value name обязателен для заполнения.',
        ];
    }
}
