<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use App\Models\AdminSpecializedRole;
use App\Models\UserSpecializedRoleAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * SpecializedRolesSeeder
 * Creates test users with specialized admin roles
 */
class SpecializedRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Get specialized roles
        $censeur = AdminSpecializedRole::where('code', 'censeur')->first();
        $intendant = AdminSpecializedRole::where('code', 'intendant')->first();
        $secretaire = AdminSpecializedRole::where('code', 'secretaire')->first();
        $surveillant = AdminSpecializedRole::where('code', 'surveillant')->first();

        // Test users data
        $testUsers = [
            [
                'name' => 'Jean Pierre Censeur',
                'email' => 'censeur@millenaire.test',
                'password' => 'Censeur@123',
                'role' => 'teacher',
                'specialized_role' => $censeur,
                'description' => 'Test Censeur account',
            ],
            [
                'name' => 'Marie Intendante',
                'email' => 'intendant@millenaire.test',
                'password' => 'Intendant@123',
                'role' => 'teacher',
                'specialized_role' => $intendant,
                'description' => 'Test Intendant account',
            ],
            [
                'name' => 'Sophie Secrétaire',
                'email' => 'secretaire@millenaire.test',
                'password' => 'Secretaire@123',
                'role' => 'teacher',
                'specialized_role' => $secretaire,
                'description' => 'Test Secrétaire account',
            ],
            [
                'name' => 'Paul Surveillant',
                'email' => 'surveillant@millenaire.test',
                'password' => 'Surveillant@123',
                'role' => 'teacher',
                'specialized_role' => $surveillant,
                'description' => 'Test Surveillant Général account',
            ],
        ];

        foreach ($testUsers as $userData) {
            $specializedRole = $userData['specialized_role'];
            unset($userData['specialized_role'], $userData['description']);

            // Create or update user
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'preferred_language' => 'fr',
                    'is_active' => true,
                    'must_change_password' => false,
                ]
            );

            // Create teacher record if doesn't exist
            if (!$user->teacher()->exists()) {
                Teacher::create([
                    'user_id' => $user->id,
                    'is_active' => true,
                ]);
            }

            // Assign specialized role with all sections from the role
            $existingAssignment = UserSpecializedRoleAssignment::where('user_id', $user->id)
                ->where('deactivated_at', null)
                ->first();

            if (!$existingAssignment) {
                UserSpecializedRoleAssignment::create([
                    'user_id' => $user->id,
                    'admin_specialized_role_id' => $specializedRole->id,
                    'assigned_sections' => null, // All sections from role
                    'notes' => "Test account for {$specializedRole->name}",
                    'assigned_by_id' => 1, // Admin user
                    'assigned_at' => now(),
                ]);
            }

            echo "✓ Created specialized user: {$user->name} ({$specializedRole->name})\n";
        }

        echo "\n✅ Specialized roles seeding completed!\n";
        echo "Test Credentials:\n";
        echo "- Censeur: censeur@millenaire.test / Censeur@123\n";
        echo "- Intendant: intendant@millenaire.test / Intendant@123\n";
        echo "- Secrétaire: secretaire@millenaire.test / Secretaire@123\n";
        echo "- Surveillant: surveillant@millenaire.test / Surveillant@123\n";
    }
}
