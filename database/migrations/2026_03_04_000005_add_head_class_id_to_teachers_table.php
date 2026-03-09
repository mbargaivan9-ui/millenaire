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
        // Add head_class_id column to teachers if it doesn't exist
        if (!Schema::hasColumn('teachers', 'head_class_id')) {
            Schema::table('teachers', function (Blueprint $table) {
                // Reference the classes table
                $table->foreignId('head_class_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('classes', 'id')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('teachers', 'head_class_id')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('head_class_id');
            });
        }
    }
};
