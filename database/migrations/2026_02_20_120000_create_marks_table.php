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
        if (!Schema::hasTable('marks')) {
            Schema::create('marks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained()->onDelete('cascade');
                $table->foreignId('class_subject_teacher_id')->constrained('class_subject_teacher')->onDelete('cascade');
                $table->enum('term', ['1', '2', '3'])->default('1');
                $table->integer('sequence')->default(1);
                $table->enum('evaluation_type', ['quiz', 'test', 'exam', 'homework', 'participation'])->default('test');
                $table->decimal('score', 5, 2)->nullable();
                $table->decimal('coefficient', 3, 1)->default(1.0);
                $table->text('comment')->nullable();
                $table->unsignedBigInteger('recorded_by')->nullable();
                $table->dateTime('recorded_at')->nullable();
                $table->timestamps();
                
                $table->index(['student_id', 'class_subject_teacher_id']);
                $table->index(['term', 'sequence']);
                $table->index('recorded_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
