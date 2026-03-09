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
        if (!Schema::hasTable('class_subject_teachers')) {
            Schema::create('class_subject_teachers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
                $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
                $table->string('academic_year')->default('2024-2025');
                $table->decimal('hours_per_week', 5, 2)->nullable();
                $table->string('room_location')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['class_id', 'subject_id', 'teacher_id', 'academic_year'], 'cst_unique_combo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_subject_teachers');
    }
};
