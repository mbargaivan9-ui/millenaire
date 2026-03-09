<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = User::where('role', 'teacher')->get();
        $classes = Classes::all();
        $subjects = Subject::all();

        if ($teachers->isEmpty() || $classes->isEmpty() || $subjects->isEmpty()) {
            $this->command->warn('Veuillez d\'abord exécuter les seeders: RolePermissionSeeder, UserSeeder, ClassSeeder, SubjectSeeder');
            return;
        }

        $schedules = ['08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00', '13:00-14:00', '14:00-15:00', '15:00-16:00'];
        $rooms = ['A101', 'A102', 'A103', 'B101', 'B102', 'B103', 'C101', 'C102'];

        // Assign each class 8-10 subjects with different teachers
        foreach ($classes as $class) {
            $selectedSubjects = $subjects->random(rand(8, 10));
            
            foreach ($selectedSubjects as $subject) {
                $teacher = $teachers->random();
                
                Assignment::create([
                    'prof_id' => $teacher->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'schedule' => $schedules[array_rand($schedules)],
                    'room' => $rooms[array_rand($rooms)],
                    'notes' => 'Affectation pour classe ' . $class->name,
                    'is_active' => true
                ]);
            }
        }
    }
}
