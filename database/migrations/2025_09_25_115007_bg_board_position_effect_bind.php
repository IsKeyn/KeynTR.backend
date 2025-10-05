<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BgBoardPositionEffectBind extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bg_board_position_effect_binds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_effect_id')->nullable()->constrained('bg_board_position_effects')->nullOnDelete();
            $table->foreignId('board_game_id')->nullable()->constrained('board_games')->nullOnDelete();
            $table->integer('position');
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bg_board_position_effect_binds');
    }
}
