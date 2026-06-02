<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeleteToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('created_by')->after('spc_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('active')->default(true)->after('description');
            $table->integer('sort')->nullable()->after('description');
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
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('active');
            $table->dropColumn('sort');
            $table->dropSoftDeletes();
        });
    }
}
