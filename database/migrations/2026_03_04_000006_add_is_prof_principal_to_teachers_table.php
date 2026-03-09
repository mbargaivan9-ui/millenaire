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
        // Add is_prof_principal column to teachers if it doesn't exist
        if (!Schema::hasColumn('teachers', 'is_prof_principal')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->boolean('is_prof_principal')->default(false)->after('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('teachers', 'is_prof_principal')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropColumn('is_prof_principal');
            });
        }
    }
};
