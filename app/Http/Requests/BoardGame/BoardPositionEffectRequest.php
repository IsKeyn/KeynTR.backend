<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BoardPositionEffectRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        $id = $this->route('BoardPositionEffect')?->id ?? $this->route('id') ?? $this->get('id');

        return [
            'name' => 'required|string|sometimes',
            'slug' => [
                'required',
                'string',
                'alpha_dash',
                Rule::unique('games', 'slug')->ignore($id),
            ],
            'description' => 'sometimes|string|nullable',
            'actions' => 'sometimes|string|nullable',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean|nullable',
            'created_by' => 'sometimes|integer|nullable',
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
