<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoardGameGameListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('board_game_game_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->nullable();
            $table->foreignId('board_game_id')->nullable();
            $table->foreignId('gaming_platform_id')->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('points')->nullable();
            $table->bigInteger('difficult')->nullable();
            $table->bigInteger('game_completion_time')->nullable();
            $table->boolean('coop')->default(0);
            $table->integer('list_type')->nullable();
            $table->integer('sort')->nullable();
            $table->boolean('active')->default(1);
            $table->text('source')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
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
        Schema::dropIfExists('board_game_game_lists');
    }
}
