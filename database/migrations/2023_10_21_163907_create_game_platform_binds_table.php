<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamePlatformBindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gaming_platform_binds', function (Blueprint $table) {
            $table->id();
            $table->integer('gaming_platform_id');
            $table->integer('gaming_platform_bind_id');
            $table->string('gaming_platform_bind_type');
            $table->string('additional_info')->nullable();
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
        Schema::dropIfExists('gaming_platform_binds');
    }
}
