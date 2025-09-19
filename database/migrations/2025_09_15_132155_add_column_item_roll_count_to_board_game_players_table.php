<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnItemRollCountToBoardGamePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_game_players', function (Blueprint $table) {
            $table->text('not_active_reason')->nullable()->after('points');
            $table->integer('item_roll_count')->default(0)->after('points');
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
            Schema::dropIfExists('item_roll_count');
            Schema::dropIfExists('not_active_reason');
        });
    }
}
