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
 * BulletinNgControllerTest
 * 
 * FEATURE TESTS for BulletinNgController methods
 * Tests individual controller actions and their responses
 */
class BulletinNgControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $profPrincipal;
    protected User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->profPrincipal = User::factory()->create([
            'is_prof_principal' => true,
            'name' => 'Prof. Principal',
        ]);
        
        $this->teacher = User::factory()->create([
            'is_prof_principal' => false,
            'name' => 'Regular Teacher',
        ]);
    }

    /**
     * Test: index() - Professor can see their configs
     */
    public function test_index_shows_prof_configs()
    {
        // Create a config for this prof
        BulletinNgConfig::create([
            'prof_principal_id' => $this->profPrincipal->id,
            'nom_classe' => '3ème A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);

        $response = $this->actingAs($this->profPrincipal)
            ->get(route('teacher.bulletin_ng.index'));

        $response->assertStatus(200);
        $response->assertViewHas('configs');
        $configs = $response->viewData('configs');
        $this->assertCount(1, $configs);
    }

    /**
     * Test: index() - No configs shows empty list
     */
    public function test_index_shows_empty_when_no_configs()
    {
        $response = $this->actingAs($this->profPrincipal)
            ->get(route('teacher.bulletin_ng.index'));

        $response->assertStatus(200);
        $configs = $response->viewData('configs');
        $this->assertCount(0, $configs);
    }

    /**
     * Test: storeConfig() validation - missing required fields
     */
    public function test_store_config_validation_fails_missing_fields()
    {
        $response = $this->actingAs($this->profPrincipal)
            ->post(route('teacher.bulletin_ng.store-config'), [
                'langue' => 'FR',
                // Missing required fields
            ]);

        $response->assertStatus(422); // Validation error
    }

    /**
     * Test: storeConfig() validation - invalid language
     */
    public function test_store_config_validation_invalid_language()
    {
        $response = $this->actingAs($this->profPrincipal)
            ->post(route('teacher.bulletin_ng.store-config'), [
                'langue' => 'ES', // Invalid
                'school_name' => 'School',
                'nom_classe' => '3A',
                'effectif' => 45,
                'trimestre' => 1,
                'sequence' => 1,
                'annee_academique' => '2025-2026',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test: step3Subjects() - View has correct data
     */
    public function test_step3_subjects_view_shows_subjects()
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

        // Add some subjects
        BulletinNgSubject::create([
            'config_id' => $config->id,
            'nom' => 'Mathématiques',
            'coefficient' => 2,
        ]);

        $response = $this->actingAs($this->profPrincipal)
            ->get(route('teacher.bulletin_ng.step3', $config->id));

        $response->assertStatus(200);
        $response->assertViewHas('config', $config);
        $response->assertViewHas('subjects');
    }

    /**
     * Test: storeSubjects() - Updates existing subjects
     */
    public function test_store_subjects_updates_existing()
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
            'nom' => 'Old Name',
            'coefficient' => 1,
        ]);

        // Update the subject
        $response = $this->actingAs($this->profPrincipal)
            ->post(route('teacher.bulletin_ng.store-subjects', $config->id), [
                'subjects' => [
                    [
                        'id' => $subject->id,
                        'nom' => 'New Name',
                        'coefficient' => 2,
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify update
        $subject->refresh();
        $this->assertEquals('New Name', $subject->nom);
        $this->assertEquals(2, $subject->coefficient);
    }

    /**
     * Test: step4Students() - shows students
     */
    public function test_step4_students_lists_students()
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

        BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Test Student',
            'matricule' => 'S001',
            'sexe' => 'M',
        ]);

        $response = $this->actingAs($this->profPrincipal)
            ->get(route('teacher.bulletin_ng.step4', $config->id));

        $response->assertStatus(200);
        $response->assertViewHas('students');
        $students = $response->viewData('students');
        $this->assertCount(1, $students);
    }

    /**
     * Test: storeStudent() - Adds student with validation
     */
    public function test_store_student_validation()
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

        // Missing matricule
        $response = $this->actingAs($this->profPrincipal)
            ->post(route('teacher.bulletin_ng.students.store', $config->id), [
                'nom' => 'Student',
                'sexe' => 'M',
                // Missing matricule
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test: deleteStudent() - Removes student
     */
    public function test_delete_student_removes_record()
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

        $student = BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Delete Me',
            'matricule' => 'S999',
            'sexe' => 'M',
        ]);

        $response = $this->actingAs($this->profPrincipal)
            ->delete(route('teacher.bulletin_ng.students.delete', [$config->id, $student->id]));

        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('bulletin_ng_students', ['id' => $student->id]);
    }

    /**
     * Test: saveNote() validation - invalid note range
     */
    public function test_save_note_validation_invalid_range()
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
            'nom' => 'Math',
            'coefficient' => 1,
        ]);

        $student = BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Student',
            'matricule' => 'S001',
            'sexe' => 'M',
        ]);

        // Try to save note > 20
        $response = $this->actingAs($this->profPrincipal)
            ->post(route('teacher.bulletin_ng.save-note', $config->id), [
                'ng_student_id' => $student->id,
                'ng_subject_id' => $subject->id,
                'note' => 25, // Invalid
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test: step5Notes() - renders grade entry grid
     */
    public function test_step5_notes_shows_grid()
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

        BulletinNgSubject::create([
            'config_id' => $config->id,
            'nom' => 'Math',
            'coefficient' => 1,
        ]);

        BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Student',
            'matricule' => 'S001',
            'sexe' => 'M',
        ]);

        $response = $this->actingAs($this->profPrincipal)
            ->get(route('teacher.bulletin_ng.step5', $config->id));

        $response->assertStatus(200);
        $response->assertViewHas(['config', 'subjects', 'students']);
    }

    /**
     * Test: lockNotes() - Changes session status
     */
    public function test_lock_notes_changes_status()
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

        $session = BulletinNgSession::create([
            'config_id' => $config->id,
            'trimestre_number' => 1,
            'sequence_number' => 1,
            'statut' => 'brouillon',
        ]);

        $response = $this->actingAs($this->profPrincipal)
            ->post(route('teacher.bulletin_ng.lock-notes', $session->id));

        $response->assertJson(['success' => true]);
        
        $session->refresh();
        $this->assertEquals('saisie_fermee', $session->statut);
    }

    /**
     * Test: step6Conduite() - shows conduct form
     */
    public function test_step6_conduct_shows_form()
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

        BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Student',
            'matricule' => 'S001',
            'sexe' => 'M',
        ]);

        $response = $this->actingAs($this->profPrincipal)
            ->get(route('teacher.bulletin_ng.step6', $config->id));

        $response->assertStatus(200);
        $response->assertViewHas('students');
    }

    /**
     * Test: step7Generate() - shows PDF preview
     */
    public function test_step7_generate_shows_students()
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

        BulletinNgStudent::create([
            'config_id' => $config->id,
            'nom' => 'Student',
            'matricule' => 'S001',
            'sexe' => 'M',
        ]);

        $response = $this->actingAs($this->profPrincipal)
            ->get(route('teacher.bulletin_ng.step7', $config->id));

        $response->assertStatus(200);
        $response->assertViewHas('students');
    }
}
