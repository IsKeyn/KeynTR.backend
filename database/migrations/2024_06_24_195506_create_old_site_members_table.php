<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOldSiteMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('old_site_members', function (Blueprint $table) {
            $table->id();
            $table->json('names');
            $table->string('email');
            $table->integer('type')->nullable();
            $table->boolean('was_handled')->default(false);
            $table->timestamp('first_message_date')->nullable();
            $table->integer('message_count')->nullable();
            $table->integer('score_count')->nullable();
            $table->integer('score_percent')->nullable();
            $table->integer('best_of')->nullable();
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
        Schema::dropIfExists('old_site_members');
    }
}
