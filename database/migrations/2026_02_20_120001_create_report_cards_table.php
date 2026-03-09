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
        if (!Schema::hasTable('report_cards')) {
            Schema::create('report_cards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained()->onDelete('cascade');
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->enum('term', ['1', '2', '3'])->default('1');
                $table->integer('sequence')->default(1);
                $table->decimal('term_average', 5, 2)->nullable();
                $table->integer('rank')->nullable();
                $table->text('appreciation')->nullable();
                $table->text('behavior_comment')->nullable();
                $table->unsignedBigInteger('generated_by')->nullable();
                $table->dateTime('generated_at')->nullable();
                $table->boolean('is_validated')->default(false);
                $table->unsignedBigInteger('validated_by')->nullable();
                $table->dateTime('validated_at')->nullable();
                $table->string('pdf_path')->nullable();
                $table->timestamps();
                
                $table->index(['student_id', 'class_id']);
                $table->index(['term', 'sequence']);
                $table->index('is_validated');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
