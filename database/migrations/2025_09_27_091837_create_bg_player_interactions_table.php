<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBgPlayerInteractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bg_player_interactions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('status')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('board_game_id')->nullable();
            $table->foreignId('bg_player_id')->nullable();
            $table->foreignId('with_player')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('entity_id')->nullable();
            $table->string('entity_type')->nullable();
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('bg_player_interactions');
    }
}
