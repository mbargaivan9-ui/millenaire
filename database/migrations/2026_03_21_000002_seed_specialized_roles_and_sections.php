<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ════════════════════════════════════════════════
        // Insert Specialized Roles
        // ════════════════════════════════════════════════
        
        $specializedRoles = [
            [
                'code' => 'censeur',
                'name' => 'Censeur',
                'description' => 'Responsable de la gestion pédagogique, contrôle académique et discipline',
                'icon' => '📚',
                'color' => '#3b82f6',
                'hierarchy_level' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'intendant',
                'name' => 'Intendant',
                'description' => 'Responsable des finances, ressources matérielles et administratives',
                'icon' => '💼',
                'color' => '#10b981',
                'hierarchy_level' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'secretaire',
                'name' => 'Secrétaire',
                'description' => 'Gestion administrative, inscriptions et dossiers étudiants',
                'icon' => '📋',
                'color' => '#f59e0b',
                'hierarchy_level' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'surveillant',
                'name' => 'Surveillant Général',
                'description' => 'Surveillance, discipline des élèves et sécurité de l\'établissement',
                'icon' => '👮',
                'color' => '#ef4444',
                'hierarchy_level' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($specializedRoles as $role) {
            DB::table('admin_specialized_roles')->insert($role);
        }

        // ════════════════════════════════════════════════
        // Insert Admin Sections
        // ════════════════════════════════════════════════
        
        $sections = [
            // CENSEUR Sections
            [
                'code' => 'classes',
                'name' => 'Gestion des Classes',
                'description' => 'Manage classes, class information, and settings',
                'icon' => '🏫',
                'route' => 'admin.classes.index',
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'students',
                'name' => 'Gestion des Élèves',
                'description' => 'Manage student records, enrollments, and information',
                'icon' => '🎓',
                'route' => 'admin.students.index',
                'order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'teachers',
                'name' => 'Gestion des Enseignants',
                'description' => 'Manage teacher records and assignments',
                'icon' => '👩‍🏫',
                'route' => 'admin.teachers.index',
                'order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'subjects',
                'name' => 'Gestion des Matières',
                'description' => 'Manage subjects and curricula',
                'icon' => '📖',
                'route' => 'admin.subjects.index',
                'order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'assignments',
                'name' => 'Assignations (Classe-Matière)',
                'description' => 'Manage teacher-class-subject assignments',
                'icon' => '🔗',
                'route' => 'admin.assignments.index',
                'order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'attendance',
                'name' => 'Présences',
                'description' => 'Manage student and teacher attendance',
                'icon' => '✅',
                'route' => 'admin.attendance.index',
                'order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'marks',
                'name' => 'Gestion des Notes',
                'description' => 'Manage and validate student marks and grades',
                'icon' => '📊',
                'route' => 'admin.marks.index',
                'order' => 7,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'bulletins',
                'name' => 'Bulletins Scolaires',
                'description' => 'Manage and validate student report cards',
                'icon' => '📄',
                'route' => 'admin.bulletins.index',
                'order' => 8,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // INTENDANT Sections
            [
                'code' => 'finance',
                'name' => 'Gestion Financière',
                'description' => 'Manage school finances and treasury',
                'icon' => '💰',
                'route' => 'admin.finance.index',
                'order' => 9,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'fees',
                'name' => 'Gestion des Frais',
                'description' => 'Manage school fees and payments',
                'icon' => '🏦',
                'route' => 'admin.fees.index',
                'order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'payments',
                'name' => 'Historique des Paiements',
                'description' => 'View and manage payment history',
                'icon' => '💳',
                'route' => 'admin.payments.history',
                'order' => 11,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'announcements',
                'name' => 'Annonces',
                'description' => 'Manage school announcements',
                'icon' => '📢',
                'route' => 'admin.announcements.index',
                'order' => 12,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // SECRÉTAIRE Sections
            [
                'code' => 'schedule',
                'name' => 'Emploi du Temps',
                'description' => 'Manage school schedule and timetables',
                'icon' => '📅',
                'route' => 'admin.schedule.index',
                'order' => 13,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'users',
                'name' => 'Gestion des Utilisateurs',
                'description' => 'Manage system users and accounts',
                'icon' => '👥',
                'route' => 'admin.users.index',
                'order' => 14,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // SURVEILLANT Sections
            [
                'code' => 'discipline',
                'name' => 'Discipline des Élèves',
                'description' => 'Manage student discipline cases',
                'icon' => '⚖️',
                'route' => 'admin.discipline.index',
                'order' => 15,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'teacher_absences',
                'name' => 'Absences Enseignants',
                'description' => 'Manage teacher absences',
                'icon' => '🕐',
                'route' => 'admin.teacher-absences.index',
                'order' => 16,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Common Sections
            [
                'code' => 'settings',
                'name' => 'Paramètres',
                'description' => 'System and school settings',
                'icon' => '⚙️',
                'route' => 'admin.settings.edit',
                'order' => 17,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'roles',
                'name' => 'Rôles & Permissions',
                'description' => 'Manage admin roles and permissions',
                'icon' => '🛡️',
                'route' => 'admin.roles.index',
                'order' => 18,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'reports',
                'name' => 'Rapports & Statistiques',
                'description' => 'View reports and analytics',
                'icon' => '📈',
                'route' => 'admin.reports.dashboard',
                'order' => 19,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($sections as $section) {
            DB::table('admin_role_sections')->insert($section);
        }
    }

    public function down(): void
    {
        DB::table('admin_role_sections')->truncate();
        DB::table('admin_specialized_roles')->truncate();
    }
};
