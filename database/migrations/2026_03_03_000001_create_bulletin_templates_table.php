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
        if (! Schema::hasTable('bulletin_templates')) {
            Schema::create('bulletin_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
                $table->string('name');
                $table->string('template_image_path')->nullable();
                $table->integer('image_width')->nullable();
                $table->integer('image_height')->nullable();
                $table->json('field_zones')->nullable()->comment('Zones de champs sur le template');
                $table->json('metadata')->nullable()->comment('Métadonnées additionnelles');
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                
                $table->index('classe_id');
                $table->index('is_active');
                $table->unique(['classe_id', 'name'], 'unique_template_per_class');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletin_templates');
    }
};
