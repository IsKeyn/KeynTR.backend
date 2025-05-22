<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToBoardGameGameListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('board_game_game_lists', function (Blueprint $table) {
            $table->foreignId('added_by')->after('board_game_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('active')->after('board_game_id')->default(1);
            $table->bigInteger('points')->after('board_game_id')->nullable();
            $table->text('description')->after('board_game_id')->nullable();
            $table->foreignId('gaming_platform_id')->after('board_game_id')->nullable();
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
            $table->dropForeign(['added_by']);
            $table->dropColumn('added_by');
            $table->dropColumn('active');
            $table->dropColumn('points');
            $table->dropColumn('description');
            $table->dropColumn('gaming_platform_id');
        });
    }
}
