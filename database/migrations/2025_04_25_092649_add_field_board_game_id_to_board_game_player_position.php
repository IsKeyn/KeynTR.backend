<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldBoardGameIdToBoardGamePlayerPosition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_game_player_positions', function (Blueprint $table) {
            $table->foreignId('board_game_id')->nullable()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('board_game_player_positions', function (Blueprint $table) {
            $table->dropColumn('board_game_id');
        });
    }
}
