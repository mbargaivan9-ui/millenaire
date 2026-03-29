<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Create bulletin_ng_trimestres table
 * 
 * Stockage des moyennes calculées par trimestre pour chaque étudiant
 * Permet le calcul rapide du classement sans recalculer les moyennes à chaque fois
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bulletin_ng_trimestres')) {
            Schema::create('bulletin_ng_trimestres', function (Blueprint $table) {
                $table->id();
                $table->foreignId('config_id')
                      ->constrained('bulletin_ng_configs')
                      ->cascadeOnDelete();
                $table->foreignId('ng_student_id')
                      ->constrained('bulletin_ng_students')
                      ->cascadeOnDelete();
                
                // Numéro du trimestre (1-3)
                $table->tinyInteger('trimestre_number');
                
                // Moyenne calculée
                $table->decimal('moyenne', 5, 2)->nullable();
                
                // Classement de l'élève dans la classe pour ce trimestre
                $table->integer('rang_classe')->nullable();
                
                // Nombre total d'élèves pour calculer le rang
                $table->integer('effectif_total')->nullable();
                
                $table->timestamps();
                
                // Constraint unique: one trimester average per student per config
                $table->unique([
                    'config_id',
                    'ng_student_id',
                    'trimestre_number'
                ], 'uniq_bulletin_ng_trimestre_student');
                
                $table->index(['config_id', 'trimestre_number', 'moyenne'], 'idx_bulletin_ng_trimestre_ranking');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_ng_trimestres');
    }
};
