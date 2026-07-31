<?php

namespace App\Http\Requests\BoardGame;

use App\Models\BoardGame\StatusEffect;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BgStatusEffectRequest extends FormRequest
{
    public function authorize()
    {
        // Разрешаем всем авторизованным пользователям (или ваша логика)
        return true;
    }

    public function rules()
    {
        $id = $this->route('statusEffect')?->id ?? $this->route('id') ?? $this->get('id');

        return [
            'type' => 'sometimes|integer|nullable',
            'name' => 'required|string|sometimes',
            'slug' => [
                'required',
                'string',
                'alpha_dash',
                Rule::unique(StatusEffect::TABLE_NAME, 'slug')->ignore($id),
            ],
            'description' => 'sometimes|string|nullable',
            'board_game_player_id' => 'sometimes|integer|nullable',
            'board_game_id' => 'sometimes|integer|nullable',
            'actions' => 'nullable|array',
            'debuff' => 'sometimes|boolean|nullable',
            'title_image' => 'sometimes|integer|nullable',
            'sound' => 'sometimes|integer|nullable',
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
