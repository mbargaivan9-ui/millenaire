<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ════════════════════════════════════════════════
        // Define sections accessible by each role
        // ════════════════════════════════════════════════

        $rolePermissions = [
            // CENSEUR permissions
            'censeur' => [
                // Section Code => [can_create, can_read, can_update, can_delete, can_export]
                'classes' => [true, true, true, false, true],
                'students' => [true, true, true, false, true],
                'teachers' => [false, true, false, false, true],
                'subjects' => [false, true, false, false, true],
                'assignments' => [true, true, true, true, true],
                'attendance' => [true, true, true, false, true],
                'marks' => [false, true, true, false, true],
                'bulletins' => [false, true, true, false, true],
                'announcements' => [true, true, true, true, false],
                'reports' => [false, true, false, false, true],
            ],
            // INTENDANT permissions
            'intendant' => [
                'finance' => [true, true, true, false, true],
                'fees' => [true, true, true, false, true],
                'payments' => [false, true, false, false, true],
                'announcements' => [false, true, false, false, false],
                'reports' => [false, true, false, false, true],
            ],
            // SECRÉTAIRE permissions
            'secretaire' => [
                'schedule' => [true, true, true, false, true],
                'users' => [true, true, true, false, true],
                'students' => [false, true, false, false, true],
                'teachers' => [false, true, false, false, true],
                'announcements' => [true, true, true, false, false],
                'reports' => [false, true, false, false, true],
            ],
            // SURVEILLANT permissions
            'surveillant' => [
                'discipline' => [true, true, true, true, true],
                'attendance' => [false, true, false, false, true],
                'teacher_absences' => [false, true, true, false, true],
                'announcements' => [false, true, false, false, false],
                'reports' => [false, true, false, false, true],
            ],
        ];

        // Get roles and sections from database
        $roles = DB::table('admin_specialized_roles')->get()->keyBy('code');
        $sections = DB::table('admin_role_sections')->get()->keyBy('code');

        // Create assignments
        foreach ($rolePermissions as $roleCode => $sectionPerms) {
            $role = $roles[$roleCode] ?? null;
            if (!$role) continue;

            foreach ($sectionPerms as $sectionCode => $permissions) {
                $section = $sections[$sectionCode] ?? null;
                if (!$section) continue;

                DB::table('admin_role_section_assignments')->insert([
                    'admin_specialized_role_id' => $role->id,
                    'admin_role_section_id' => $section->id,
                    'permissions' => json_encode(['custom' => true]),
                    'can_create' => (bool)$permissions[0],
                    'can_read' => (bool)$permissions[1],
                    'can_update' => (bool)$permissions[2],
                    'can_delete' => (bool)$permissions[3],
                    'can_export' => (bool)$permissions[4],
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('admin_role_section_assignments')->truncate();
    }
};
