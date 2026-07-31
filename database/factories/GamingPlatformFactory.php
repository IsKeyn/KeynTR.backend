<?php

namespace Database\Factories;

use App\Models\GamingPlatform;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GamingPlatformFactory extends Factory
{
    /**
     * Имя модели, для которой предназначена фабрика.
     *
     * @var class-string<\App\Models\GamingPlatform>
     */
    protected $model = GamingPlatform::class;

    /**
     * Определяет значения по умолчанию для атрибутов модели.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Генерируем реалистичные названия платформ, например: "Sony PlayStation 5"
            'name' => $this->faker->words(3, true),
            'short_name' => $this->faker->lexify('???'), // Например: "PS5", "XBX", "PC_"

            // Slug ОБЯЗАТЕЛЬНО должен быть уникальным, чтобы избежать ошибок БД
            'slug' => $this->faker->unique()->slug(),

            'description' => $this->faker->sentence(),
            'release_date' => $this->faker->date('Y-m-d', 'now'), // Дата выпуска до сегодняшнего дня
            'spc_id' => $this->faker->randomNumber(5), // Какой-то внешний ID

            'sort' => $this->faker->numberBetween(1, 100),

            // По умолчанию платформа активна, чтобы не ломать логику выборки
            'active' => true,

            // Автоматически создаем пользователя-создателя
            'created_by' => User::factory(),
        ];
    }

    /**
     * Состояние: Неактивная платформа
     * Использование: GamingPlatform::factory()->inactive()->create()
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
