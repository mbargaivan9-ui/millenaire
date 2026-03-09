<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_materials')) {
            Schema::create('course_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('type', ['document', 'video', 'link', 'image', 'audio', 'other'])->default('document');
                $table->string('file_path')->nullable();
                $table->string('file_name')->nullable();
                $table->string('file_size')->nullable();
                $table->string('url')->nullable();
                $table->boolean('is_published')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->integer('download_count')->default(0);
                $table->timestamps();

                $table->index(['teacher_id', 'subject_id']);
                $table->index(['class_id', 'is_published']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('course_materials');
    }
};
