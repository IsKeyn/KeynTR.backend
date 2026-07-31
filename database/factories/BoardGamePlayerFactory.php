<?php

namespace Database\Factories;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardGamePlayerFactory extends Factory
{
    /**
     * Имя модели, для которой предназначена фабрика.
     *
     * @var class-string<\App\Models\BoardGame\BoardGamePlayer>
     */
    protected $model = BoardGamePlayer::class;

    /**
     * Определяет значения по умолчанию для атрибутов модели.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Автоматически создаем связанные модели, если они не переданы вручную
            'user_id' => User::factory(),
            'board_game_id' => BoardGame::factory(),

            // Игровые метрики (генерируем реалистичные случайные значения)
            'points' => $this->faker->numberBetween(0, 5000),
            'points_per_hour' => $this->faker->randomFloat(2, 0, 100),
            'place' => $this->faker->numberBetween(1, 100),
            'item_roll_count' => $this->faker->numberBetween(0, 10),
            'step_count' => $this->faker->numberBetween(0, 50),
            'streak' => $this->faker->numberBetween(0, 30),

            // Ключевые поля для логики твоего сервиса.
            // По умолчанию ставим 0, чтобы в тестах можно было легко переопределить их для проверки условий.
            'rerolled_game_count' => 0,
            'rerolled_own_game_count' => 0,
            'added_games' => 0,

            'active' => true,
            'not_active_reason' => null,

            // Критически важно для теста: инициализируем массив с пустым исключением платформ
            'settings' => [
                'exceptionPlatforms' => [],
            ],

            'premium' => false,
            'sort' => $this->faker->numberBetween(1, 100),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Состояние: Игрок достиг порога для списка "Реролл" (rerolled_own_game_count)
     * Использование: BoardGamePlayer::factory()->reachedRerollThreshold()->create()
     */
    public function reachedRerollThreshold(): static
    {
        return $this->state(fn (array $attributes) => [
            'rerolled_own_game_count' => 5, // Заведомо больше дефолтного значения 2 из настроек игры
        ]);
    }

    /**
     * Состояние: Игрок достиг порога для "Золотого списка" (rerolled_game_count)
     * Использование: BoardGamePlayer::factory()->reachedGoldenThreshold()->create()
     */
    public function reachedGoldenThreshold(): static
    {
        return $this->state(fn (array $attributes) => [
            'rerolled_game_count' => 5, // Заведомо больше дефолтного значения 3 из настроек игры
        ]);
    }

    /**
     * Состояние: У игрока есть исключенные платформы
     * Использование: BoardGamePlayer::factory()->withExceptionPlatforms([1, 2])->create()
     */
    public function withExceptionPlatforms(array $platformIds): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => array_merge($attributes['settings'] ?? [], [
                'exceptionPlatforms' => $platformIds,
            ]),
        ]);
    }

    /**
     * Состояние: Премиум игрок
     * Использование: BoardGamePlayer::factory()->premium()->create()
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'premium' => true,
        ]);
    }

    /**
     * Состояние: Неактивный игрок
     * Использование: BoardGamePlayer::factory()->inactive()->create()
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
            'not_active_reason' => $this->faker->sentence(),
        ]);
    }
}
