<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Table: template_subject_assignments
     * 
     * Pivot table : attribue les professeurs à chaque matière
     * d'un template et gère les permissions d'accès (saisie, appréciation).
     */
    public function up(): void
    {
        // Check if table already exists to avoid conflicts
        // Also check if referenced tables exist
        if (!Schema::hasTable('template_subject_assignments') && 
            Schema::hasTable('bulletin_templates') && 
            Schema::hasTable('subjects')) {
            
            Schema::create('template_subject_assignments', function (Blueprint $table) {
                $table->id();
                
                // Relations
                $table->foreignId('template_id')
                      ->constrained('bulletin_templates')
                      ->onDelete('cascade');
                $table->foreignId('subject_id')
                      ->constrained('subjects')
                      ->onDelete('cascade');
                $table->foreignId('teacher_id')
                      ->constrained('users')
                      ->onDelete('cascade');
                
                // Permissions granulaires
                $table->boolean('can_enter_grades')->default(true);
                $table->boolean('can_enter_appreciation')->default(true);
                
                // Tracking d'activation
                $table->timestamp('access_granted_at')->nullable();
                $table->foreignId('access_granted_by')
                      ->nullable()
                      ->constrained('users')
                      ->onDelete('set null');
                
                // Audit
                $table->timestamps();
                
                // Index et unicité
                $table->unique(['template_id', 'subject_id', 'teacher_id']);
                $table->index(['teacher_id', 'template_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('template_subject_assignments');
    }
};
