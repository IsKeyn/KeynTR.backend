<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GameRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        // Получаем ID из роута (при обновлении) или из запроса
        $gameId = $this->route('game')?->id ?? $this->route('id') ?? $this->get('id');

        return [
            'name' => 'required|string',
            'slug' => [
                'required',
                'string',
                'alpha_dash',
                Rule::unique('games', 'slug')->ignore($gameId),
            ],
            'description' => 'sometimes|string|nullable',
            'mod' => 'sometimes|boolean',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'show_in_list' => 'sometimes|boolean',
            'title_image' => 'sometimes|nullable',
            'covers' => 'sometimes|nullable',
            'additional_fields' => 'sometimes|nullable',
            'groups' => 'sometimes|nullable',
            'series' => 'sometimes|nullable',
            'genres' => 'sometimes|nullable',
            'companies' => 'sometimes|nullable',
            'tags' => 'sometimes|nullable',
            'seo' => 'sometimes|nullable',
            'links' => 'sometimes|nullable',
            'anons_dates' => 'sometimes|nullable',
            'release_dates' => 'sometimes|nullable',
            'blocks' => 'sometimes|nullable',
            'created_at' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Название обязательно для заполнения.',
            'slug.required' => 'Слаг обязателен для заполнения.',
            'slug.unique'   => 'Игра с таким слагом уже существует.',
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
