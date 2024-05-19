<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('code', 'slug');
            $table->renameColumn('title', 'name');
            $table->dropColumn('tags');
            $table->dropColumn('vote_up');
            $table->dropColumn('vote_down');
        });

        Schema::table('articles', function (Blueprint $table) {
                $table->string('slug')->unique()->after('name')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
