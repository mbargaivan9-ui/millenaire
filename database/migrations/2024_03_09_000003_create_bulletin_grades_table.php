<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Table: bulletin_grades
     * 
     * Saisie des notes par matière pour chaque élève.
     * Autorise les calculs temps réel et historique complet.
     */
    public function up(): void
    {
        Schema::create('bulletin_grades', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('bulletin_id')
                  ->constrained('student_bulletins')
                  ->onDelete('cascade');
            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->onDelete('cascade');
            $table->foreignId('teacher_id')
                  ->constrained('users')
                  ->onDelete('restrict');
            
            // Notes saisies
            $table->decimal('note_classe', 4, 2)->nullable();
            $table->decimal('note_composition', 4, 2)->nullable();
            
            // Calculs automatiques
            $table->decimal('subject_average', 4, 2)->nullable();
            $table->smallInteger('subject_rank')->nullable();
            $table->string('appreciation', 200)->nullable();
            
            // État
            $table->boolean('is_locked')->default(false);
            $table->timestamp('entered_at')->nullable();
            $table->foreignId('entered_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            // Audit
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['bulletin_id', 'subject_id']);
            $table->index('teacher_id');
            $table->index('is_locked');
            $table->unique(['bulletin_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_grades');
    }
};
