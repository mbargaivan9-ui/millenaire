<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration Phase 3 — Colonnes manquantes pour les 4 espaces utilisateurs
 *
 * Cette migration est complémentaire à la Phase 2.
 * Elle ajoute les colonnes/index nécessaires aux espaces
 * Admin, Enseignant, Parent et Élève.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Colonne prof_principal_id sur la table classes ──
        // (Si elle n'existe pas encore)
        if (Schema::hasTable('classes') && ! Schema::hasColumn('classes', 'prof_principal_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->foreignId('prof_principal_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete()
                    ->comment('Professeur Principal de la classe');
            });
        }

        // ── Colonne is_active sur la table classes ──
        if (Schema::hasTable('classes') && ! Schema::hasColumn('classes', 'is_active')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('name');
            });
        }

        // ── Colonne is_active sur la table teachers ──
        if (Schema::hasTable('teachers') && ! Schema::hasColumn('teachers', 'is_active')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('id');
            });
        }

        // ── Colonne head_class_id sur la table teachers ──
        // (Pour détecter rapidement si l'enseignant est Prof Principal)
        if (Schema::hasTable('teachers') && ! Schema::hasColumn('teachers', 'head_class_id')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->foreignId('head_class_id')
                    ->nullable()
                    ->after('is_active')
                    ->constrained('classes')
                    ->nullOnDelete()
                    ->comment('Classe dont cet enseignant est Prof. Principal');
            });
        }

        // ── Colonne is_prof_principal sur la table teachers ──
        if (Schema::hasTable('teachers') && ! Schema::hasColumn('teachers', 'is_prof_principal')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->boolean('is_prof_principal')->default(false)->after('head_class_id');
            });
        }

        // ── Table guardians (parents/tuteurs liés aux élèves) ──
        // Si elle n'existe pas sous ce nom, on la crée.
        if (! Schema::hasTable('guardians')) {
            Schema::create('guardians', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()
                    ->comment('L\'utilisateur avec le rôle parent');
                $table->string('relationship')->nullable()
                    ->comment('père, mère, tuteur, etc.');
                $table->boolean('is_primary')->default(false)
                    ->comment('Contact principal');
                $table->timestamps();

                $table->unique(['student_id', 'user_id'], 'unique_guardian');
                $table->index('user_id', 'idx_guardian_user');
            });
        }

        // ── Index sur bulletin_summaries pour les rapports ──
        // (pour éviter full-table-scan sur les dashboards admin)
        // Laravel ignore les index en double
        try {
            Schema::table('bulletin_summaries', function (Blueprint $table) {
                $table->index(['academic_year', 'term', 'rank'], 'idx_bs_year_term_rank');
            });
        } catch (\Exception $e) {
            // index déjà existant, on ignore
        }

        // ── Index sur bulletin_entries pour les stats ──
        try {
            Schema::table('bulletin_entries', function (Blueprint $table) {
                $table->index(['academic_year', 'term', 'score'], 'idx_be_year_term_score');
            });
        } catch (\Exception $e) {
            // index déjà existant, on ignore
        }

        // ── Colonne category sur notifications ──
        if (Schema::hasTable('notifications') && ! Schema::hasColumn('notifications', 'category')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->string('category')->nullable()->after('type')
                    ->comment('Ex: grade_alert, payment, system');
            });
        }
    }

    public function down(): void
    {
        // ══════════════════════════════════════════════════════════════
        // 1. Désactiver les contraintes d'intégrité
        // ══════════════════════════════════════════════════════════════
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            // Supprimer les index ajoutés
            try {
                Schema::table('bulletin_summaries', function (Blueprint $table) {
                    $table->dropIndex('idx_bs_year_term_rank');
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('bulletin_entries', function (Blueprint $table) {
                    $table->dropIndex('idx_be_year_term_score');
                });
            } catch (\Exception $e) {}

            // Supprimer les colonnes ajoutées (ordre inverse des contraintes)
            if (Schema::hasTable('teachers')) {
                Schema::table('teachers', function (Blueprint $table) {
                    try { $table->dropConstrainedForeignId('head_class_id'); } catch(\Exception $e) {}
                    try { $table->dropColumn('is_prof_principal'); } catch(\Exception $e) {}
                    try { $table->dropColumn('is_active'); } catch(\Exception $e) {}
                });
            }

            if (Schema::hasTable('classes')) {
                Schema::table('classes', function (Blueprint $table) {
                    try { $table->dropConstrainedForeignId('prof_principal_id'); } catch(\Exception $e) {}
                    try { $table->dropColumn('is_active'); } catch(\Exception $e) {}
                });
            }

            // Enfin, supprimer la table guardians
            Schema::dropIfExists('guardians');
            
        } finally {
            // ══════════════════════════════════════════════════════════════
            // 2. Réactiver les contraintes d'intégrité
            // ══════════════════════════════════════════════════════════════
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
};
