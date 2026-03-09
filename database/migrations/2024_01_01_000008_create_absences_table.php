<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->string('status')->default('absent')->comment('present | absent | late | excused');
            $table->boolean('justified')->default(false);
            $table->text('justification')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            
            $table->index('student_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
