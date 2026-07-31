<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerInteractions;
use App\Models\BoardGame\PlayerStatusEffect;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Collection;

class FillBoardGamePlayerIdCommand extends Command
{
    protected $signature = 'app:fill-board-game-player-id-command {--chunk=1000} {--sleep=0 : Пауза между чанками в миллисекундах}';

    protected $description = 'Заполняет bg_player_id по user_id/created_by и board_game_id для строк с bg_player_id = 0';

    public function handle(): int
    {
        $targets = [
            PlayerStatusEffect::class => 'user_id',
            BoardGameInventory::class => 'user_id',
            PlayerGame::class => 'user_id',
            PlayerInteractions::class => 'created_by',
            BoardGamePlayerPosition::class => 'user_id',
        ];

        $playerModel = new BoardGamePlayer();
        $playerTable = $playerModel->getTable();

        $playerDeletedColumn = 'deleted_at';
        $playerHasDeletedAt = $playerModel
            ->getConnection()
            ->getSchemaBuilder()
            ->hasColumn($playerTable, $playerDeletedColumn);

        foreach ($targets as $modelClass => $userColumn) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = new $modelClass();

            $table = $model->getTable();
            $connection = $model->getConnection();
            $schema = $connection->getSchemaBuilder();

            if (! $schema->hasColumn($table, 'bg_player_id') || ! $schema->hasColumn($table, $userColumn)) {
                $this->warn("Пропуск таблицы {$table}: нет колонки bg_player_id или {$userColumn}.");
                continue;
            }

            $updatedAtColumn = $model->getUpdatedAtColumn();
            $hasUpdatedAt = $updatedAtColumn && $schema->hasColumn($table, $updatedAtColumn);

            $this->info("Обработка таблицы: {$table}");

            $updatedTotal = 0;
            $skippedTotal = 0;

            $query = $modelClass::query()
                ->whereNotNull($userColumn)
                ->whereNotNull('board_game_id')
                ->where('bg_player_id', 0); // Ищем строки, где bg_player_id = 0

            $this->addPlayerExists(
                $query,
                $table,
                $playerTable,
                $userColumn,
                $playerHasDeletedAt,
                $playerDeletedColumn
            );

            $query
                ->select('id')
                ->toBase()
                ->orderBy('id')
                ->chunkById(
                    (int) $this->option('chunk'),
                    function (Collection $rows) use (
                        $connection,
                        $table,
                        $playerTable,
                        $userColumn,
                        $hasUpdatedAt,
                        $updatedAtColumn,
                        $playerHasDeletedAt,
                        $playerDeletedColumn,
                        &$updatedTotal,
                        &$skippedTotal
                    ): void {
                        $ids = $rows->pluck('id')->all();

                        if ($ids === []) {
                            return;
                        }

                        $updated = $this->updateChunk(
                            $connection,
                            $table,
                            $playerTable,
                            $userColumn,
                            $ids,
                            $connection->getQueryGrammar(),
                            $hasUpdatedAt,
                            (string) $updatedAtColumn,
                            $playerHasDeletedAt,
                            $playerDeletedColumn
                        );

                        $updatedTotal += $updated;
                        $skippedTotal += count($ids) - $updated;

                        $sleep = (int) $this->option('sleep');

                        if ($sleep > 0) {
                            usleep($sleep * 1000);
                        }
                    },
                    'id',
                    'id'
                );

            $this->info("{$table}: обновлено {$updatedTotal}, пропущено {$skippedTotal}.");
        }

        return self::SUCCESS;
    }

    private function updateChunk(
        ConnectionInterface $connection,
        string $table,
        string $playerTable,
        string $userColumn,
        array $ids,
        Grammar $grammar,
        bool $hasUpdatedAt,
        string $updatedAtColumn,
        bool $playerHasDeletedAt,
        string $playerDeletedColumn
    ): int {
        $query = $connection
            ->table($table)
            ->whereIn('id', $ids)
            ->where('bg_player_id', 0); // Дополнительная проверка

        $this->addPlayerExists(
            $query,
            $table,
            $playerTable,
            $userColumn,
            $playerHasDeletedAt,
            $playerDeletedColumn
        );

        $subQuery = sprintf(
            'select min(%s) from %s where %s = %s and %s = %s',
            $grammar->wrap($playerTable . '.id'),
            $grammar->wrap($playerTable),
            $grammar->wrap($playerTable . '.user_id'),
            $grammar->wrap($table . '.' . $userColumn),
            $grammar->wrap($playerTable . '.board_game_id'),
            $grammar->wrap($table . '.board_game_id')
        );

        if ($playerHasDeletedAt) {
            $subQuery .= ' and ' . $grammar->wrap($playerTable . '.' . $playerDeletedColumn) . ' is null';
        }

        $values = [
            'bg_player_id' => $connection->raw('(' . $subQuery . ')'),
        ];

        if ($hasUpdatedAt) {
            $values[$updatedAtColumn] = $connection->raw($grammar->wrap($updatedAtColumn));
        }

        return $query->update($values);
    }

    private function addPlayerExists(
        $query,
        string $table,
        string $playerTable,
        string $userColumn,
        bool $playerHasDeletedAt,
        string $playerDeletedColumn
    ): void {
        $query->whereExists(function ($q) use (
            $table,
            $playerTable,
            $userColumn,
            $playerHasDeletedAt,
            $playerDeletedColumn
        ): void {
            $q->selectRaw('1')
                ->from($playerTable)
                ->whereColumn($playerTable . '.user_id', $table . '.' . $userColumn)
                ->whereColumn($playerTable . '.board_game_id', $table . '.board_game_id');

            if ($playerHasDeletedAt) {
                $q->whereNull($playerTable . '.' . $playerDeletedColumn);
            }
        });
    }
}
