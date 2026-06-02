<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GamingPlatformRequest extends FormRequest
{
    private const NAME = 'genre';
    private const TABLE_NAME = 'genres';

    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        // Получаем ID из роута (при обновлении) или из запроса
        $id = $this->route(self::NAME)?->id ?? $this->route('id') ?? $this->get('id');

        return [
            'name' => 'required|string',
            'slug' => [
                'required',
                'string',
                'alpha_dash',
                Rule::unique(self::TABLE_NAME, 'slug')->ignore($id),
            ],
            'description' => 'sometimes|string|nullable',
            'type' => 'sometimes|integer|nullable',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'spc_id' => 'sometimes|string|nullable',
            'title_image' => 'sometimes|nullable',
            'covers' => 'sometimes|nullable',
            'additional_fields' => 'sometimes|nullable',
            'groups' => 'sometimes|nullable',
            'game' => 'sometimes|nullable',
            'tags' => 'sometimes|nullable',
            'seo' => 'sometimes|nullable',
            'blocks' => 'sometimes|nullable',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Название обязательно для заполнения.',
            'slug.required' => 'Слаг обязателен для заполнения.',
            'slug.unique'   => 'Элемент с таким слагом уже существует.',
            'slug.alpha_dash' => 'Слаг может содержать только буквы, цифры, дефисы и подчёркивания.',
        ];
    }

    /**
     * Подготовка данных перед валидацией (опционально)
     * Например, генерация слага из названия, если он не передан
     */
    protected function prepareForValidation()
    {
        if ($this->missing('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => \Str::slug($this->get('name')),
            ]);
        }
    }
}
