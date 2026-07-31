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
        Schema::table('bg_player_interactions', function (Blueprint $table) {
            $table->foreignId('bg_player_id')->nullable()->after('board_game_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bg_player_interactions', function (Blueprint $table) {
            $table->dropColumn('bg_player_id');
        });
    }
};
