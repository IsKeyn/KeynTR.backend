<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\User\Notification;
use App\Services\BoardGame\LogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetStatusEffectOnPlayerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'board-game:set-status-effect-on-player-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Вешает статус эффект на случайного игрока';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $seSlug = 'syr-ksyrsyka';

        // Проверяем существует ли и активен нужный статус эффект
        $statusEffect = StatusEffect::query()
            ->where('slug', $seSlug)
            ->active()
            ->with(['statusEffectBinds'])
            ->first();

        if (!$statusEffect) {
            $message = "Статус эффект со Slug: {$seSlug} не существует или не активен";
            $this->error($message);
            Log::channel('statusEffects')->warning($message);
            return self::FAILURE;
        }

        $boardGameList = BoardGame::query()
            ->active()
            ->open()
            ->with(['settings'])
            ->get();

        foreach ($boardGameList as $boardGame) {
            try {
                $this->processBoardGame($boardGame, $statusEffect);
            } catch (\Exception $e) {
                Log::channel('statusEffects')->error(
                    "Ошибка обработки игры {$boardGame->id}: {$e->getMessage()}",
                    ['exception' => $e]
                );
                $this->error("Ошибка обработки игры {$boardGame->name}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Обрабатывает одну настольную игру
     *
     * @param BoardGame $boardGame Настольная игра
     * @param StatusEffect $statusEffect Статус эффект
     */
    private function processBoardGame(
        BoardGame $boardGame,
        StatusEffect $statusEffect
    ): void
    {
        if ($boardGame->status !== BoardGame::OPEN_STATUS) {
            $message = "Настольная игра {$boardGame->name} завершена";

            $this->line($message);
            Log::channel('statusEffects')->debug(
                $message,
                ['board_game_id' => $boardGame->id]
            );
            return;
        }

        // Ищем id привязки для этой игры
        $bind = $statusEffect->statusEffectBinds->firstWhere('board_game_id', $boardGame->id);

        if (!$bind) {
            $message = "Нет привязки для игры {$boardGame->id}";

            $this->line($message);
            Log::channel('statusEffects')->debug(
                "Нет привязки для игры {$boardGame->id}",
                ['board_game_id' => $boardGame->id]
            );
            return;
        }

        $bindId = $bind->id;

        // Массовое снятие статус-эффектов с текущих игроков
        $updatedCount = PlayerStatusEffect::query()
            ->where('board_game_id', $boardGame->id)
            ->where('status_effect_bind_id', $bindId)
            ->where('active', true)
            ->update(['active' => false]);

        if ($updatedCount > 0) {
            Log::channel('statusEffects')->info(
                "Снято статус-эффектов: {$updatedCount}",
                ['board_game_id' => $boardGame->id, 'bind_id' => $bindId]
            );
        }

        // Ищем случайного игрока
        $randomPlayer = $this->getRandomPlayer($boardGame, $bindId);

        if (!$randomPlayer) {
            Log::channel('statusEffects')->warning(
                "Не удалось найти подходящего игрока для игры {$boardGame->id}"
            );
            return;
        }

        // Вешаем на игрока статус эффект в транзакции
        DB::transaction(function () use ($randomPlayer, $boardGame, $bindId, $statusEffect) {
            $playerStatusEffect = PlayerStatusEffect::create([
                'user_id' => $randomPlayer->user_id,
                'bg_player_id' => $randomPlayer->id,
                'board_game_id' => $boardGame->id,
                'status_effect_bind_id' => $bindId,
                'active' => true,
            ]);

            // Логи
            $userName = $randomPlayer->user->public_name ?? $randomPlayer->user->name;

            $fields = [
                'user_id' => $randomPlayer->user_id,
                'created_by' => $randomPlayer->user_id,
                'message' => "Вы получили статус эффект {$statusEffect->name}, воспользуйтесь его преимуществами, пока он не ущел другому игроку",
                'entity_type' => $boardGame::class,
                'entity_id' => $boardGame->id,
            ];

            Notification::create($fields);

            $message = "получил статус эффект {$statusEffect->name}";

            $this->line("Статус эффект {$statusEffect->name} получил {$userName}");

            Log::channel('statusEffects')->info(
                'Статус эффект получен',
                [
                    'user_id' => $randomPlayer->user_id,
                    'user_name' => $userName,
                    'board_game_id' => $boardGame->id,
                    'board_game_name' => $boardGame->name,
                    'player_id' => $randomPlayer->id,
                    'status_effect_id' => $statusEffect->id,
                ]
            );

            LogService::addLog(
                $randomPlayer->user_id,
                $boardGame->id,
                $message,
                $randomPlayer->id
            );
        });
    }

    /**
     * Ищем случайного игрока в настолькой игре/ивенте, исключая тех, кто владел статус эффектом
     *
     * @param BoardGame $boardGame Настольная игра / Ивент
     * @param int $bindId ID привязки предмета к настольной игре (StatusEffectBind)
     * @param array $excludingPlayers Игроки, которых исключаем из выборки
     * @param int $recursionDepth Глубина рекурсии для предотвращения бесконечного цикла
     * @param int $maxLastPlayers Количество игроков, которые получали эффект ранее
     * @return BoardGamePlayer|null
     */
    private function getRandomPlayer(
        BoardGame $boardGame,
        int $bindId,
        array $excludingPlayers = [],
        int $recursionDepth = 0,
        int $maxLastPlayers = null
    ): ?BoardGamePlayer {
        if ($recursionDepth > 1000) {
            Log::warning('Превышена глубина рекурсии в getRandomPlayer', [
                'board_game_id' => $boardGame->id,
                'bind_id' => $bindId,
            ]);
            return null;
        }

        if ($maxLastPlayers === null) {
            $lastPlayersWithEveryDayStatusEffectSetting = $boardGame
                ->settings
                ->where('code', '=', 'last_players_with_every_day_status_effect')
                ->first();


            $maxLastPlayers = $lastPlayersWithEveryDayStatusEffectSetting ? $lastPlayersWithEveryDayStatusEffectSetting->value : 3;
        }

        // Получаем последних игроков со статус-эффектом
        $lastPlayersWithSe = PlayerStatusEffect::query()
            ->findByBoardGame($boardGame->id)
            ->where('status_effect_bind_id', $bindId)
            ->with(['player:id'])
            ->orderByDesc('id')
            ->limit($maxLastPlayers)
            ->pluck('bg_player_id')
            ->toArray();

        // Объединяем исключаемых игроков
        $excludedIds = array_unique(array_merge($lastPlayersWithSe, $excludingPlayers));

        // Ищем активного игрока через запрос к БД
        $player = BoardGamePlayer::query()
            ->findByBoardGame($boardGame->id)
            ->active()
            ->whereNotIn('id', $excludedIds)
            ->with('user')
            ->inRandomOrder()
            ->first();

        // Если игрок не найден - возвращаем null
        if (!$player) {
            return $this->getRandomPlayer(
                $boardGame,
                $bindId,
                [],
                $recursionDepth + 1,
                $maxLastPlayers !== 0 ? $maxLastPlayers - 1 : 0
            );
        }

        // Проверяем условие для рекурсивного поиска
        $eventType = $boardGame->settings->where('code', 'event_type')->first();

        if ($eventType &&
            $eventType->value === 'board-last-cell' &&
            $player->finishBoard) {

            // Рекурсивно ищем другого игрока
            $excludingPlayers[] = $player->id;
            return $this->getRandomPlayer(
                $boardGame,
                $bindId,
                $excludingPlayers,
                $recursionDepth + 1
            );
        }

        return $player;
    }
}
