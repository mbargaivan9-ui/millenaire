<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Table: student_bulletins
     * 
     * Bulletins individuels des élèves, générés automatiquement
     * à partir d'un template validé.
     */
    public function up(): void
    {
        Schema::create('student_bulletins', function (Blueprint $table) {
            $table->id();
            
            // Relations clés
            $table->foreignId('template_id')
                  ->constrained('bulletin_templates')
                  ->onDelete('cascade');
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->onDelete('cascade');
            $table->foreignId('classroom_id')
                  ->constrained('classes')
                  ->onDelete('cascade');
            
            // Période
            $table->string('academic_year', 9);
            $table->tinyInteger('trimester')->default(1);
            
            // Calculs automatiques
            $table->decimal('general_average', 4, 2)->nullable();
            $table->smallInteger('class_rank')->nullable();
            $table->string('appreciation', 100)->nullable();
            $table->smallInteger('total_absences')->default(0);
            
            // État du bulletin
            $table->enum('status', ['draft', 'partial', 'complete', 'exported'])
                  ->default('draft');
            
            // Export et verrouillage
            $table->timestamp('exported_at')->nullable();
            $table->string('pdf_path', 500)->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            // Métadonnées
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['student_id', 'classroom_id']);
            $table->index(['template_id', 'trimester']);
            $table->index('status');
            $table->unique(['student_id', 'template_id', 'trimester', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_bulletins');
    }
};
