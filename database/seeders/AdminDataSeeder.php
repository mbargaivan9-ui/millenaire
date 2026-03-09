<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Fee;

class AdminDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test classes
        $classes = [];
        foreach (['6e', '5e', '4e', '3e', 'Tle', 'Tle D'] as $className) {
            $classes[] = Classe::create(['name' => $className, 'status' => 'active']);
        }

        // Create test students
        $studentCount = 0;
        foreach ($classes as $classe) {
            for ($i = 1; $i <= 30; $i++) {
                $user = User::create([
                    'name' => "Étudiant {$classe->name}-{$i}",
                    'email' => "student_{$classe->id}_{$i}@millennial.local",
                    'password' => bcrypt('password123'),
                    'role' => 'student'
                ]);

                Student::create([
                    'user_id' => $user->id,
                    'classe_id' => $classe->id,
                    'matricule' => sprintf("STD%06d", ++$studentCount),
                    'date_of_birth' => now()->subYears(rand(14, 20)),
                    'gender' => rand(0, 1) ? 'male' : 'female',
                    'phone' => "+237697" . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
                    'financial_status' => collect(['paid', 'pending', 'overdue'])->random(),
                ]);
            }
        }

        // Create subjects
        $subjects = [];
        foreach (['Mathématiques', 'Français', 'Anglais', 'Physique-Chimie', 'SVT', 'Informatique', 'Philosophie'] as $subjectName) {
            $subjects[] = Subject::create(['name' => $subjectName]);
        }

        // Create schedules
        foreach ($classes as $classe) {
            foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                for ($i = 0; $i < 3; $i++) {
                    $startHour = 7 + ($i * 2);
                    Schedule::create([
                        'classe_id' => $classe->id,
                        'subject_id' => $subjects[rand(0, count($subjects) - 1)]->id,
                        'teacher_id' => rand(1, 10),
                        'day_of_week' => $day,
                        'start_time' => sprintf("%02d:00", $startHour),
                        'end_time' => sprintf("%02d:00", $startHour + 1),
                        'room' => sprintf("Salle %d", rand(1, 20))
                    ]);
                }
            }
        }

        // Create attendance records
        $students = Student::all();
        foreach ($students->random(100) as $student) {
            for ($i = 0; $i < 20; $i++) {
                Attendance::create([
                    'student_id' => $student->id,
                    'date' => now()->subDays(rand(1, 90)),
                    'status' => collect(['present', 'absent', 'justified', 'ill'])->random(),
                    'notes' => rand(0, 1) ? "Note de test" : null,
                ]);
            }
        }

        // Create fees
        $fees = [
            ['name' => 'Frais d\'Inscription', 'amount' => 50000, 'is_mandatory' => true],
            ['name' => 'Frais de Scolarité', 'amount' => 250000, 'is_mandatory' => true],
            ['name' => 'Frais de Laboratoire', 'amount' => 25000, 'is_mandatory' => false],
            ['name' => 'Frais de Bibliothèque', 'amount' => 10000, 'is_mandatory' => false],
            ['name' => 'Frais d\'Examen', 'amount' => 15000, 'is_mandatory' => true],
        ];

        foreach ($fees as $fee) {
            Fee::create(array_merge($fee, [
                'description' => 'Frais scolaires de base',
                'status' => 'active',
                'due_date' => now()->addMonth()
            ]));
        }

        $this->command->info('Admin test data seeded successfully!');
    }
}
