<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Bulletin NG (Nouvelle Génération)
 * Système de bulletin Le Millenaire — Bilingue FR/EN
 * Tables : bulletin_ng_configs, bulletin_ng_subjects,
 *          bulletin_ng_students, bulletin_ng_notes, bulletin_ng_conduites
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Configuration par session (prof principal)
        if (! Schema::hasTable('bulletin_ng_configs')) {
            Schema::create('bulletin_ng_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prof_principal_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();

                // Langue / Section
                $table->enum('langue', ['FR', 'EN'])->default('FR');

                // Infos établissement
                $table->string('school_name')->default('ÉTABLISSEMENT LE MILLENAIRE');
                $table->string('logo_path')->nullable();
                $table->string('delegation_fr')->nullable();
                $table->string('delegation_en')->nullable();

                // Infos pédagogiques
                $table->string('nom_classe');
                $table->integer('effectif')->default(0);
                $table->tinyInteger('trimestre')->default(1);
                $table->tinyInteger('sequence')->default(1);
                $table->string('annee_academique', 9)->default('2025-2026');

                // Workflow
                $table->enum('statut', ['configuration', 'saisie_ouverte', 'saisie_fermee', 'conduite', 'genere'])
                      ->default('configuration');
                $table->boolean('notes_verrouillee')->default(false);
                $table->timestamp('notes_verrouillee_at')->nullable();

                $table->timestamps();

                $table->index(['prof_principal_id', 'class_id', 'trimestre', 'annee_academique'], 'idx_bulletin_ng_config');
            });
        }

        // ── 2. Matières configurées pour la session
        if (! Schema::hasTable('bulletin_ng_subjects')) {
            Schema::create('bulletin_ng_subjects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('config_id')
                      ->constrained('bulletin_ng_configs')
                      ->cascadeOnDelete();
                $table->string('nom');
                $table->decimal('coefficient', 4, 2)->default(1.00);
                $table->string('nom_prof')->nullable();
                $table->integer('ordre')->default(0);
                $table->timestamps();
            });
        }

        // ── 3. Élèves de la session
        if (! Schema::hasTable('bulletin_ng_students')) {
            Schema::create('bulletin_ng_students', function (Blueprint $table) {
                $table->id();
                $table->foreignId('config_id')
                      ->constrained('bulletin_ng_configs')
                      ->cascadeOnDelete();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();

                $table->string('matricule');
                $table->string('nom');
                $table->date('date_naissance')->nullable();
                $table->string('lieu_naissance')->nullable();
                $table->enum('sexe', ['M', 'F'])->default('M');
                $table->integer('ordre')->default(0);

                $table->timestamps();
            });
        }

        // ── 4. Notes par élève / matière
        if (! Schema::hasTable('bulletin_ng_notes')) {
            Schema::create('bulletin_ng_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('config_id')
                      ->constrained('bulletin_ng_configs')
                      ->cascadeOnDelete();
                $table->foreignId('ng_student_id')
                      ->constrained('bulletin_ng_students')
                      ->cascadeOnDelete();
                $table->foreignId('ng_subject_id')
                      ->constrained('bulletin_ng_subjects')
                      ->cascadeOnDelete();

                $table->decimal('note', 5, 2)->nullable();

                // Traçabilité
                $table->foreignId('saisie_par')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('saisie_at')->nullable();

                $table->timestamps();

                $table->unique(['ng_student_id', 'ng_subject_id'], 'uniq_note_eleve_matiere');
            });
        }

        // ── 5. Conduite & comportement
        if (! Schema::hasTable('bulletin_ng_conduites')) {
            Schema::create('bulletin_ng_conduites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('config_id')
                      ->constrained('bulletin_ng_configs')
                      ->cascadeOnDelete();
                $table->foreignId('ng_student_id')
                      ->constrained('bulletin_ng_students')
                      ->cascadeOnDelete();

                // Travail
                $table->boolean('tableau_honneur')->default(false);
                $table->boolean('encouragement')->default(false);
                $table->boolean('felicitations')->default(false);
                $table->boolean('blame_travail')->default(false);
                $table->string('avert_travail')->default('Non');

                // Conduite
                $table->integer('absences_totales')->default(0);
                $table->integer('absences_nj')->default(0);
                $table->boolean('exclusion')->default(false);
                $table->string('avert_conduite')->default('Non');
                $table->string('blame_conduite')->default('Non');

                $table->timestamps();

                $table->unique(['ng_student_id'], 'uniq_conduite_eleve');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_ng_conduites');
        Schema::dropIfExists('bulletin_ng_notes');
        Schema::dropIfExists('bulletin_ng_students');
        Schema::dropIfExists('bulletin_ng_subjects');
        Schema::dropIfExists('bulletin_ng_configs');
    }
};
