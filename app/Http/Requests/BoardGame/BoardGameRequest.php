<?php

namespace App\Http\Requests\BoardGame;

use App\Models\BoardGame\BoardGame;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BoardGameRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        $id = $this->route('BoardGame')?->id ?? $this->route('id') ?? $this->get('id');

        return [
            'name' => 'required|string',
            'slug' => [
                'required',
                'string',
                'alpha_dash',
                Rule::unique(BoardGame::TABLE_NAME, 'slug')->ignore($id),
            ],
            'description' => 'sometimes|string|nullable',
            'is_close' => 'sometimes|boolean|nullable',
            'started_at' => 'sometimes|date|nullable',
            'ended_at' => 'sometimes|date|nullable',

            'media' => 'sometimes|nullable',
            'settings' => 'sometimes|nullable',

            'tags' => 'sometimes|nullable',
            'additional_fields' => 'sometimes|nullable',
            'people' => 'sometimes|nullable',
            'companies' => 'sometimes|nullable',
            'links' => 'sometimes|nullable',
            'seo' => 'sometimes|nullable',
            'blocks' => 'sometimes|nullable',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'created_by' => 'sometimes|integer|nullable',
            'published_at' => 'sometimes|nullable|date',
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Название обязательно для заполнения.',
            'slug.required' => 'Слаг обязателен для заполнения.',
            'slug.unique'   => 'Сущность с таким слагом уже существует.',
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
