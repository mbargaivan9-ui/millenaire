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
        // Individual fields in the structure
        if (!Schema::hasTable('bulletin_structure_fields')) {
            Schema::create('bulletin_structure_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulletin_structure_id');
            $table->foreign('bulletin_structure_id', 'fk_bsf_structure')
                ->references('id')
                ->on('bulletin_dynamic_structures')
                ->onDelete('cascade');

            // Field identity
            $table->string('field_name'); // e.g., "francais", "math", "moyenne"
            $table->string('field_label'); // Display name: "Français", "Mathématiques"
            $table->enum('field_type', [
                'subject', 'coefficient', 'note', 'average', 'rank', 'appreciation', 'custom'
            ])->default('subject');

            // Field configuration
            $table->decimal('coefficient', 5, 2)->default(1); // For subjects
            $table->decimal('min_value', 5, 2)->default(0); // Minimum possible value
            $table->decimal('max_value', 5, 2)->default(20); // Maximum possible value

            // Calculation formula (for calculated fields)
            $table->text('calculation_formula')->nullable(); // e.g., "weighted_average", "rank_by_avg"
            $table->json('formula_params')->default('{}'); // Additional parameters

            // Display config
            $table->integer('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_editable')->default(true);

            $table->timestamps();

            // Indexes - use shorter names for MySQL compatibility
            $table->index(['bulletin_structure_id', 'field_type'], 'idx_bsf_struct_type');
            $table->index('display_order', 'idx_bsf_order');
            });
        }

        // Revision history for audit trail
        if (!Schema::hasTable('bulletin_structure_revisions')) {
            Schema::create('bulletin_structure_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulletin_structure_id');
            $table->foreign('bulletin_structure_id', 'fk_bsr_structure')
                ->references('id')
                ->on('bulletin_dynamic_structures')
                ->onDelete('cascade');

            // What changed
            $table->json('old_structure')->nullable();
            $table->json('new_structure')->nullable();
            $table->text('change_description')->nullable();

            // Who made the change and when
            $table->foreignId('modified_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('modified_at')->useCurrent();

            // Indexes - use shorter names to avoid MySQL identifier length limit
            $table->index(['bulletin_structure_id', 'modified_at'], 'idx_bsr_struct_modified');
            $table->index('modified_at', 'idx_bsr_modified');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletin_structure_revisions');
        Schema::dropIfExists('bulletin_structure_fields');
    }
};
