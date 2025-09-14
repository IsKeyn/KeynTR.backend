<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnShortDescriptionToBgItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bg_items', function (Blueprint $table) {
//            $table->text('short_description')->after('slug')->nullable();
//            $table->renameColumn('description', 'full_description');
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
//            $table->dropColumn('short_description');
//            $table->renameColumn('full_description', 'description');
        });
    }
}
