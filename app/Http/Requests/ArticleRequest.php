<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        $articleId = $this->route('article')?->id ?? $this->route('id') ?? $this->get('id');

        return [
            'name' => 'required|string',
            'slug' => [
                'required',
                'string',
                'alpha_dash',
                Rule::unique(Article::TABLE_NAME, 'slug')
                    ->ignore($articleId)
                    ->where(function ($query) {
                        $entityId = $this->input('entity_id');
                        $entityType = $this->input('entity_type');

                        // Если оба поля null — проверяем уникальность среди записей с null значениями
                        if (is_null($entityId) && is_null($entityType)) {
                            return $query
                                ->whereNull('entity_id')
                                ->whereNull('entity_type');
                        }

                        // Если значения есть — проверяем уникальность в рамках этой пары entity_id + entity_type
                        return $query
                            ->where('entity_id', $entityId)
                            ->where('entity_type', $entityType);
                }),
            ],
            'text_preview' => 'sometimes|string|nullable',
            'text_full' => 'sometimes|string|nullable',
            'title_image' => 'sometimes|nullable',
            'type' => 'sometimes|integer|nullable',
            'created_by' => 'sometimes|integer|nullable',
            'editor' => 'sometimes|integer|nullable',
            'show_author' => 'sometimes|boolean|nullable',
            'show_editor' => 'sometimes|boolean|nullable',
            'entity_id' => 'sometimes|nullable|boolean',
            'entity_type' => 'sometimes|nullable|boolean',
            'sort' => 'sometimes|integer|nullable',
            'active' => 'sometimes|boolean',
            'published_at' => 'sometimes|nullable|date',
            'created_at' => 'sometimes|nullable|date',

            'author' => 'sometimes|integer|nullable',
            'articleEditor' => 'sometimes|integer|nullable',

            'tags' => 'sometimes|nullable',
            'additional_fields' => 'sometimes|nullable',
            'people' => 'sometimes|nullable',
            'companies' => 'sometimes|nullable',
            'links' => 'sometimes|nullable',
            'seo' => 'sometimes|nullable',
            'blocks' => 'sometimes|nullable',
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
        // Генерация слага
        if ($this->missing('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => \Str::slug($this->get('name')),
            ]);
        }

        // Нормализация null-значений для entity_id и entity_type
        if ($this->input('entity_id') === 'null' || $this->input('entity_id') === '') {
            $this->merge(['entity_id' => null]);
        }
        if ($this->input('entity_type') === 'null' || $this->input('entity_type') === '') {
            $this->merge(['entity_type' => null]);
        }
    }
}
