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
        Schema::table('board_game_player_positions', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('has_use_effect');
            $table->integer('sort')->nullable()->after('has_use_effect');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_game_player_positions', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropColumn('sort');
            $table->dropSoftDeletes();
        });
    }
};
