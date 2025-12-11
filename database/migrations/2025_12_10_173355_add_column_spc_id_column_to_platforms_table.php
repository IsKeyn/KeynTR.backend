<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnSpcIdColumnToPlatformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gaming_platforms', function (Blueprint $table) {
            $table->string('spc_id')->after('release_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gaming_platforms', function (Blueprint $table) {
            $table->dropColumn('spc_id');
        });
    }
}
