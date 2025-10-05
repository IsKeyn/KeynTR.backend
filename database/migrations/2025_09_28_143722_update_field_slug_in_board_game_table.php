<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFieldSlugInBoardGameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_games', function (Blueprint $table) {
            $table->unique('slug');
            $table->dropColumn('settings');
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
            $table->dropUnique(['slug']);
            $table->json('settings')->nullable();
        });
    }
}
