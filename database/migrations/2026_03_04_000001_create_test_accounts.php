<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete old test accounts if they exist
        DB::table('users')->whereIn('email', [
            'admin@millenaire.test',
            'teacher@millenaire.test',
            'parent@millenaire.test',
            'student@millenaire.test',
        ])->delete();

        // Create Admin Account
        $admin = DB::table('users')->insertGetId([
            'name' => 'Administrateur Test',
            'email' => 'admin@millenaire.test',
            'password' => Hash::make('admin@123'),
            'role' => 'admin',
            'is_active' => true,
            'gender' => 'M',
            'date_of_birth' => '1990-01-15',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Teacher Account
        $teacher = DB::table('users')->insertGetId([
            'name' => 'Professeur Test',
            'email' => 'teacher@millenaire.test',
            'password' => Hash::make('teacher@123'),
            'role' => 'teacher',
            'is_active' => true,
            'gender' => 'M',
            'date_of_birth' => '1985-06-20',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Teacher record
        if (Schema::hasTable('teachers')) {
            DB::table('teachers')->insertOrIgnore([
                'user_id' => $teacher,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create Parent Account
        $parent = DB::table('users')->insertGetId([
            'name' => 'Parent Test',
            'email' => 'parent@millenaire.test',
            'password' => Hash::make('parent@123'),
            'role' => 'parent',
            'is_active' => true,
            'gender' => 'M',
            'date_of_birth' => '1960-05-12',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Guardian record if table exists
        if (Schema::hasTable('guardians')) {
            DB::table('guardians')->insertOrIgnore([
                'user_id' => $parent,
                'profession' => 'Professeur',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create Student Account
        $student = DB::table('users')->insertGetId([
            'name' => 'Élève Test',
            'email' => 'student@millenaire.test',
            'password' => Hash::make('student@123'),
            'role' => 'student',
            'is_active' => true,
            'gender' => 'M',
            'date_of_birth' => '2008-09-15',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Student record
        if (Schema::hasTable('students')) {
            DB::table('students')->insertOrIgnore([
                'user_id' => $student,
                'matricule' => 'STU-' . str_pad($student, 6, '0', STR_PAD_LEFT),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->whereIn('email', [
            'admin@millenaire.test',
            'teacher@millenaire.test',
            'parent@millenaire.test',
            'student@millenaire.test',
        ])->delete();
    }
};
