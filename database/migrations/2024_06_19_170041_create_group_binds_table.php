<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupBindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_binds', function (Blueprint $table) {
            $table->id();
            $table->integer('group_id');
            $table->integer('group_bind_id');
            $table->string('group_bind_type');
            $table->integer('type')->nullable();
            $table->integer('first_b_id')->nullable();
            $table->string('first_b_type')->nullable();
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
        Schema::dropIfExists('group_binds');
    }
}
