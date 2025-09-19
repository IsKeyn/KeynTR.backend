<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDropChanceToBgItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bg_items', function (Blueprint $table) {
            $table->integer('drop_chance')->default(10)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bg_items', function (Blueprint $table) {
            Schema::dropIfExists('drop_chance');
        });
    }
}
