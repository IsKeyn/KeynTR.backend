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
        Schema::table('player_games', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('time');
            $table->integer('sort')->nullable()->after('time');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_games', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropColumn('sort');
            $table->dropSoftDeletes();
        });
    }
};
