<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des absences des enseignants
     */
    public function up(): void
    {
        if (!Schema::hasTable('teacher_absences')) {
            Schema::create('teacher_absences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
                $table->date('date');
                $table->enum('status', ['present', 'absent', 'late', 'justified', 'medical_leave', 'authorized_leave'])->default('present');
                $table->text('reason')->nullable();
                $table->text('justification_document')->nullable();
                $table->string('recorded_by')->nullable();
                $table->timestamp('recorded_at')->nullable();
                $table->boolean('is_approved')->default(false);
                $table->string('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                
                $table->index(['teacher_id', 'date']);
                $table->index('status');
                $table->index('is_approved');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_absences');
    }
};
