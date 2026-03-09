<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the student absences table
     */
    public function up(): void
    {
        if (!Schema::hasTable('student_absences')) {
            Schema::create('student_absences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
                $table->date('date');
                $table->enum('status', ['present', 'absent', 'late'])->default('present');
                $table->text('justification_reason')->nullable();
                $table->string('justification_document')->nullable();
                $table->text('notes')->nullable();
                $table->string('recorded_by')->nullable();
                $table->timestamp('recorded_at')->nullable();
                $table->timestamps();
                
                // Indexes for better query performance
                $table->index(['student_id', 'classe_id']);
                $table->index(['classe_id', 'date']);
                $table->index('status');
                $table->index('date');
                
                // Composite unique index to prevent duplicate entries for same date
                $table->unique(['student_id', 'classe_id', 'date'], 'unique_student_absence_per_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_absences');
    }
};
