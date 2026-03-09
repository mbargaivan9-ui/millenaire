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
        // Add teacher_id column to schedules if it doesn't exist
        if (!Schema::hasColumn('schedules', 'teacher_id')) {
            Schema::table('schedules', function (Blueprint $table) {
                // Get teacher_id from class_subject_teachers table
                // We need to populate it from existing data
                $table->foreignId('teacher_id')
                    ->nullable()
                    ->after('class_subject_teacher_id')
                    ->constrained('teachers')
                    ->nullOnDelete();
            });

            // Populate teacher_id from class_subject_teachers data
            \Illuminate\Support\Facades\DB::statement(
                'UPDATE schedules 
                 SET teacher_id = (
                    SELECT teacher_id FROM class_subject_teachers 
                    WHERE class_subject_teachers.id = schedules.class_subject_teacher_id
                 )'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('schedules', 'teacher_id')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropConstrainedForeignId('teacher_id');
            });
        }
    }
};
