<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_results')) {
            Schema::create('exam_results', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('academic_year');
                $table->integer('order')->default(0);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->unique(['name', 'academic_year']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
