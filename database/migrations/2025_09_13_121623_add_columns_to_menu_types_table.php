<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToMenuTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('menu_types', function (Blueprint $table) {
            $table->string('group_icon')->nullable()->after('group');
            $table->boolean('active')->default(true)->after('menu_type_bind_type');
            $table->integer('sort')->nullable()->after('menu_type_bind_type');
            $table->integer('menu_type_bind_id')->nullable()->change();
            $table->string('menu_type_bind_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menu_types', function (Blueprint $table) {
            $table->dropColumn('group_name');
            $table->dropColumn('group_icon');
            $table->dropColumn('active');
            $table->dropColumn('sort');
//            $table->integer('menu_type_bind_id')->nullable(false)->change();
//            $table->string('menu_type_bind_type')->nullable(false)->change();
        });
    }
}
