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

    /**
     * Test : Prof principal peut accéder à la page des bulletins
     */
    public function test_professor_principal_can_access_report_cards_page(): void
    {
        $response = $this->actingAs($this->profPrincipalUser)
            ->get(route('teacher.report-cards'));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.report-cards.index');
        $response->assertViewHas('reportCards');
    }

    /**
     * Test : Prof principal peut voir les bulletins de sa classe
     */
    public function test_professor_principal_can_view_report_card(): void
    {
        $response = $this->actingAs($this->profPrincipalUser)
            ->get(route('teacher.report-cards.show', $this->reportCard));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.report-cards.show');
        $response->assertViewHas('reportCard');
    }

    /**
     * Test : Prof principal peut éditer un bulletin
     */
    public function test_professor_principal_can_edit_report_card(): void
    {
        $response = $this->actingAs($this->profPrincipalUser)
            ->get(route('teacher.report-cards.edit', $this->reportCard));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.report-cards.edit');
    }

    /**
     * Test : Prof principal peut mettre à jour un bulletin
     */
    public function test_professor_principal_can_update_report_card(): void
    {
        $response = $this->actingAs($this->profPrincipalUser)
            ->put(route('teacher.report-cards.update', $this->reportCard), [
                'appreciation' => 'Très bon étudiant',
                'behavior_comment' => 'Comportement excellent',
            ]);

        $response->assertRedirect(route('teacher.report-cards.show', $this->reportCard));
        
        $this->reportCard->refresh();
        $this->assertEquals('Très bon étudiant', $this->reportCard->appreciation);
        $this->assertEquals('Comportement excellent', $this->reportCard->behavior_comment);
    }

    /**
     * Test : Prof principal peut télécharger un bulletin en PDF
     */
    public function test_professor_principal_can_download_report_card_pdf(): void
    {
        $response = $this->actingAs($this->profPrincipalUser)
            ->get(route('teacher.report-cards.pdf', $this->reportCard));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test : Un prof ordinaire ne peut pas accéder aux bulletins
     */
    public function test_regular_teacher_cannot_access_report_cards(): void
    {
        // Créer un prof ordinaire (pas prof_principal)
        $regularTeacherUser = User::factory()->create([
            'role' => 'teacher',
            'email' => 'regular_teacher@test.com',
        ]);
        
        Teacher::factory()->create([
            'user_id' => $regularTeacherUser->id,
            'is_prof_principal' => false,
        ]);

        $response = $this->actingAs($regularTeacherUser)
            ->get(route('teacher.report-cards'));

        $response->assertStatus(403);
    }

    /**
     * Test : Un utilisateur non authentifié est redirigé vers login
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('teacher.report-cards'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test : Un parent ne peut pas accéder aux bulletins
     */
    public function test_parent_cannot_access_report_cards(): void
    {
        $parentUser = User::factory()->create(['role' => 'parent']);

        $response = $this->actingAs($parentUser)
            ->get(route('teacher.report-cards'));

        $response->assertStatus(403);
    }

    /**
     * Test : Admin peut accéder à tous les bulletins
     */
    public function test_admin_can_access_any_report_cards(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($adminUser)
            ->get(route('teacher.report-cards'));

        $response->assertStatus(200);
    }

    /**
     * Test : Prof principal ne peut accéder qu'à ses propres bulletins
     */
    public function test_professor_principal_cannot_access_other_class_report_cards(): void
    {
        // Créer une autre classe
        $otherClass = Classe::factory()->create();
        
        // Créer un étudiant dans l'autre classe
        $otherStudentUser = User::factory()->create(['role' => 'student']);
        $otherStudent = Student::factory()->create([
            'user_id' => $otherStudentUser->id,
            'classe_id' => $otherClass->id,
        ]);

        // Créer un bulletin pour l'autre classe
        $otherReportCard = ReportCard::factory()->create([
            'student_id' => $otherStudent->id,
            'class_id' => $otherClass->id,
        ]);

        // Vérifier que le prof principal ne peut pas accéder au bulletin de l'autre classe
        $response = $this->actingAs($this->profPrincipalUser)
            ->get(route('teacher.report-cards.show', $otherReportCard));

        $response->assertStatus(403);
    }
}
