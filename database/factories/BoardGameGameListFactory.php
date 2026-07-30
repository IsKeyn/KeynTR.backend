<?php

namespace Database\Factories;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\Game;
use App\Models\GamingPlatform;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardGameGameListFactory extends Factory
{
    /**
     * Имя модели, для которой предназначена фабрика.
     *
     * @var class-string<\App\Models\BoardGame\BoardGameGameList>
     */
    protected $model = BoardGameGameList::class;

    /**
     * Определяет значения по умолчанию для атрибутов модели.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Связи с другими моделями.
            // Если у тебя еще нет GameFactory или GamingPlatformFactory,
            // замени Game::factory() на fn() => Game::inRandomOrder()->first()?->id ?? 1
            'game_id' => Game::factory(),
            'board_game_id' => BoardGame::factory(),
            'gaming_platform_id' => GamingPlatform::factory(),

            'points' => $this->faker->numberBetween(10, 500),
            'difficult' => $this->faker->numberBetween(1, 100),
            'game_completion_time' => $this->faker->numberBetween(30, 300), // в минутах
            'coop' => $this->faker->boolean(),

            // КРИТИЧЕСКИ ВАЖНО: По умолчанию список обычный (null),
            // так как сервис проверяет ->where('list_type', null) для дефолтного списка
            'list_type' => null,

            'description' => $this->faker->sentence(),
            'sort' => $this->faker->numberBetween(1, 100),

            // КРИТИЧЕСКИ ВАЖНО: Должно быть true, так как в сервисе используется скоуп ->active()
            'active' => true,

            'source' => $this->faker->randomElement(['manual', 'auto', 'api']),
            'added_by' => User::factory(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Состояние: Игра из "Золотого списка"
     * Использование: BoardGameGameList::factory()->golden()->create()
     */
    public function golden(): static
    {
        return $this->state(fn (array $attributes) => [
            'list_type' => BoardGameGameList::GOLDEN_LIST,
        ]);
    }

    /**
     * Состояние: Неактивная игра (для проверки того, что скоуп ->active() её отсекает)
     * Использование: BoardGameGameList::factory()->inactive()->create()
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Состояние: Игра для конкретной платформы
     * Использование: BoardGameGameList::factory()->forPlatform(5)->create()
     */
    public function forPlatform(int $platformId): static
    {
        return $this->state(fn (array $attributes) => [
            'gaming_platform_id' => $platformId,
        ]);
    }
}
