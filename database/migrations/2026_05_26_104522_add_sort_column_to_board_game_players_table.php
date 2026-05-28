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
            $table->integer('sort')->nullable()->after('not_active_reason');
            $table->boolean('premium')->default(false)->after('not_active_reason');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_game_players', function (Blueprint $table) {
            $table->dropColumn('sort');
            $table->dropColumn('premium');
            $table->dropSoftDeletes();
        });
    }
};
