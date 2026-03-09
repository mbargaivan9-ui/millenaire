<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\User;
use App\Models\Assignment;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $assignments = Assignment::with(['class', 'subject'])->get();

        if ($students->isEmpty() || $assignments->isEmpty()) {
            $this->command->warn('Veuillez d\'abord exécuter les seeders: UserSeeder, AssignmentSeeder');
            return;
        }

        foreach ($students as $student) {
            foreach ($assignments as $assignment) {
                // Only create grades for students in the assignment's class
                if ($student->class_id === $assignment->class_id) {
                    $homework = rand(8, 20);
                    $classwork = rand(8, 20);
                    $exam = rand(8, 20);
                    $average = ($homework + $classwork + $exam) / 3;
                    $status = $average >= 10 ? 'pass' : 'fail';

                    Grade::create([
                        'student_id' => $student->id,
                        'subject_id' => $assignment->subject_id,
                        'assignment_id' => $assignment->id,
                        'homework' => $homework,
                        'classwork' => $classwork,
                        'exam' => $exam,
                        'average' => round($average, 2),
                        'status' => $status,
                        'comment' => $this->randomComment($status),
                        'graded_by' => $assignment->prof_id,
                        'graded_at' => now()->subDays(rand(1, 30))
                    ]);
                }
            }
        }
    }

    private function randomComment($status)
    {
        if ($status === 'pass') {
            $comments = [
                'Bon travail !',
                'Résultats satisfaisants',
                'Progression positive',
                'Excellent effort',
                'À continuer ainsi'
            ];
        } else {
            $comments = [
                'Nécessite une révision',
                'Travail à améliorer',
                'Besoin d\'effort supplémentaire',
                'À revoir',
                'Progression insuffisante'
            ];
        }
        return $comments[array_rand($comments)];
    }
}
