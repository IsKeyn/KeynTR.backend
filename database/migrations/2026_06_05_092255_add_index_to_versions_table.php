<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            Schema::table('versions', function (Blueprint $table) {
                $table->index(['created_at', 'sort'], 'idx_versions_created_sort');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->dropIndex('idx_versions_created_sort');
        });
    }
};
