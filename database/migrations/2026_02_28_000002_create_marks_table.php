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
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();
            $table->foreignId('class_subject_teacher_id')
                ->nullable()
                ->constrained('class_subject_teachers')
                ->nullOnDelete();
            $table->float('score')->default(0); // Score: 0-20
            $table->integer('term')->default(1); // Term/Trimestre
            $table->integer('sequence')->default(1); // Sequence/Test number
            $table->text('comment')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index('student_id');
            $table->index('subject_id');
            $table->index('class_subject_teacher_id');
            $table->index(['term', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
