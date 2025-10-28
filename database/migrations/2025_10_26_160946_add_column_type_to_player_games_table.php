<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTypeToPlayerGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('player_games', function (Blueprint $table) {
            $table->foreignId('from_user_id')->after('board_game_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('type')->nullable()->after('board_game_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('player_games', function (Blueprint $table) {
            $table->dropColumn('from_user_id');
            $table->dropColumn('type');
        });
    }
}
