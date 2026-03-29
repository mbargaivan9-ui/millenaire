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

class BulletinNgWizardTest extends TestCase
{
    use RefreshDatabase;

    private User $prof;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prof = User::factory()->create(['is_prof_principal' => true]);
    }

    public function test_professor_can_access_step_1_language_selection(): void
    {
        $response = $this->actingAs($this->prof)
            ->get(route('teacher.bulletin_ng.step1'));
        
        $response->assertStatus(200);
        $response->assertViewIs('teacher.bulletin_ng.step1_section');
    }

    public function test_professor_can_create_config_at_step_2(): void
    {
        $response = $this->actingAs($this->prof)
            ->post(route('teacher.bulletin_ng.store-config'), [
                'langue' => 'FR',
                'school_name' => 'Test School',
                'delegation_fr' => 'Test',
                'delegation_en' => 'Test',
                'nom_classe' => '3ème A',
                'effectif' => 45,
                'trimestre' => 1,
                'sequence' => 1,
                'annee_academique' => '2025-2026',
            ]);
        
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertEquals(1, BulletinNgConfig::where('prof_principal_id', $this->prof->id)->count());
    }

    public function test_professor_can_add_subjects_at_step_3(): void
    {
        $config = BulletinNgConfig::create([
            'prof_principal_id' => $this->prof->id,
            'nom_classe' => '3ème A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);
        
        $response = $this->actingAs($this->prof)
            ->post(route('teacher.bulletin_ng.store-subjects', $config->id), [
                'subjects' => [
                    ['nom' => 'Math', 'coefficient' => 2],
                    ['nom' => 'French', 'coefficient' => 1.5],
                ],
            ]);
        
        $response->assertJson(['success' => true]);
        $this->assertEquals(2, BulletinNgSubject::where('config_id', $config->id)->count());
    }

    public function test_professor_can_add_students_at_step_4(): void
    {
        $config = BulletinNgConfig::create([
            'prof_principal_id' => $this->prof->id,
            'nom_classe' => '3ème A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);
        
        $response = $this->actingAs($this->prof)
            ->post(route('teacher.bulletin_ng.students.store', $config->id), [
                'matricule' => 'S001',
                'nom' => 'Jean',
                'sexe' => 'M',
            ]);
        
        $response->assertJson(['success' => true]);
        $this->assertEquals(1, BulletinNgStudent::where('config_id', $config->id)->count());
    }

    public function test_professor_can_enter_grades_at_step_5(): void
    {
        $config = BulletinNgConfig::create([
            'prof_principal_id' => $this->prof->id,
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
        
        $response = $this->actingAs($this->prof)
            ->post(route('teacher.bulletin_ng.save-note', $config->id), [
                'ng_student_id' => $student->id,
                'ng_subject_id' => $subject->id,
                'note' => 16,
            ]);
        
        $response->assertJson(['success' => true]);
        $this->assertEquals(1, BulletinNgNote::where('config_id', $config->id)->count());
    }

    public function test_non_professor_cannot_access_bulletin_wizard(): void
    {
        $user = User::factory()->create(['is_prof_principal' => false]);
        
        $response = $this->actingAs($user)
            ->get(route('teacher.bulletin_ng.step1'));
        
        $response->assertStatus(403);
    }

    public function test_professor_cannot_modify_other_professors_config(): void
    {
        $prof2 = User::factory()->create(['is_prof_principal' => true]);
        
        $config = BulletinNgConfig::create([
            'prof_principal_id' => $prof2->id,
            'nom_classe' => '3ème B',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'School',
            'annee_academique' => '2025-2026',
        ]);
        
        $response = $this->actingAs($this->prof)
            ->get(route('teacher.bulletin_ng.step3', $config->id));
        
        $response->assertStatus(403);
    }

    public function test_validation_fails_with_missing_required_fields(): void
    {
        $response = $this->actingAs($this->prof)
            ->post(route('teacher.bulletin_ng.store-config'), [
                'langue' => 'FR',
                // Missing required fields
            ]);
        
        $response->assertUnprocessable();
    }
}
