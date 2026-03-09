<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [
            ['name' => 'admin', 'description' => 'Administrateur système'],
            ['name' => 'teacher', 'description' => 'Professeur'],
            ['name' => 'parent', 'description' => 'Parent d\'élève'],
            ['name' => 'student', 'description' => 'Élève'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        // Create permissions
        $permissions = [
            // User management
            ['name' => 'users.view', 'description' => 'Voir les utilisateurs'],
            ['name' => 'users.create', 'description' => 'Créer des utilisateurs'],
            ['name' => 'users.edit', 'description' => 'Éditer les utilisateurs'],
            ['name' => 'users.delete', 'description' => 'Supprimer les utilisateurs'],
            
            // Class management
            ['name' => 'classes.view', 'description' => 'Voir les classes'],
            ['name' => 'classes.create', 'description' => 'Créer des classes'],
            ['name' => 'classes.edit', 'description' => 'Éditer les classes'],
            ['name' => 'classes.delete', 'description' => 'Supprimer les classes'],
            
            // Grades
            ['name' => 'grades.view', 'description' => 'Voir les notes'],
            ['name' => 'grades.create', 'description' => 'Créer des notes'],
            ['name' => 'grades.edit', 'description' => 'Éditer les notes'],
            
            // Finance
            ['name' => 'finance.view', 'description' => 'Voir les finances'],
            ['name' => 'finance.manage', 'description' => 'Gérer les finances'],
            
            // Announcements
            ['name' => 'announcements.view', 'description' => 'Voir les annonces'],
            ['name' => 'announcements.create', 'description' => 'Créer des annonces'],
            ['name' => 'announcements.manage', 'description' => 'Gérer les annonces'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
