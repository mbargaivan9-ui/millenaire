<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ════════════════════════════════════════════════
        // Table: admin_specialized_roles
        // Stores specialized roles (Censeur, Intendant, Secrétaire, Surveillant)
        // ════════════════════════════════════════════════
        if (!Schema::hasTable('admin_specialized_roles')) {
            Schema::create('admin_specialized_roles', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique(); // censeur, intendant, secretaire, surveillant
                $table->string('name'); // Censeur, Intendant, Secrétaire, Surveillant Général
                $table->text('description')->nullable();
                $table->string('icon')->nullable(); // For frontend display
                $table->string('color')->nullable(); // Color for UI
                $table->integer('hierarchy_level')->default(0); // 0=lowest
                $table->json('default_permissions')->nullable(); // Default permissions for this role
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // ════════════════════════════════════════════════
        // Table: admin_role_sections
        // Stores sections that can be assigned/managed by roles
        // ════════════════════════════════════════════════
        if (!Schema::hasTable('admin_role_sections')) {
            Schema::create('admin_role_sections', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique(); // students, classes, teachers, finance, etc
                $table->string('name'); // Students Management, Classes, etc
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('route')->nullable(); // Route name (e.g., admin.students.index)
                $table->integer('order')->default(0); // Display order
                $table->json('required_permissions')->nullable(); // Permissions needed
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // ════════════════════════════════════════════════
        // Table: admin_role_section_assignments
        // Links specialized roles to their assigned sections
        // ════════════════════════════════════════════════
        if (!Schema::hasTable('admin_role_section_assignments')) {
            Schema::create('admin_role_section_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_specialized_role_id')
                    ->constrained('admin_specialized_roles')
                    ->cascadeOnDelete();
                $table->foreignId('admin_role_section_id')
                    ->constrained('admin_role_sections')
                    ->cascadeOnDelete();
                $table->json('permissions')->nullable(); // Specific permissions for this assignment
                $table->boolean('can_create')->default(false);
                $table->boolean('can_read')->default(true);
                $table->boolean('can_update')->default(false);
                $table->boolean('can_delete')->default(false);
                $table->boolean('can_export')->default(false);
                $table->timestamp('assigned_at')->useCurrent();
                $table->timestamp('removed_at')->nullable();
                $table->timestamps();

                // Unique constraint
                $table->unique(['admin_specialized_role_id', 'admin_role_section_id'], 'arsa_role_section_unique');
            });
        }

        // ════════════════════════════════════════════════
        // Table: user_specialized_role_assignments
        // Links users to their specialized roles with section assignments
        // ════════════════════════════════════════════════
        if (!Schema::hasTable('user_specialized_role_assignments')) {
            Schema::create('user_specialized_role_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('admin_specialized_role_id')
                    ->references('id')->on('admin_specialized_roles')
                    ->cascadeOnDelete();
                $table->json('assigned_sections')->nullable(); // JSON array of assigned section IDs
                $table->text('notes')->nullable(); // Notes about assignment
                $table->timestamp('assigned_at')->useCurrent();
                $table->timestamp('assigned_by_id')->nullable(); // Who assigned this role
                $table->timestamp('deactivated_at')->nullable();
                $table->timestamps();

                // Indices for quick lookup
                $table->index('user_id');
                $table->index('admin_specialized_role_id');
            });
        }

        // ════════════════════════════════════════════════
        // Table: admin_section_activity_logs
        // Tracks what each role does in their assigned sections
        // ════════════════════════════════════════════════
        if (!Schema::hasTable('admin_section_activity_logs')) {
            Schema::create('admin_section_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('admin_role_section_id')
                    ->references('id')->on('admin_role_sections')
                    ->cascadeOnDelete();
                $table->string('action'); // create, read, update, delete, export
                $table->string('entity_type')->nullable(); // student, teacher, class, etc
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->text('description')->nullable();
                $table->json('changes')->nullable(); // What was changed
                $table->string('ip_address')->nullable();
                $table->timestamp('logged_at')->useCurrent();
                $table->timestamps();

                // Indices
                $table->index('user_id');
                $table->index('admin_role_section_id');
                $table->index('logged_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_section_activity_logs');
        Schema::dropIfExists('user_specialized_role_assignments');
        Schema::dropIfExists('admin_role_section_assignments');
        Schema::dropIfExists('admin_role_sections');
        Schema::dropIfExists('admin_specialized_roles');
    }
};
