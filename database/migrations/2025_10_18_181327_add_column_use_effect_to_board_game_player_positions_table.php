<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnUseEffectToBoardGamePlayerPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_game_player_positions', function (Blueprint $table) {
            $table->boolean('has_use_effect')->after('board_game_id')->default(false);
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
            $table->dropColumn('has_use_effect');
        });
    }
}
