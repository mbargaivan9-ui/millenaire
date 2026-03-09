<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: bulletin_entries
 *
 * Table principale du module Bulletin Vivant.
 * Chaque entrée représente la note d'un élève pour une matière, une séquence et un trimestre.
 * C'est sur cette table que repose tout le calcul temps réel (moyenne, rang, appréciation).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Table centrale : une note par élève / matière / séquence / trimestre
        if (! Schema::hasTable('bulletin_entries')) {
            Schema::create('bulletin_entries', function (Blueprint $table) {
                $table->id();

                // Qui est concerné
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('class_subject_teacher_id')->constrained('class_subject_teacher')->cascadeOnDelete();
                // Redondant mais utile pour les requêtes rapides
                $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();

                // Contexte académique
                $table->tinyInteger('term')->default(1)->comment('Trimestre: 1, 2, 3');
                $table->tinyInteger('sequence')->default(1)->comment('Séquence dans le trimestre: 1 ou 2');
                $table->string('academic_year', 9)->default('2025-2026')->comment('Ex: 2025-2026');

                // La note
                $table->decimal('score', 5, 2)->nullable()->comment('Note sur 20');
                $table->decimal('coefficient', 4, 2)->default(1.00);
                $table->boolean('excused_absence')->default(false)->comment('Absence justifiée = pas de calcul');

                // Appréciation
                $table->text('teacher_comment')->nullable()->comment('Commentaire du professeur');
                $table->string('auto_appreciation')->nullable()->comment('Appréciation suggérée automatiquement');

                // Traçabilité
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('recorded_at')->nullable();
                $table->foreignId('last_modified_by')->nullable()->constrained('users')->nullOnDelete();

                // Verrou (Professeur Principal peut verrouiller la classe)
                $table->boolean('is_locked')->default(false);
                $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('locked_at')->nullable();

                $table->timestamps();

                // Une seule note par élève / matière / séquence / trimestre / année
                $table->unique(
                    ['student_id', 'class_subject_teacher_id', 'term', 'sequence', 'academic_year'],
                    'unique_bulletin_entry'
                );

                $table->index(['class_id', 'term', 'sequence', 'academic_year'], 'idx_class_term_seq');
                $table->index(['student_id', 'class_id', 'term', 'academic_year'], 'idx_student_class_term');
                $table->index(['subject_id', 'term', 'sequence'], 'idx_subject_term');
            });
        }

        // Table des résumés (bulletins calculés par trimestre)
        if (! Schema::hasTable('bulletin_summaries')) {
            Schema::create('bulletin_summaries', function (Blueprint $table) {
                $table->id();

                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $table->tinyInteger('term')->default(1);
                $table->string('academic_year', 9)->default('2025-2026');

                // Moyennes calculées
                $table->decimal('sequence1_average', 5, 2)->nullable()->comment('Moy. Séq. 1');
                $table->decimal('sequence2_average', 5, 2)->nullable()->comment('Moy. Séq. 2');
                $table->decimal('term_average', 5, 2)->nullable()->comment('Moy. Trim. = (Seq1+Seq2)/2');
                $table->decimal('annual_average', 5, 2)->nullable()->comment('Moy. Annuelle recalculée');

                // Classement
                $table->unsignedSmallInteger('rank')->nullable()->comment('Rang dans la classe');
                $table->unsignedSmallInteger('total_students')->nullable();
                $table->string('rank_display')->nullable()->comment('Ex: 3ème/35');

                // Statut du bulletin
                $table->enum('status', ['draft', 'in_progress', 'locked', 'validated', 'published'])
                    ->default('draft');
                $table->string('appreciation')->nullable();
                $table->text('general_observation')->nullable();
                $table->text('principal_teacher_comment')->nullable();

                // Validation hiérarchique (Censeur / Proviseur)
                $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('validated_at')->nullable();
                $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('locked_at')->nullable();

                // Export PDF
                $table->string('pdf_path')->nullable();
                $table->timestamp('pdf_generated_at')->nullable();

                $table->timestamps();

                $table->unique(['student_id', 'class_id', 'term', 'academic_year'], 'unique_bulletin_summary');
                $table->index(['class_id', 'term', 'academic_year', 'status'], 'idx_class_status');
            });
        }

        // Table pour le verrouillage d'une classe entière par trimestre
        if (! Schema::hasTable('class_term_locks')) {
            Schema::create('class_term_locks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $table->tinyInteger('term');
                $table->string('academic_year', 9)->default('2025-2026');
                $table->boolean('is_locked')->default(false);
                $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('locked_at')->nullable();
                $table->text('lock_reason')->nullable();
                $table->timestamps();

                $table->unique(['class_id', 'term', 'academic_year'], 'unique_class_term_lock');
            });
        }

        // Table de complétion : suivi par matière pour le Professeur Principal
        if (! Schema::hasTable('subject_completion_tracking')) {
            Schema::create('subject_completion_tracking', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_subject_teacher_id')->constrained('class_subject_teacher')->cascadeOnDelete();
                $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $table->tinyInteger('term');
                $table->tinyInteger('sequence');
                $table->string('academic_year', 9);
                $table->unsignedSmallInteger('total_students')->default(0);
                $table->unsignedSmallInteger('filled_count')->default(0);
                $table->decimal('completion_percentage', 5, 2)->default(0.00);
                $table->timestamp('last_entry_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['class_subject_teacher_id', 'term', 'sequence', 'academic_year'],
                    'unique_completion_tracking'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_completion_tracking');
        Schema::dropIfExists('class_term_locks');
        Schema::dropIfExists('bulletin_summaries');
        Schema::dropIfExists('bulletin_entries');
    }
};
