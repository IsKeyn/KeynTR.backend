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
        Schema::table('board_game_inventories', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('use_result');
            $table->integer('sort')->nullable()->after('use_result');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_game_inventories', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropColumn('sort');
            $table->dropSoftDeletes();
        });
    }
};
