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
        Schema::create('parent_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id')->comment('Professeur principal qui a créé le token');
            $table->unsignedBigInteger('student_id')->comment('Élève dont le parent accède');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Parent/Tuteur user_id (peut être null si compte non crée)');
            $table->string('token', 100)->unique()->index()->comment('Token unique d\'accès');
            $table->string('email')->nullable()->comment('Email du parent pour création compte');
            $table->string('phone')->nullable()->comment('Téléphone du parent');
            $table->string('relationship')->default('parent')->comment('Relation avec l\'étudiant (parent, guardian, etc)');
            $table->timestamp('expires_at')->nullable()->comment('Expiration du token (null = pas d\'expiration)');
            $table->timestamp('used_at')->nullable()->comment('Date d\'utilisation du token');
            $table->boolean('is_revoked')->default(false)->comment('Token révoqué par le prof principal');
            $table->timestamps();
            
            // Indexes et foreign keys
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['student_id', 'teacher_id']);
            $table->index(['token', 'is_revoked']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_access_tokens');
    }
};
