<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bg_board_cell_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('player_id')
                ->nullable()
                ->comment('ID игрока, из настольной игры\\ивента')
                ->constrained('board_game_players')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->comment('ID пользователя')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('board_game_id')
                ->nullable()
                ->index()
                ->comment('ID настольной игры\\ивента')
                ->constrained('board_games')
                ->nullOnDelete();

            $table->foreignId('board_position_effects_id')
                ->nullable()
                ->index()
                ->comment('ID эффекта игрового поля игры\\ивента')
                ->constrained('bg_board_position_effects')
                ->nullOnDelete();

            $table->foreignId('comment_id')
                ->comment('ID комментария')
                ->constrained('comments')
                ->cascadeOnDelete();

            $table->unsignedInteger('completion_time_seconds')
                ->default(0)
                ->comment('Время затраченное на ячейку в секундах');

            $table->integer('sort')->default(0)->index();
            $table->boolean('active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bg_board_cell_reviews');
    }
};
