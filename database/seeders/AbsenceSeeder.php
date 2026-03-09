<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Absence;
use App\Models\User;
use App\Models\Subject;

class AbsenceSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $subjects = Subject::all();

        if ($students->isEmpty() || $subjects->isEmpty()) {
            $this->command->warn('Veuillez d\'abord exécuter les seeders: UserSeeder, SubjectSeeder');
            return;
        }

        $types = ['absence', 'late', 'justified'];

        foreach ($students as $student) {
            // Create 5-15 absences per student
            for ($i = 0; $i < rand(5, 15); $i++) {
                Absence::create([
                    'student_id' => $student->id,
                    'subject_id' => $subjects->random()->id,
                    'date' => now()->subDays(rand(1, 60)),
                    'type' => $types[array_rand($types)],
                    'reason' => $this->randomReason(),
                    'comment' => 'Absence marquée par le professeur'
                ]);
            }
        }
    }

    private function randomReason()
    {
        $reasons = [
            'Maladie',
            'Raison familiale',
            'Rendez-vous médical',
            'Transport',
            'Raison personnelle',
            'Justifiée par les parents',
            'Accident',
            'Cas de force majeure'
        ];
        return $reasons[array_rand($reasons)];
    }
}
