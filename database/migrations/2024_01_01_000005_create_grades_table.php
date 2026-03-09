<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('assignments')->nullOnDelete();
            $table->decimal('homework', 5, 2)->nullable(); // Devoirs
            $table->decimal('classwork', 5, 2)->nullable(); // Travaux en classe
            $table->decimal('exam', 5, 2)->nullable(); // Examen
            $table->decimal('average', 5, 2)->nullable();
            $table->string('status')->nullable(); // Bon, Moyen, Faible
            $table->text('comment')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
