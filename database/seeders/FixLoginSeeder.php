<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FixLoginSeeder extends Seeder
{
    public function run(): void
    {
        // Delete old test accounts with wrong domains
        User::whereIn('email', [
            'admin@millenaire.test',
            'teacher@millenaire.test',
            'parent@millenaire.test',
            'student@millenaire.test',
            'admin@millénaire.local',
            'teacher@millénaire.local',
            'parent@millénaire.local',
            'student@millénaire.local',
        ])->delete();

        // Create correct test accounts as per README
        $accounts = [
            [
                'name' => 'Admin',
                'email' => 'admin@millenaire.cm',
                'password' => 'Admin@2025!',
                'role' => 'admin'
            ],
            [
                'name' => 'Enseignant',
                'email' => 'teacher@millenaire.cm',
                'password' => 'Teacher@2025!',
                'role' => 'teacher'
            ],
            [
                'name' => 'Parent',
                'email' => 'parent@millenaire.cm',
                'password' => 'Parent@2025!',
                'role' => 'parent'
            ],
            [
                'name' => 'Élève',
                'email' => 'student@millenaire.cm',
                'password' => 'Student@2025!',
                'role' => 'student'
            ]
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'must_change_password' => false,
                    'two_factor_enabled' => false
                ]
            );
        }
    }
}
