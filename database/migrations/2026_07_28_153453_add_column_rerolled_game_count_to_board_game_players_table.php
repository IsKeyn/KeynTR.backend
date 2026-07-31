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
            $table
                ->unsignedTinyInteger('rerolled_game_count')
                ->default(0)
                ->after('streak')
                ->comment('Количество рерольнутых игр подряд');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_game_players', function (Blueprint $table) {
            $table
                ->dropColumn('rerolled_game_count');
        });
    }
};
