<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewDatesColumnsToBoardGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_games', function (Blueprint $table) {
            $table->dateTime('ended_at')->nullable()->after('active');
            $table->dateTime('started_at')->nullable()->after('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('board_games', function (Blueprint $table) {
            $table->dropColumn('ended_at');
            $table->dropColumn('started_at');
        });
    }
}
