<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusEffectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_effects', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->default(0);
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->json('actions')->nullable();
            $table->foreignId('board_game_id')->nullable();
            $table->boolean('debuff')->default(false);
            $table->softDeletes();
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
        Schema::dropIfExists('status_effects');
    }
}
