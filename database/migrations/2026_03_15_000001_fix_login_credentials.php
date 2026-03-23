<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    public function up(): void
    {
        // Delete all test/demo accounts with wrong domains/credentials
        DB::table('users')->whereIn('email', [
            'admin@millenaire.test',
            'teacher@millenaire.test',
            'parent@millenaire.test',
            'student@millenaire.test',
            'admin@millénaire.local',
            'teacher1@millénaire.local',
            'teacher2@millénaire.local',
            'teacher3@millénaire.local',
            'teacher4@millénaire.local',
            'teacher5@millénaire.local',
            'teacher6@millénaire.local',
            'teacher7@millénaire.local',
            'teacher8@millénaire.local',
            'teacher9@millénaire.local',
            'teacher10@millénaire.local',
        ])->delete();

        // Ensure proper test accounts exist with correct credentials from README
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@millenaire.cm'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@2025!'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'must_change_password' => false,
                'two_factor_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'teacher@millenaire.cm'],
            [
                'name' => 'Enseignant',
                'password' => Hash::make('Teacher@2025!'),
                'role' => 'teacher',
                'is_active' => true,
                'email_verified_at' => now(),
                'must_change_password' => false,
                'two_factor_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'parent@millenaire.cm'],
            [
                'name' => 'Parent',
                'password' => Hash::make('Parent@2025!'),
                'role' => 'parent',
                'is_active' => true,
                'email_verified_at' => now(),
                'must_change_password' => false,
                'two_factor_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'student@millenaire.cm'],
            [
                'name' => 'Élève',
                'password' => Hash::make('Student@2025!'),
                'role' => 'student',
                'is_active' => true,
                'email_verified_at' => now(),
                'must_change_password' => false,
                'two_factor_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('users')->whereIn('email', [
            'admin@millenaire.cm',
            'teacher@millenaire.cm',
            'parent@millenaire.cm',
            'student@millenaire.cm',
        ])->delete();
    }
};
