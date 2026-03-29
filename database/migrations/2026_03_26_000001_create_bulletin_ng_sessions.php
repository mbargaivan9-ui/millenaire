<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Create bulletin_ng_sessions table
 * 
 * Représente une session de saisie de bulletins pour une trimestre/séquence
 * État workflow: brouillon → saisie_ouverte → saisie_fermee → conduite → genere
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bulletin_ng_sessions')) {
            Schema::create('bulletin_ng_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('config_id')
                      ->constrained('bulletin_ng_configs')
                      ->cascadeOnDelete();
                
                // État de la session
                $table->enum('statut', [
                    'brouillon',
                    'saisie_ouverte',
                    'saisie_fermee',
                    'conduite',
                    'genere'
                ])->default('brouillon');
                
                // Visibilité et publication
                $table->boolean('visibilite_enseignants')->default(false);
                $table->timestamp('date_publication')->nullable();
                
                // Verrouillage des notes
                $table->boolean('notes_verrouillee')->default(false);
                $table->timestamp('notes_verrouillee_at')->nullable();
                
                // Références trimestre et séquence
                $table->tinyInteger('trimestre_number')->default(1);  // 1-3
                $table->tinyInteger('sequence_number')->default(1);   // 1-6
                
                // Métadonnées
                $table->text('description')->nullable();
                
                $table->timestamps();
                
                $table->index(['config_id', 'statut'], 'idx_bulletin_ng_session');
                $table->index(['trimestre_number', 'sequence_number'], 'idx_bulletin_ng_session_trim_seq');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_ng_sessions');
    }
};
