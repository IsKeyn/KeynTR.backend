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
        Schema::create('bg_add_games', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('bg_player_id')
                ->constrained('board_game_players')
                ->cascadeOnDelete(); // При удалении игрока, запись тоже удалится
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete(); // При удалении пользователя поле станет null
            $table->foreignId('board_game_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name', 1000)->nullable();
            $table->foreignId('gaming_platform_id')->nullable()
                ->constrained('gaming_platforms');
            $table->boolean('coop')->default(false);
            $table->string('game_completion_time')->nullable();
            $table->tinyInteger('difficulty')->nullable();
            $table->text('description')->nullable();
            $table->text('comment_for_moderator')->nullable();
            $table->text('moderator_comment')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bg_add_games');
    }
};
