<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDifficultAndGctColumnToBoardGameGameListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_game_game_lists', function (Blueprint $table) {
            $table->bigInteger('game_completion_time')->default(0)->after('points');
            $table->integer('difficult')->default(0)->after('points');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('board_game_game_lists', function (Blueprint $table) {
            $table->dropColumn('game_completion_time');
            $table->dropColumn('difficult');
        });
    }
}
