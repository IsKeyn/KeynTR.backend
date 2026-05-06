<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeleteToGamingPlatformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gaming_platforms', function (Blueprint $table) {
            $table->foreignId('created_by')->after('sort')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('active')->default(true)->after('sort');
            $table->softDeletes();
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
            $table->dropColumn('created_by');
            $table->dropColumn('active');
            $table->dropSoftDeletes();
        });
    }
}
