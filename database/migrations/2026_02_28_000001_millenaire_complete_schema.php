<?php

// ─────────────────────────────────────────────────────────────────────────────
// FICHIER UNIQUE DE TOUTES LES NOUVELLES MIGRATIONS
// Phase 13 — Base de Données
// Exécuter via: php artisan migrate
// ─────────────────────────────────────────────────────────────────────────────

// ═══════════════════════════════════════════════════════
// Migration 1: appointments — Prise de RDV Parents/Profs
// ═══════════════════════════════════════════════════════
// php artisan make:migration create_appointments_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ───────────────────────────────────────────────────────
// MIGRATION: create_appointments_table
// ───────────────────────────────────────────────────────
return new class extends Migration {
    public function up(): void {
        // 1. Appointments
        if (!Schema::hasTable('appointments')) {
            Schema::create('appointments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->datetime('scheduled_at');
                $table->enum('status', ['pending', 'confirmed', 'cancelled', 'done'])->default('pending');
                $table->text('notes')->nullable();
                $table->text('parent_notes')->nullable();
                $table->string('meeting_link')->nullable(); // Pour les RDV en ligne
                $table->timestamps();

                $table->index(['teacher_id', 'scheduled_at']);
                $table->index(['parent_id', 'student_id']);
            });
        }

        // 2. Teacher Assignment Histories
        if (!Schema::hasTable('teacher_assignment_histories')) {
            Schema::create('teacher_assignment_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->foreignId('old_teacher_id')->nullable()->constrained('teachers')->onDelete('set null');
                $table->foreignId('new_teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
                $table->string('reason')->nullable();
                $table->timestamp('changed_at');
                $table->timestamps();

                $table->index(['class_id', 'changed_at']);
            });
        }

        // 3. Testimonials
        if (!Schema::hasTable('testimonials')) {
            Schema::create('testimonials', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('role');
                $table->text('content');
                $table->string('photo')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 4. Quiz Tables
        if (!Schema::hasTable('quizzes')) {
            Schema::create('quizzes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->integer('time_limit_minutes')->nullable();
                $table->integer('pass_score')->default(50);
                $table->boolean('is_published')->default(false);
                $table->timestamp('available_from')->nullable();
                $table->timestamp('available_until')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('quiz_questions')) {
            Schema::create('quiz_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
                $table->text('question');
                $table->enum('type', ['multiple_choice', 'true_false', 'short_answer'])->default('multiple_choice');
                $table->json('options')->nullable(); // Array of options for MCQ
                $table->text('correct_answer');
                $table->integer('points')->default(1);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('quiz_submissions')) {
            Schema::create('quiz_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->json('answers');
                $table->integer('score')->nullable();
                $table->integer('total_points')->nullable();
                $table->boolean('is_graded')->default(false);
                $table->timestamp('submitted_at');
                $table->timestamps();

                $table->unique(['quiz_id', 'student_id']);
            });
        }

        // 5. Modifications table users
        if (!Schema::hasColumn('users', 'preferred_language')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('preferred_language', ['fr', 'en'])->default('fr')->after('email');
            });
        }
        if (!Schema::hasColumn('users', 'is_online')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_online')->default(false)->after('preferred_language');
            });
        }
        if (!Schema::hasColumn('users', 'peer_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('peer_id')->nullable()->after('is_online');
            });
        }

        // 6. Modifications table messages
        if (!Schema::hasColumn('messages', 'is_edited')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->boolean('is_edited')->default(false)->after('content');
                $table->timestamp('edited_at')->nullable()->after('is_edited');
                $table->boolean('is_deleted_for_sender')->default(false)->after('edited_at');
                $table->boolean('is_deleted_for_all')->default(false)->after('is_deleted_for_sender');
            });
        }

        // 7. Modifications table classes — section bilingue
        if (!Schema::hasColumn('classes', 'section')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->enum('section', ['francophone', 'anglophone'])->default('francophone')->after('name');
                $table->integer('capacity')->nullable()->after('section');
                $table->foreignId('head_teacher_id')->nullable()->after('capacity')
                    ->constrained('teachers')->onDelete('set null');
            });
        }

        // 8. Modifications table teachers
        if (!Schema::hasColumn('teachers', 'is_prof_principal')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->boolean('is_prof_principal')->default(false)->after('is_active');
                $table->foreignId('head_class_id')->nullable()->after('is_prof_principal')
                    ->constrained('classes')->onDelete('set null');
            });
        }

        // 9. Extensions EstablishmentSetting
        // First create the table if it doesn't exist
        if (!Schema::hasTable('establishment_settings')) {
            Schema::create('establishment_settings', function (Blueprint $table) {
                $table->id();
                $table->string('platform_name')->nullable();
                $table->string('school_name_fr')->nullable();
                $table->string('school_name_en')->nullable();
                $table->string('slogan')->nullable();
                $table->string('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('hero_title')->nullable();
                $table->text('hero_subtitle')->nullable();
                $table->string('hero_cta_text')->nullable();
                $table->string('hero_image')->nullable();
                $table->string('primary_color')->default('#0d9488');
                $table->string('secondary_color')->default('#0f766e');
                $table->string('favicon_path')->nullable();
                $table->string('about_image')->nullable();
                $table->string('signature_image')->nullable();
                $table->string('proviseur_title')->nullable();
                $table->string('proviseur_name')->nullable();
                $table->string('social_facebook')->nullable();
                $table->string('social_twitter')->nullable();
                $table->string('google_maps_url')->nullable();
                $table->integer('years_existence')->default(10);
                $table->string('anglophone_grading')->default('letter');
                $table->integer('sequences_per_term')->default(2);
                $table->string('current_academic_year')->nullable();
                $table->integer('current_term')->default(1);
                $table->integer('current_sequence')->default(1);
                $table->string('grading_system')->default('20');
                $table->decimal('pass_mark', 5, 2)->default(10.00);
                $table->boolean('notify_absence_parent')->default(true);
                $table->boolean('notify_new_bulletin')->default(true);
                $table->boolean('notify_payment_success')->default(true);
                $table->boolean('email_notifications')->default(false);
                $table->timestamps();
            });
        }
        // Then add columns if they don't exist
        elseif (!Schema::hasColumn('establishment_settings', 'platform_name')) {
            Schema::table('establishment_settings', function (Blueprint $table) {
                $table->string('platform_name')->nullable()->after('id');
                $table->string('slogan')->nullable();
                $table->string('hero_title')->nullable();
                $table->text('hero_subtitle')->nullable();
                $table->string('hero_cta_text')->nullable();
                $table->string('hero_image')->nullable();
                $table->string('primary_color')->default('#0d9488');
                $table->string('secondary_color')->default('#0f766e');
                $table->string('favicon_path')->nullable();
                $table->string('about_image')->nullable();
                $table->string('signature_image')->nullable();
                $table->string('proviseur_title')->nullable();
                $table->string('social_facebook')->nullable();
                $table->string('social_twitter')->nullable();
                $table->string('google_maps_url')->nullable();
                $table->integer('years_existence')->default(10);
                $table->string('anglophone_grading')->default('letter');
                $table->integer('sequences_per_term')->default(2);
                $table->boolean('notify_absence_parent')->default(true);
                $table->boolean('notify_new_bulletin')->default(true);
                $table->boolean('notify_payment_success')->default(true);
                $table->boolean('email_notifications')->default(false);
            });
        }

        // 10. Message read receipts (si pas déjà existant)
        if (!Schema::hasTable('message_reads')) {
            Schema::create('message_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamp('read_at');
                $table->timestamps();

                $table->unique(['message_id', 'user_id']);
                $table->index('message_id');
            });
        }

        // 11. Teacher availability (RDV)
        if (!Schema::hasTable('teacher_availabilities')) {
            Schema::create('teacher_availabilities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']);
                $table->time('start_time');
                $table->time('end_time');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('teacher_id');
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('teacher_availabilities');
        Schema::dropIfExists('message_reads');
        Schema::dropIfExists('quiz_submissions');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('teacher_assignment_histories');
        Schema::dropIfExists('appointments');
    }
};
