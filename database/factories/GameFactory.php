<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    /**
     * Имя модели, для которой предназначена фабрика.
     *
     * @var class-string<\App\Models\Game>
     */
    protected $model = Game::class;

    /**
     * Определяет значения по умолчанию для атрибутов модели.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Генерируем название игры из 2-4 слов, например: "The Witcher 3"
            'name' => $this->faker->words(rand(2, 4), true),

            // Slug ОБЯЗАТЕЛЬНО должен быть уникальным
            'slug' => $this->faker->unique()->slug(),

            'description' => $this->faker->paragraph(),

            // Логические поля
            'mod' => $this->faker->boolean(20), // 20% шанс, что это мод
            'active' => true, // По умолчанию игра активна
            'show_in_list' => true, // По умолчанию показываем в списке

            'spc_id' => $this->faker->randomNumber(5), // Внешний ID
            'sort' => $this->faker->numberBetween(1, 100),

            // Автоматически создаем пользователя-создателя
            'created_by' => User::factory(),
        ];
    }

    /**
     * Состояние: Неактивная игра
     * Использование: Game::factory()->inactive()->create()
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Состояние: Игра-мод
     * Использование: Game::factory()->mod()->create()
     */
    public function mod(): static
    {
        return $this->state(fn (array $attributes) => [
            'mod' => true,
        ]);
    }

    /**
     * Состояние: Скрытая игра (не показывается в списке)
     * Использование: Game::factory()->hidden()->create()
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_list' => false,
        ]);
    }
}
