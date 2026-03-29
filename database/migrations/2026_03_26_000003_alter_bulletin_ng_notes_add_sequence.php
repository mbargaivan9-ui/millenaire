<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Alter bulletin_ng_notes table
 * 
 * Ajouter colonnes pour:
 * - sequence_number: Numéro de la séquence (1-6)
 * - session_id: FK vers bulletin_ng_sessions pour tracer l'historique
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bulletin_ng_notes')) {
            Schema::table('bulletin_ng_notes', function (Blueprint $table) {
                // Ajouter sequence_number si colonne n'existe pas
                if (! Schema::hasColumn('bulletin_ng_notes', 'sequence_number')) {
                    $table->tinyInteger('sequence_number')->default(1)->after('ng_subject_id');
                }
                
                // Ajouter session_id si colonne n'existe pas
                if (! Schema::hasColumn('bulletin_ng_notes', 'session_id')) {
                    $table->foreignId('session_id')
                          ->nullable()
                          ->constrained('bulletin_ng_sessions')
                          ->nullOnDelete()
                          ->after('config_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bulletin_ng_notes')) {
            Schema::table('bulletin_ng_notes', function (Blueprint $table) {
                if (Schema::hasColumn('bulletin_ng_notes', 'session_id')) {
                    $table->dropForeign(['session_id']);
                    $table->dropColumn('session_id');
                }
                if (Schema::hasColumn('bulletin_ng_notes', 'sequence_number')) {
                    $table->dropColumn('sequence_number');
                }
            });
        }
    }
};
