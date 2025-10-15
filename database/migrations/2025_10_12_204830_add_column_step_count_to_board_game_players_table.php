<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStepCountToBoardGamePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_game_players', function (Blueprint $table) {
            $table->integer('step_count')->default(0)->after('item_roll_count');
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
            $table->dropColumn('step_count');
        });
    }
}
