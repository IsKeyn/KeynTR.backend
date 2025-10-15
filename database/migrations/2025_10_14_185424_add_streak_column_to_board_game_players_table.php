<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStreakColumnToBoardGamePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_game_players', function (Blueprint $table) {
            $table->integer('streak')->default(0)->after('step_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('board_game_players', function (Blueprint $table) {
            $table->dropColumn('streak');
        });
    }
}
