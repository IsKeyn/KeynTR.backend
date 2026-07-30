<?php

namespace Tests\Unit\Services\BoardGame;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use App\Services\BoardGame\BgPlayerGameService;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\User;

class BgPlayerGameServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BgPlayerGameService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BgPlayerGameService();
    }

    #[Test]
    public function it_returns_default_game_list_when_no_rerolls_made(): void
    {
        $user = User::factory()->create();
        $boardGame = BoardGame::factory()->create();

        $player = BoardGamePlayer::factory()->create([
            'user_id' => $user->id,
            'board_game_id' => $boardGame->id,
            'rerolled_own_game_count' => 0,
            'rerolled_game_count' => 0,
        ]);

        $boardGame->settings()->create(['code' => 'rerolled_own_game_count_for_rerolled_list', 'value' => 2]);
        $boardGame->settings()->create(['code' => 'rerolled_game_count_for_gold_list', 'value' => 3]);

        $defaultGame = BoardGameGameList::factory()->create([
            'board_game_id' => $boardGame->id,
            'list_type' => null,
        ]);

        $conditionData = [
            'boardGame' => $boardGame,
            'player' => $player,
            'user' => $user,
        ];

        $result = $this->service->getFilteredGameList(null, $conditionData);

        $this->assertEquals('default', $result['listType']);
        $this->assertCount(1, $result['gameList']);
        $this->assertEquals($defaultGame->id, $result['gameList']->first()->id);
    }

    #[Test]
    public function it_returns_golden_list_when_rerolled_game_count_reached(): void
    {
        $user = User::factory()->create();
        $boardGame = BoardGame::factory()->create();

        $player = BoardGamePlayer::factory()->reachedGoldenThreshold()->create([
            'user_id' => $user->id,
            'board_game_id' => $boardGame->id,
        ]);

        $boardGame->settings()->create(['code' => 'rerolled_own_game_count_for_rerolled_list', 'value' => 2]);
        $boardGame->settings()->create(['code' => 'rerolled_game_count_for_gold_list', 'value' => 3]);

        $goldenGame = BoardGameGameList::factory()->golden()->create([
            'board_game_id' => $boardGame->id,
        ]);

        $conditionData = [
            'boardGame' => $boardGame,
            'player' => $player,
            'user' => $user,
        ];

        $result = $this->service->getFilteredGameList(null, $conditionData);

        $this->assertEquals('golden', $result['listType']);
        $this->assertCount(1, $result['gameList']);
        $this->assertEquals($goldenGame->id, $result['gameList']->first()->id);
    }

    #[Test]
    public function it_resets_player_count_and_returns_default_list_when_golden_list_is_empty(): void
    {
        $user = User::factory()->create();
        $boardGame = BoardGame::factory()->create();

        $player = BoardGamePlayer::factory()->reachedGoldenThreshold()->create([
            'user_id' => $user->id,
            'board_game_id' => $boardGame->id,
        ]);

        $boardGame->settings()->create(['code' => 'rerolled_own_game_count_for_rerolled_list', 'value' => 2]);
        $boardGame->settings()->create(['code' => 'rerolled_game_count_for_gold_list', 'value' => 3]);

        $defaultGame = BoardGameGameList::factory()->create([
            'board_game_id' => $boardGame->id,
            'list_type' => null,
        ]);

        $conditionData = [
            'boardGame' => $boardGame,
            'player' => $player,
            'user' => $user,
        ];

        $result = $this->service->getFilteredGameList(null, $conditionData);

        $this->assertEquals(0, $player->fresh()->rerolled_game_count);
        $this->assertEquals('default', $result['listType']);
        $this->assertCount(1, $result['gameList']);
        $this->assertEquals($defaultGame->id, $result['gameList']->first()->id);
    }
}
