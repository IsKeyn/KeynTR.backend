<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBgStatusEffectsBindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bg_status_effects_binds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_effect_id')->nullable()->constrained('status_effects')->nullOnDelete();
            $table->foreignId('board_game_id')->nullable()->constrained('board_games')->nullOnDelete();
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('bg_status_effects_binds');
    }
}
