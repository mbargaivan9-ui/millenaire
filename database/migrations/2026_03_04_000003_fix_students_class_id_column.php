<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if classe_id exists, if not add it
        if (!Schema::hasColumn('students', 'classe_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('classe_id')
                    ->nullable()
                    ->constrained('classes', 'id')
                    ->onDelete('set null');
            });
        }

        // Drop class_id if it exists (old naming)
        if (Schema::hasColumn('students', 'class_id')) {
            try {
                Schema::table('students', function (Blueprint $table) {
                    // First drop the foreign key constraint
                    $table->dropForeign(['class_id']);
                });
            } catch (QueryException $e) {
                // Constraint doesn't exist, continue
            }

            try {
                Schema::table('students', function (Blueprint $table) {
                    $table->dropColumn('class_id');
                });
            } catch (QueryException $e) {
                // Column doesn't exist, continue
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('students', 'classe_id')) {
            Schema::table('students', function (Blueprint $table) {
                try {
                    $table->dropForeign(['classe_id']);
                } catch (QueryException $e) {
                    // Constraint doesn't exist
                }
            });
        }
    }
};
