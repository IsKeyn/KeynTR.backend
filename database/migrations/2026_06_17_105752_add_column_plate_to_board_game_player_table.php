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
            $table->unsignedSmallInteger('place')->nullable()->default(null)->after('points_per_hour');
            $table->index('place');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_game_players', function (Blueprint $table) {
            $table->dropColumn('place');
        });
    }
};
