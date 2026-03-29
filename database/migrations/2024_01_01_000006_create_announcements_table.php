<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('announcements')) {
            return; // Table already exists, skip
        }
        
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('content');
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('target_audience')->default('all'); // students, parents, teachers, all
            $table->string('status')->default('draft'); // draft, active, scheduled, archived
            $table->string('priority')->default('normal'); // low, normal, high
            $table->string('image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
            
            $table->index('status');
            $table->index('target_audience');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
