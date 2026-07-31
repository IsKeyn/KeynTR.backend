<?php

namespace Database\Factories;

use App\Models\BoardGame\BoardGame;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardGameFactory extends Factory
{
    /**
     * Имя модели, для которой предназначена фабрика.
     *
     * @var class-string<\App\Models\BoardGame\BoardGame>
     */
    protected $model = BoardGame::class;

    /**
     * Определяет значения по умолчанию для атрибутов модели.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Генерируем название из 3 слов, например: "Epic Space Adventure"
            'name' => $this->faker->words(3, true),

            // Slug должен быть уникальным, чтобы не было ошибок базы данных
            'slug' => $this->faker->unique()->slug(),

            'description' => $this->faker->paragraph(),
            'active' => true, // По умолчанию игра активна
            'sort' => $this->faker->numberBetween(1, 100),
            'is_close' => false, // По умолчанию игра открыта
            'is_test' => false,

            // Даты настроены так, чтобы метод getStatusAttribute возвращал OPEN_STATUS (1)
            'started_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'ended_at' => $this->faker->dateTimeBetween('now', '+1 month'),

            // Автоматически создаем пользователя, который создал игру (если в тесте не передан свой)
            'created_by' => User::factory(),
        ];
    }

    /**
     * Состояние (State): Закрытая игра.
     * Использование: BoardGame::factory()->closed()->create()
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_close' => true,
            'ended_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'), // Игра уже закончилась
        ]);
    }

    /**
     * Состояние (State): Тестовая игра.
     * Использование: BoardGame::factory()->testGame()->create()
     */
    public function testGame(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_test' => true,
        ]);
    }

    /**
     * Состояние (State): Будущая игра (Coming Soon).
     * Использование: BoardGame::factory()->upcoming()->create()
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'ended_at' => $this->faker->dateTimeBetween('+2 months', '+3 months'),
        ]);
    }
}
