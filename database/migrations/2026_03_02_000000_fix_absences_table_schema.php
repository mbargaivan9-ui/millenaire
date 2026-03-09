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
        Schema::table('absences', function (Blueprint $table) {
            // Drop old `type` column if it exists
            if (Schema::hasColumn('absences', 'type')) {
                $table->dropColumn('type');
            }

            // Add status column if it doesn't exist
            if (!Schema::hasColumn('absences', 'status')) {
                $table->string('status')->default('absent')->comment('present | absent | late | excused');
            }

            // Add class_id if it doesn't exist
            if (!Schema::hasColumn('absences', 'class_id')) {
                $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            }

            // Add teacher_id if it doesn't exist
            if (!Schema::hasColumn('absences', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            }

            // Add subject_id if it doesn't exist
            if (!Schema::hasColumn('absences', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            }

            // Add justified column if it doesn't exist
            if (!Schema::hasColumn('absences', 'justified')) {
                $table->boolean('justified')->default(false);
            }

            // Add justification column if it doesn't exist
            if (!Schema::hasColumn('absences', 'justification')) {
                $table->text('justification')->nullable();
            }

            // Add notified_at column if it doesn't exist
            if (!Schema::hasColumn('absences', 'notified_at')) {
                $table->timestamp('notified_at')->nullable();
            }

            // Drop reason and comment columns if they exist and are not needed
            if (Schema::hasColumn('absences', 'reason')) {
                $table->dropColumn('reason');
            }
            if (Schema::hasColumn('absences', 'comment')) {
                $table->dropColumn('comment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            // Restore old structure
            if (Schema::hasColumn('absences', 'status')) {
                $table->dropColumn('status');
            }
            if (!Schema::hasColumn('absences', 'type')) {
                $table->string('type')->default('absence');
            }
            if (Schema::hasColumn('absences', 'class_id')) {
                $table->dropColumnIf('class_id');
            }
            if (Schema::hasColumn('absences', 'teacher_id')) {
                $table->dropColumnIf('teacher_id');
            }
            if (Schema::hasColumn('absences', 'subject_id')) {
                $table->dropColumnIf('subject_id');
            }
            if (Schema::hasColumn('absences', 'justified')) {
                $table->dropColumn('justified');
            }
            if (Schema::hasColumn('absences', 'justification')) {
                $table->dropColumn('justification');
            }
            if (Schema::hasColumn('absences', 'notified_at')) {
                $table->dropColumn('notified_at');
            }
        });
    }
};
