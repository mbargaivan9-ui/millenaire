<?php

namespace Tests\Feature;

use App\Models\BulletinNgConfig;
use App\Models\BulletinNgSession;
use App\Models\BulletinNgStudent;
use App\Models\BulletinNgSubject;
use App\Models\BulletinNgNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TeacherGradeEntryTest
 * 
 * FEATURE TESTS for TeacherGradeEntryController
 * Tests the real-time grade entry system for teachers
 */
class TeacherGradeEntryTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected User $profPrincipal;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->teacher = User::factory()->create([
            'is_prof_principal' => false,
            'name' => 'Teacher',
        ]);
        
        $this->profPrincipal = User::factory()->create([
            'is_prof_principal' => true,
            'name' => 'Prof Principal',
        ]);
    }

    /**
     * Test: index() - Teacher sees visible sessions
     */
    public function test_teacher_grade_dashboard_shows_visible_sessions()
    {
        // Create a config and make session visible to teachers
        $config = BulletinNgConfig::create([
            'prof_principal_id' => $this->profPrincipal->id,
            'nom_classe' => '3ème A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);

        $subject = BulletinNgSubject::create([
            'config_id' => $config->id,
            'nom' => 'Mathématiques',
            'coefficient' => 2,
            'user_id' => $this->teacher->id, // Assign to this teacher
        ]);

        $session = BulletinNgSession::create([
            'config_id' => $config->id,
            'trimestre_number' => 1,
            'sequence_number' => 1,
            'statut' => 'saisie_ouverte',
            'visible_depuis' => now(),
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.grades.bulletin_ng.index'));

        $response->assertStatus(200);
        $sessions = $response->viewData('sessions');
        $this->assertTrue($sessions->contains('id', $session->id));
    }

    /**
     * Test: index() - Teacher cannot see sessions not assigned to them
     */
    public function test_teacher_cannot_see_unassigned_sessions()
    {
        // Create config with subject NOT assigned to this teacher
        $otherTeacher = User::factory()->create();
        $config = BulletinNgConfig::create([
            'prof_principal_id' => $this->profPrincipal->id,
            'nom_classe' => '3ème A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);

        BulletinNgSubject::create([
            'config_id' => $config->id,
            'nom' => 'Mathématiques',
            'coefficient' => 2,
            'user_id' => $otherTeacher->id, // Assigned to different teacher
        ]);

        $session = BulletinNgSession::create([
            'config_id' => $config->id,
            'trimestre_number' => 1,
            'sequence_number' => 1,
            'statut' => 'saisie_ouverte',
            'visible_depuis' => now(),
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.grades.bulletin_ng.index'));

        $response->assertStatus(200);
        $sessions = $response->viewData('sessions');
        $this->assertFalse($sessions->contains('id', $session->id));
    }

    /**
     * Test: editForm() - Teacher sees grade entry grid
     */
    public function test_grade_entry_form_shows_grid()
    {
        $config = BulletinNgConfig::create([
            'prof_principal_id' => $this->profPrincipal->id,
            'nom_classe' => '3ème A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);

        $subject = BulletinNgSubject::create([
            'config_id' => $config->id,
            'nom' => 'Mathématiques',
            'coefficient' => 2,
            'user_id' => $this->teacher->id,
        ]);

        $student = BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Student',
            'matricule' => 'S001',
            'sexe' => 'M',
        ]);

        $session = BulletinNgSession::create([
            'config_id' => $config->id,
            'trimestre_number' => 1,
            'sequence_number' => 1,
            'statut' => 'saisie_ouverte',
            'visible_depuis' => now(),
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.grades.bulletin_ng.edit-form', $session->id));

        $response->assertStatus(200);
        $response->assertViewHas(['session', 'students', 'subjects']);
    }

    /**
     * Test: saveGrade() - AJAX grade save
     */
    public function test_teacher_can_save_grade()
    {
        $config = BulletinNgConfig::create([
            'prof_principal_id' => $this->profPrincipal->id,
            'nom_classe' => '3ème A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);

        $subject = BulletinNgSubject::create([
            'config_id' => $config->id,
            'nom' => 'Mathématiques',
            'coefficient' => 2,
            'user_id' => $this->teacher->id,
        ]);

        $student = BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Student',
            'matricule' => 'S001',
            'sexe' => 'M',
        ]);

        $session = BulletinNgSession::create([
            'config_id' => $config->id,
            'trimestre_number' => 1,
            'sequence_number' => 1,
            'statut' => 'saisie_ouverte',
            'visible_depuis' => now(),
        ]);

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.grades.bulletin_ng.save-grade', $session->id), [
                'ng_student_id' => $student->id,
                'ng_subject_id' => $subject->id,
                'note' => 17,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify note was saved
        $this->assertDatabaseHas('bulletin_ng_notes', [
            'ng_student_id' => $student->id,
            'ng_subject_id' => $subject->id,
            'note' => 17,
        ]);
    }

    /**
     * Test: saveGrade() - Teacher cannot save if entry closed
     */
    public function test_teacher_cannot_save_if_entry_closed()
    {
        $config = BulletinNgConfig::create([
            'prof_principal_id' => $this->profPrincipal->id,
            'nom_classe' => '3ème A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);

        $subject = BulletinNgSubject::create([
            'config_id' => $config->id,
            'nom' => 'Mathématiques',
            'coefficient' => 2,
            'user_id' => $this->teacher->id,
        ]);

        $student = BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Student',
            'matricule' => 'S001',
            'sexe' => 'M',
        ]);

        // Session is CLOSED
        $session = BulletinNgSession::create([
            'config_id' => $config->id,
            'trimestre_number' => 1,
            'sequence_number' => 1,
            'statut' => 'saisie_fermee', // Closed
            'visible_depuis' => now(),
        ]);

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.grades.bulletin_ng.save-grade', $session->id), [
                'ng_student_id' => $student->id,
                'ng_subject_id' => $subject->id,
                'note' => 17,
            ]);

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test: getProgress() - Returns completion stats
     */
    public function test_progress_endpoint_returns_stats()
    {
        $config = BulletinNgConfig::create([
            'prof_principal_id' => $this->profPrincipal->id,
            'nom_classe' => '3ème A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);

        $subject = BulletinNgSubject::create([
            'config_id' => $config->id,
            'nom' => 'Mathématiques',
            'coefficient' => 2,
            'user_id' => $this->teacher->id,
        ]);

        BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Student 1',
            'matricule' => 'S001',
            'sexe' => 'M',
        ]);
        BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Student 2',
            'matricule' => 'S002',
            'sexe' => 'F',
        ]);

        $session = BulletinNgSession::create([
            'config_id' => $config->id,
            'trimestre_number' => 1,
            'sequence_number' => 1,
            'statut' => 'saisie_ouverte',
            'visible_depuis' => now(),
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.grades.bulletin_ng.get-progress', $session->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'completion_percent',
            'notes_count',
            'max_notes',
        ]);
    }

    /**
     * Test: Non-teacher cannot save grades
     */
    public function test_non_teacher_cannot_save_grade()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $config = BulletinNgConfig::create([
            'prof_principal_id' => $this->profPrincipal->id,
            'nom_classe' => '3ème A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);

        BulletinNgSubject::create([
            'config_id' => $config->id,
            'nom' => 'Math',
            'coefficient' => 1,
            'user_id' => $this->teacher->id,
        ]);

        $student = BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Student',
            'matricule' => 'S001',
            'sexe' => 'M',
        ]);

        $session = BulletinNgSession::create([
            'config_id' => $config->id,
            'trimestre_number' => 1,
            'sequence_number' => 1,
            'statut' => 'saisie_ouverte',
            'visible_depuis' => now(),
        ]);

        // Admin tries to save (shouldn't work)
        $response = $this->actingAs($admin)
            ->post(route('teacher.grades.bulletin_ng.save-grade', $session->id), [
                'ng_student_id' => $student->id,
                'ng_subject_id' => 999,
                'note' => 15,
            ]);

        // Should fail authorization
        $response->assertStatus(403);
    }

    /**
     * Test: Teachers can only edit notes for their subjects
     */
    public function test_teacher_can_only_edit_own_subjects()
    {
        $otherTeacher = User::factory()->create();

        $config = BulletinNgConfig::create([
            'prof_principal_id' => $this->profPrincipal->id,
            'nom_classe' => '3ème A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);

        // Subject assigned to OTHER teacher
        $subject = BulletinNgSubject::create([
            'config_id' => $config->id,
            'nom' => 'Mathématiques',
            'coefficient' => 2,
            'user_id' => $otherTeacher->id,
        ]);

        $student = BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Student',
            'matricule' => 'S001',
            'sexe' => 'M',
        ]);

        $session = BulletinNgSession::create([
            'config_id' => $config->id,
            'trimestre_number' => 1,
            'sequence_number' => 1,
            'statut' => 'saisie_ouverte',
            'visible_depuis' => now(),
        ]);

        // THIS teacher tries to save for OTHER teacher's subject
        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.grades.bulletin_ng.save-grade', $session->id), [
                'ng_student_id' => $student->id,
                'ng_subject_id' => $subject->id,
                'note' => 15,
            ]);

        // Should fail - not authorized
        $response->assertStatus(403);
    }
}
