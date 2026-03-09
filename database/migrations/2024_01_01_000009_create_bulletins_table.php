<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->integer('trimester')->default(1); // 1, 2, 3
            $table->integer('year')->default(2024);
            $table->decimal('general_average', 5, 2)->nullable();
            $table->string('ranking')->nullable(); // Classement
            $table->integer('rank_position')->nullable();
            $table->string('status')->default('draft'); // draft, finalized, published
            $table->text('observation')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->unique(['student_id', 'trimester', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletins');
    }
};
