<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bg_shop_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bg_player_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('board_game_id')->nullable();
            $table->integer('status')->nullable();
            $table->foreignId('bought_by_player_id')->nullable();
            $table->string('entity_type')->nullable();
            $table->integer('entity_id')->nullable();
            $table->integer('sort')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bg_shop_items');
    }
};
