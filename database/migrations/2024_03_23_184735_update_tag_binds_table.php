<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTagBindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tag_binds', function (Blueprint $table) {
            $table->renameColumn('tag_bind_id', 'tag_binds_id');
            $table->renameColumn('tag_bind_type', 'tag_binds_type');
            $table->string('type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tag_binds', function (Blueprint $table) {
            $table->renameColumn('tag_binds_id', 'tag_bind_id');
            $table->renameColumn('tag_binds_type', 'tag_bind_type');
            $table->string('type')->change();
        });
    }
}
