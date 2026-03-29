<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Alter bulletin_ng_students table
 * 
 * Ajouter colonnes pour:
 * - is_active: Boolean pour indiquer si étudiant est actif dans cette session
 * - matricule_original: Stocker le matricule original pour historique
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bulletin_ng_students')) {
            Schema::table('bulletin_ng_students', function (Blueprint $table) {
                // Ajouter is_active si colonne n'existe pas
                if (! Schema::hasColumn('bulletin_ng_students', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('sexe');
                }
                
                // Ajouter matricule_original si colonne n'existe pas
                if (! Schema::hasColumn('bulletin_ng_students', 'matricule_original')) {
                    $table->string('matricule_original')->nullable()->after('matricule');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bulletin_ng_students')) {
            Schema::table('bulletin_ng_students', function (Blueprint $table) {
                if (Schema::hasColumn('bulletin_ng_students', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if (Schema::hasColumn('bulletin_ng_students', 'matricule_original')) {
                    $table->dropColumn('matricule_original');
                }
            });
        }
    }
};
