<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\ClassSubjectTeacher;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Discipline;
use App\Models\Payment;
use App\Models\Assignment;
use App\Models\Announcement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ComprehensiveDataSeeder extends Seeder
{
    /**
     * Seed comprehensive test data for all models
     */
    public function run(): void
    {
        echo "\n========== SEEDING COMPREHENSIVE TEST DATA ==========\n\n";

        // Get the class
        $class = Classe::first() ?? Classe::create(['name' => '6e A', 'level' => 6]);
        echo "✓ Using class: {$class->name}\n";

        // ==================== TEACHERS ====================
        echo "\n📚 Creating Teachers...\n";

        $teachers = [];
        $teacherEmails = [
            'prof.mathematiques@millenniaire.test' => 'Mr. Emmanuel DONGMO',
            'prof.francais@millenniaire.test' => 'Mme ABANDA Marie',
            'prof.physique@millenniaire.test' => 'Mr. Alain NKODO',
        ];

        foreach ($teacherEmails as $email => $name) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $teacher = Teacher::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'registration_number' => 'PROF-' . str_pad(count($teachers) + 1, 3, '0', STR_PAD_LEFT),
                        'specialization' => 'Teaching',
                        'years_of_experience' => rand(1, 10),
                        'is_principal' => $email === 'prof.francais@millenniaire.test',
                    ]
                );
                $teachers[] = $teacher;
                echo "✓ Teacher: {$user->name} (ID: {$teacher->id})\n";
            }
        }

        // ==================== GUARDIANS ====================
        echo "\n👨‍👩‍👧 Creating Guardians...\n";

        $guardians = [];
        $guardianEmails = [
            'parent1@millenniaire.test' => 'Mr. KOUADJE Ignace',
            'parent2@millenniaire.test' => 'Mme BIHINA CHARLOTTE',
        ];

        foreach ($guardianEmails as $email => $name) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $guardian = Guardian::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'relationship' => 'Parent',
                        'occupation' => 'Professional',
                        'phone' => $user->phoneNumber,
                    ]
                );
                $guardians[] = $guardian;
                echo "✓ Guardian: {$user->name} (ID: {$guardian->id})\n";
            }
        }

        // ==================== STUDENTS ====================
        echo "\n👨‍🎓 Creating Students...\n";

        $students = [];
        $studentEmails = [
            'student1@millenniaire.test' => 0,
            'student2@millenniaire.test' => 1,
            'student3@millenniaire.test' => -1,
            'student4@millenniaire.test' => 1,
            'student5@millenniaire.test' => -1,
        ];

        $studentIndex = 0;
        foreach ($studentEmails as $email => $guardianIndex) {
            $studentIndex++;
            $user = User::where('email', $email)->first();
            if ($user) {
                $student = Student::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'classe_id' => $class->id,
                        'registration_number' => 'STU-' . str_pad($studentIndex, 6, '0', STR_PAD_LEFT),
                        'financial_status' => 'up_to_date',
                        'admission_date' => now()->subMonths(6),
                    ]
                );
                $students[] = $student;

                // Assign guardian
                if ($guardianIndex >= 0 && isset($guardians[$guardianIndex])) {
                    $student->guardians()->attach($guardians[$guardianIndex]->id, [
                        'relationship' => 'Parent',
                    ]);
                }

                echo "✓ Student: {$user->name} ({$student->registration_number})\n";
            }
        }

        // ==================== SUBJECTS ====================
        echo "\n📖 Linking Subjects to Class...\n";

        $subjects = Subject::all();
        if ($subjects->count() > 0) {
            foreach ($subjects->take(3) as $subject) {
                foreach ($teachers->take(3) as $index => $teacher) {
                    ClassSubjectTeacher::firstOrCreate(
                        [
                            'classe_id' => $class->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacher->id,
                        ],
                        [
                            'is_primary' => $index === 0,
                        ]
                    );
                }
            }
            echo "✓ Linked " . min(3, $subjects->count()) . " subjects to teachers\n";
        }

        // ==================== SCHEDULES ====================
        echo "\n⏰ Creating Schedules...\n";

        foreach ($subjects->take(3) as $index => $subject) {
            Schedule::firstOrCreate(
                [
                    'classe_id' => $class->id,
                    'subject_id' => $subject->id,
                    'day_of_week' => ['Monday', 'Tuesday', 'Wednesday'][$index] ?? 'Monday',
                ],
                [
                    'start_time' => '08:' . str_pad($index * 15, 2, '0', STR_PAD_LEFT),
                    'end_time' => '09:' . str_pad($index * 15, 2, '0', STR_PAD_LEFT),
                    'room' => 'Room ' . (101 + $index),
                ]
            );
        }
        echo "✓ Created schedules for " . min(3, $subjects->count()) . " subjects\n";

        // ==================== GRADES ====================
        echo "\n📊 Creating Grades...\n";

        foreach ($students as $student) {
            foreach ($subjects->take(3) as $subject) {
                Grade::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'score' => rand(10, 20),
                        'grading_period' => 'Q1',
                        'recorded_by' => $teachers[0]->user_id,
                    ]
                );
            }
        }
        echo "✓ Created grades for all students\n";

        // ==================== ATTENDANCE ====================
        echo "\n✅ Creating Attendance Records...\n";

        foreach ($students as $student) {
            for ($i = 0; $i < 10; $i++) {
                Attendance::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'classe_id' => $class->id,
                        'date' => now()->subDays($i),
                    ],
                    [
                        'status' => rand(0, 10) > 1 ? 'present' : 'absent',
                        'recorded_by' => $teachers[0]->user_id,
                    ]
                );
            }
        }
        echo "✓ Created attendance records\n";

        // ==================== DISCIPLINE ====================
        echo "\n⚠️ Creating Discipline Records...\n";

        foreach ($students->take(2) as $student) {
            Discipline::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'classe_id' => $class->id,
                ],
                [
                    'incident_date' => now()->subDays(5),
                    'incident_description' => 'Minor behavior issue',
                    'severity_level' => 'low',
                    'recorded_by' => $teachers[0]->user_id,
                ]
            );
        }
        echo "✓ Created discipline records\n";

        // ==================== PAYMENTS ====================
        echo "\n💰 Creating Payment Records...\n";

        foreach ($students as $index => $student) {
            Payment::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'payment_period' => 'Q1',
                ],
                [
                    'amount' => 50000,
                    'payment_date' => $index % 2 === 0 ? now()->subDays(10) : null,
                    'status' => $index % 2 === 0 ? 'completed' : 'pending',
                ]
            );
        }
        echo "✓ Created payment records\n";

        // ==================== ASSIGNMENTS ====================
        echo "\n📝 Creating Assignments...\n";

        foreach ($subjects->take(3) as $subject) {
            Assignment::firstOrCreate(
                [
                    'subject_id' => $subject->id,
                    'classe_id' => $class->id,
                ],
                [
                    'title' => 'Assignment for ' . $subject->name,
                    'description' => 'Complete the exercises on page 45-50',
                    'due_date' => now()->addDays(7),
                    'max_score' => 20,
                ]
            );
        }
        echo "✓ Created assignments\n";

        // ==================== ANNOUNCEMENTS ====================
        echo "\n📢 Creating Announcements...\n";

        Announcement::firstOrCreate(
            ['title' => 'Welcome to School'],
            [
                'content' => 'Welcome to the new school year. We are excited to have everyone here.',
                'authored_by' => $teachers[0]->user_id,
                'is_published' => true,
            ]
        );
        echo "✓ Created announcements\n";

        echo "\n========== ✅ DATA SEEDING COMPLETE ==========\n\n";
        echo "📊 SUMMARY:\n";
        echo "  • Teachers: " . Teacher::count() . "\n";
        echo "  • Guardians: " . Guardian::count() . "\n";
        echo "  • Students: " . Student::count() . "\n";
        echo "  • Classes: " . Classe::count() . "\n";
        echo "  • Grades: " . Grade::count() . "\n";
        echo "  • Attendance: " . Attendance::count() . "\n";
        echo "  • Schedules: " . Schedule::count() . "\n";
        echo "  • Disciplines: " . Discipline::count() . "\n";
        echo "  • Payments: " . Payment::count() . "\n";
        echo "  • Assignments: " . Assignment::count() . "\n";
        echo "  • Announcements: " . Announcement::count() . "\n";
        echo "\n";
    }
}
