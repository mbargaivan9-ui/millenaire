<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des absences des élèves
     */
    public function up(): void
    {
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->foreignId('class_subject_teacher_id')->constrained('class_subject_teacher')->onDelete('cascade');
                $table->date('date');
                $table->enum('status', ['present', 'absent', 'late', 'justified'])->default('present');
                $table->text('reason')->nullable();
                $table->string('recorded_by')->nullable();
                $table->timestamp('recorded_at')->nullable();
                $table->timestamps();
                $table->index(['student_id', 'date']);
                $table->unique(['student_id', 'class_subject_teacher_id', 'date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
