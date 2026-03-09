<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Table: bulletin_templates
     * 
     * Stocke les templates de bulletins clonés via OCR + Claude Haiku
     * pour chaque classe et professeur principal.
     */
    public function up(): void
    {
        Schema::create('bulletin_templates', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('school_id')
                  ->constrained('establishments')
                  ->onDelete('cascade');
            $table->foreignId('classroom_id')
                  ->constrained('classes')
                  ->onDelete('cascade');
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('restrict');
            
            // Metadata
            $table->string('name')->default('Template Bulletin');
            $table->string('academic_year', 9); // ex: 2025-2026
            $table->tinyInteger('trimester')->default(1); // 1, 2, ou 3
            $table->string('original_image_path', 500)->nullable();
            
            // Structure et rendu
            $table->longText('structure_json'); // JSON: header, student_info, subjects[], etc.
            $table->longText('html_template'); // HTML/CSS généré par Claude Haiku
            $table->decimal('ocr_confidence_score', 5, 2)->nullable(); // 0-100
            
            // États
            $table->boolean('is_validated')->default(false);
            $table->timestamp('validated_at')->nullable();
            $table->tinyInteger('version')->default(1); // Versionning
            
            // Audit
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['school_id', 'classroom_id', 'trimester', 'academic_year']);
            $table->index('is_validated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_templates');
    }
};
