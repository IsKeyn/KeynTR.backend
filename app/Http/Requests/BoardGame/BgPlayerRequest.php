<?php

namespace App\Http\Requests\BoardGame;

use Illuminate\Foundation\Http\FormRequest;

class BgPlayerRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|integer',
            'board_game_id' => 'required|integer',
            'points' => 'sometimes|integer|nullable',
            'points_per_hour' => 'sometimes|integer|nullable',
            'place' => 'sometimes|integer|nullable',
            'item_roll_count' => 'sometimes|integer|nullable',
            'step_count' => 'sometimes|integer|nullable',
            'streak' => 'sometimes|integer|nullable',
            'rerolled_own_game_count' => 'sometimes|integer|nullable',
            'not_active_reason' => 'sometimes|string|nullable',
            'settings' => 'nullable|array',
            'premium' => 'sometimes|boolean',
            'media' => 'sometimes|nullable',
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
            'created_at' => 'sometimes|nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'user_id обязательно для заполнения.',
            'board_game_id.required' => 'board_game_id обязателен для заполнения.',
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
