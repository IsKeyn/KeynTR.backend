<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteColumnsFromBgItemsBindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bg_items_binds', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('slug');
            $table->dropColumn('description');
            $table->dropColumn('actions');
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bg_items_binds', function (Blueprint $table) {
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->json('actions')->nullable();
            $table->bigInteger('type')->default(0);
        });
    }
}
