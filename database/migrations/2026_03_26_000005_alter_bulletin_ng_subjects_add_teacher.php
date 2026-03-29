<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Alter bulletin_ng_subjects table
 * 
 * Ajouter colonnes pour:
 * - user_id: FK vers users table (l'enseignant responsable de cette matière)
 * - nom_prof: Nom du professeur (backup pour affichage)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bulletin_ng_subjects')) {
            Schema::table('bulletin_ng_subjects', function (Blueprint $table) {
                // Ajouter user_id si colonne n'existe pas
                if (! Schema::hasColumn('bulletin_ng_subjects', 'user_id')) {
                    $table->foreignId('user_id')
                          ->nullable()
                          ->constrained('users')
                          ->nullOnDelete()
                          ->after('config_id');
                }
                
                // Note: nom_prof colonne existe déjà dans migration originale
                // Mais ajoutons l'index si ce n'est pas fait
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bulletin_ng_subjects')) {
            Schema::table('bulletin_ng_subjects', function (Blueprint $table) {
                if (Schema::hasColumn('bulletin_ng_subjects', 'user_id')) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                }
            });
        }
    }
};
