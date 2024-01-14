<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugColumnToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('slug')->after('name')->nullable();
        });

        Schema::table('gaming_platforms', function (Blueprint $table) {
            $table->string('slug')->after('name')->nullable();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug')->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('gaming_platforms', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
