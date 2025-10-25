<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnListTypeToBoardGameGameLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_game_game_lists', function (Blueprint $table) {
            $table->integer('list_type')->nullable()->after('coop');
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
            $table->dropColumn('list_type');
        });
    }
}
