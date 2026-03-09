<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletin_dynamic_structures', function (Blueprint $table) {
            $table->id();
            
            // Classe concernée
            $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
            
            // Source du template
            $table->string('source_file_path')->comment('Chemin du fichier OCR source');
            $table->string('source_type')->default('image')->comment('image, pdf, manual');
            
            // Structure extraite
            $table->json('structure')->comment('Structure JSON: matières, coefs, formules');
            $table->json('metadata')->nullable()->comment('Métadonnées additionnelles (école, année)');
            
            // Configuration des calculs
            $table->json('formula_config')->nullable()->comment('Configurations des formules de calcul');
            $table->json('column_mapping')->nullable()->comment('Mappage des colonnes OCR aux champs DB');
            
            // Statut
            $table->enum('status', ['draft', 'validated', 'active', 'archived'])->default('draft');
            $table->text('validation_notes')->nullable();
            
            // Audit
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            
            $table->index(['classe_id', 'status']);
            $table->index('created_by');
            $table->unique(['classe_id', 'status'], 'unique_active_structure_per_class');
        });

        // Pivot table: bulletin structure fields
        Schema::create('bulletin_structure_fields', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('bulletin_dynamic_structure_id');
            $table->foreign('bulletin_dynamic_structure_id', 'fk_bsf_structure')
                ->references('id')
                ->on('bulletin_dynamic_structures')
                ->cascadeOnDelete();
            
            // Informations du champ
            $table->string('field_name')->comment('ex: mathematique, francais, etc');
            $table->string('field_label')->comment('Label à afficher');
            $table->enum('field_type', ['subject', 'coefficient', 'note', 'average', 'rank', 'appreciation', 'custom'])->default('subject');
            
            // Position et ordre
            $table->integer('column_index')->comment('Index de colonne dans OCR');
            $table->integer('display_order')->default(0);
            
            // Configuration du calcul
            $table->string('calculation_formula')->nullable()->comment('ex: (n1+n2)/2, max, etc');
            $table->decimal('coefficient', 8, 2)->nullable()->default(1);
            $table->decimal('min_value', 8, 2)->nullable()->default(0);
            $table->decimal('max_value', 8, 2)->nullable()->default(20);
            
            // État
            $table->boolean('is_required')->default(true);
            $table->boolean('is_visible')->default(true);
            
            $table->timestamps();
            
            $table->index('bulletin_dynamic_structure_id');
        });

        // Table d'historique pour tracking des modifications
        Schema::create('bulletin_structure_revisions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('bulletin_dynamic_structure_id');
            $table->foreign('bulletin_dynamic_structure_id', 'fk_bsr_structure')
                ->references('id')
                ->on('bulletin_dynamic_structures')
                ->cascadeOnDelete();
            
            $table->json('old_structure')->nullable();
            $table->json('new_structure')->nullable();
            $table->text('change_description')->nullable();
            
            $table->foreignId('modified_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('modified_at')->useCurrent();
            
            $table->index('bulletin_dynamic_structure_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_structure_revisions');
        Schema::dropIfExists('bulletin_structure_fields');
        Schema::dropIfExists('bulletin_dynamic_structures');
    }
};
