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
        Schema::table('games', function (Blueprint $table) {
            $table->index(['show_in_list', 'active']);
            $table->index('sort');
            $table->index(['show_in_list', 'active', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['show_in_list', 'active']);
            $table->dropIndex(['sort']);
            $table->dropIndex(['show_in_list', 'active', 'sort']);
        });
    }
};
