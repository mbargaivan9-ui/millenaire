<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('bulletin_structures')) {
            Schema::create('bulletin_structures', function (Blueprint $table) {
                $table->id();
                
                // Relation avec la classe
                $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
                
                // Metadonnées
                $table->string('name')->comment('Nom du modèle/structure de bulletin');
                $table->text('description')->nullable()->comment('Description du format');
                $table->string('source_image_path')->nullable()->comment('Chemin de l\'image source OCR');
                
                // Données structurées
                $table->longText('structure_json')->nullable()
                    ->comment('Structure JSON: subjects[], coefficients[], grading_scale, appreciation_rules');
                $table->longText('calculation_rules')->nullable()
                    ->comment('Règles de calcul: formules, rounding, validation_rules, special_cases');
                
                // Confiance et validation
                $table->tinyInteger('ocr_confidence')->default(75)->comment('Score OCR (0-100)');
                $table->boolean('is_verified')->default(false)->comment('Vérifié par admin');
                $table->boolean('is_active')->default(true)->comment('Structure active et utilisée');
                
                // Audit
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                
                // Index
                $table->index('classe_id');
                $table->index('is_active');
                $table->index('is_verified');
                $table->unique(['classe_id', 'name'], 'unique_structure_per_class');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletin_structures');
    }
};
