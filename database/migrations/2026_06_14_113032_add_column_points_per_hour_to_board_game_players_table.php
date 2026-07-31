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
        Schema::table('board_game_players', function (Blueprint $table) {
            $table->integer('points_per_hour')
                ->default(0)
                ->comment('Очков в час')
                ->after('points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_game_players', function (Blueprint $table) {
            $table->dropColumn('points_per_hour');
        });
    }
};
