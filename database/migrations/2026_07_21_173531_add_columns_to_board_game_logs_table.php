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
        Schema::table('board_game_logs', function (Blueprint $table) {
            $table->string('entity_type')->nullable()->after('board_game_id');
            $table->integer('entity_id')->nullable()->after('board_game_id');
            $table->boolean('important')->default(false)->after('board_game_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_game_logs', function (Blueprint $table) {
            $table->dropColumn('entity_type');
            $table->dropColumn('entity_id');
            $table->dropColumn('important');
        });
    }
};
