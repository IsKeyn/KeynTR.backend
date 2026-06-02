<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecommendationRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'url'         => 'nullable|max:2048',
            'description' => 'nullable|string|max:1000',
            'sort'        => 'nullable|integer|min:0',
            'active'      => 'nullable|boolean',
            'media_id'    => 'nullable|integer|exists:media,id',
            'tags'        => 'nullable|array',
            'tags.*'      => 'string|max:50', // Или 'integer|exists:tags,id', если это ID тегов
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Название обязательно для заполнения.',
            'name.unique'   => 'Элемент с таким названием уже существует.',
            'url.url'       => 'Поле должно содержать корректную ссылку.',
        ];
    }
}
