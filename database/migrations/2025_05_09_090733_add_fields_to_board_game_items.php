<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToBoardGameItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_game_items', function (Blueprint $table) {
            $table->json('actions')->after('description')->default(1);
        });

        Schema::table('board_game_items', function (Blueprint $table) {
            $table->bigInteger('type')->default(1)->after('actions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('board_game_items', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
