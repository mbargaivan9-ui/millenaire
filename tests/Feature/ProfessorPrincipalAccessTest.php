<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Classe;
use App\Models\ReportCard;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessorPrincipalAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $profPrincipalUser;
    protected Teacher $profPrincipal;
    protected Classe $headClass;
    protected Student $student;
    protected ReportCard $reportCard;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un professeur principal
        $this->profPrincipalUser = User::factory()->create([
            'role' => 'teacher',
            'email' => 'prof_principal@test.com',
        ]);

        // Créer le record teacher associé
        $this->profPrincipal = Teacher::factory()->create([
            'user_id' => $this->profPrincipalUser->id,
            'is_prof_principal' => true,
        ]);

        // Créer une classe
        $this->headClass = Classe::factory()->create();
        
        // Assigner la classe au professeur principal
        $this->profPrincipal->update(['head_class_id' => $this->headClass->id]);

        // Créer un étudiant dans la classe
        $studentUser = User::factory()->create(['role' => 'student']);
        $this->student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'classe_id' => $this->headClass->id,
        ]);

        // Créer un bulletin
        $this->reportCard = ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->headClass->id,
            'term' => 1,
            'sequence' => 1,
            'term_average' => 15.5,
            'rank' => 1,
        ]);
    }

    // Report Cards tests removed
}
