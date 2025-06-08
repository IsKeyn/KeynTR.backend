<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTimerIdToBoardGamePlayerTimersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_game_player_timers', function (Blueprint $table) {
            $table->foreignId('timer_id')->after('id')->nullable();
            $table->dropColumn('user_id');
            $table->dropColumn('board_game_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('board_game_player_timers', function (Blueprint $table) {
            $table->dropColumn('timer_id');
            $table->foreignId('user_id')->nullable();
            $table->foreignId('board_game_id')->nullable();
        });
    }
}
