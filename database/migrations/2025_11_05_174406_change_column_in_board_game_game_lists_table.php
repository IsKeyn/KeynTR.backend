<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnInBoardGameGameListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_game_game_lists', function (Blueprint $table) {
            $table->string('difficult')->default(null)->nullable()->change();
            $table->string('game_completion_time')->default(null)->nullable()->change();
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
            $table->string('difficult')->default(0)->nullable(false)->change();
            $table->string('game_completion_time')->default(0)->nullable(false)->change();
        });
    }
}
