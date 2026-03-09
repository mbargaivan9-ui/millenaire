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
        // Add head_teacher_id column to classes if it doesn't exist
        if (!Schema::hasColumn('classes', 'head_teacher_id')) {
            Schema::table('classes', function (Blueprint $table) {
                // Reference the teachers table, not users
                $table->foreignId('head_teacher_id')
                    ->nullable()
                    ->after('capacity')
                    ->constrained('teachers')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('classes', 'head_teacher_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('head_teacher_id');
            });
        }
    }
};
