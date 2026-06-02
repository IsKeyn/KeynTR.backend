<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoardGamePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_game_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('board_game_id')->nullable();
            $table->bigInteger('points')->default(0);
            $table->integer('item_roll_count')->default(0);
            $table->integer('step_count')->default(0);
            $table->integer('streak')->default(0);
            $table->integer('rerolled_own_game_count')->default(0);
            $table->boolean('active')->default(true);
            $table->text('not_active_reason')->nullable();
            $table->boolean('premium')->default(false);
            $table->integer('sort')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('board_game_players');
    }
}
