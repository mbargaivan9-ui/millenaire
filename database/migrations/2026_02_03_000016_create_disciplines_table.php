<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des disciplines/sanctions
     */
    public function up(): void
    {
        if (!Schema::hasTable('disciplines')) {
            Schema::create('disciplines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
                $table->string('type'); // warning, detention, suspension, expulsion, etc
                $table->text('reason');
                $table->text('description')->nullable();
                $table->date('incident_date');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->text('resolution')->nullable();
                $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending');
                $table->timestamps();
                $table->index(['student_id', 'incident_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplines');
    }
};
