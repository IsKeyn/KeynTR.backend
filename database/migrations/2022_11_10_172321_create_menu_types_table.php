<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->boolean('group');
            $table->string('group_name')->nullable();
            $table->string('group_icon')->nullable();
            $table->integer('menu_type_bind_id')->nullable();
            $table->string('menu_type_bind_type')->nullable();
            $table->integer('sort')->nullable();
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
        Schema::dropIfExists('menu_types');
    }
}
