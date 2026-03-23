<?php
/**
 * Migration: grade_scales
 *
 * Barème d'appréciation configurable par établissement.
 * Ex: 0-9 = Insuffisant, 10-12 = Assez Bien, 13-15 = Bien, 16-18 = Très Bien, 19-20 = Excellent
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('grade_scales')) {
            Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();

            // Scope: un barème par établissement (ou global si establishment_id null)
            $table->foreignId('establishment_setting_id')
                ->nullable()
                ->constrained('establishment_settings')
                ->nullOnDelete();

            $table->decimal('min_value', 5, 2)->default(0);
            $table->decimal('max_value', 5, 2)->default(9.99);
            $table->string('label', 50)->comment('Ex: Insuffisant, Bien, Excellent...');
            $table->string('color_hex', 7)->default('#FF4444');
            $table->tinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['establishment_setting_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scales');
    }
};
