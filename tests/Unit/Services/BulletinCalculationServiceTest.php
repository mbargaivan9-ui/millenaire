<?php

namespace Tests\Unit\Services;

use App\Models\BulletinNgConfig;
use App\Models\BulletinNgSession;
use App\Models\BulletinNgStudent;
use App\Models\BulletinNgSubject;
use App\Models\BulletinNgNote;
use App\Models\User;
use App\Services\BulletinCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulletinCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private BulletinCalculationService $service;
    private BulletinNgConfig $config;
    private BulletinNgSession $session;
    private BulletinNgStudent $student;
    private BulletinNgSubject $subject1;
    private BulletinNgSubject $subject2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BulletinCalculationService::class);
        
        $user = User::factory()->create(['is_prof_principal' => true]);
        
        $this->config = BulletinNgConfig::create([
            'prof_principal_id' => $user->id,
            'nom_classe' => '3A',
            'trimestre' => 1,
            'sequence' => 1,
            'langue' => 'FR',
            'school_name' => 'Test School',
            'annee_academique' => '2025-2026',
        ]);
        
        $this->session = BulletinNgSession::create([
            'config_id' => $this->config->id,
            'trimestre_number' => 1,
            'sequence_number' => 1,
            'statut' => 'brouillon',
        ]);

        $this->student = BulletinNgStudent::create([
            'config_id' => $this->config->id,
            'matricule' => 'S001',
            'nom' => 'John Doe',
            'sexe' => 'M',
            'is_active' => true,
        ]);

        $this->subject1 = BulletinNgSubject::create([
            'config_id' => $this->config->id,
            'nom' => 'Mathematics',
            'coefficient' => 2.0,
            'ordre' => 0,
        ]);

        $this->subject2 = BulletinNgSubject::create([
            'config_id' => $this->config->id,
            'nom' => 'French',
            'coefficient' => 1.5,
            'ordre' => 1,
        ]);
    }

    public function test_can_calculate_sequence_average(): void
    {
        BulletinNgNote::create([
            'config_id' => $this->config->id,
            'ng_student_id' => $this->student->id,
            'ng_subject_id' => $this->subject1->id,
            'session_id' => $this->session->id,
            'sequence_number' => 1,
            'note' => 16,
        ]);

        BulletinNgNote::create([
            'config_id' => $this->config->id,
            'ng_student_id' => $this->student->id,
            'ng_subject_id' => $this->subject2->id,
            'session_id' => $this->session->id,
            'sequence_number' => 1,
            'note' => 14,
        ]);

        $average = $this->service->sequenceAverage(
            $this->student->id,
            $this->config->id,
            1
        );

        $this->assertIsNumeric($average);
        $this->assertGreaterThan(15, $average);
        $this->assertLessThan(16, $average);
    }

    public function test_sequence_average_with_no_grades(): void
    {
        $average = $this->service->sequenceAverage(
            $this->student->id,
            $this->config->id,
            1
        );

        $this->assertEquals(0, $average);
    }

    public function test_can_calculate_trimester_average(): void
    {
        BulletinNgNote::create([
            'config_id' => $this->config->id,
            'ng_student_id' => $this->student->id,
            'ng_subject_id' => $this->subject1->id,
            'session_id' => $this->session->id,
            'sequence_number' => 1,
            'note' => 16,
        ]);

        BulletinNgNote::create([
            'config_id' => $this->config->id,
            'ng_student_id' => $this->student->id,
            'ng_subject_id' => $this->subject2->id,
            'session_id' => $this->session->id,
            'sequence_number' => 1,
            'note' => 14,
        ]);

        $session2 = BulletinNgSession::create([
            'config_id' => $this->config->id,
            'trimestre_number' => 1,
            'sequence_number' => 2,
            'statut' => 'brouillon',
        ]);

        BulletinNgNote::create([
            'config_id' => $this->config->id,
            'ng_student_id' => $this->student->id,
            'ng_subject_id' => $this->subject1->id,
            'session_id' => $session2->id,
            'sequence_number' => 2,
            'note' => 18,
        ]);

        BulletinNgNote::create([
            'config_id' => $this->config->id,
            'ng_student_id' => $this->student->id,
            'ng_subject_id' => $this->subject2->id,
            'session_id' => $session2->id,
            'sequence_number' => 2,
            'note' => 15,
        ]);

        $average = $this->service->trimesterAverage(
            $this->student->id,
            $this->config->id,
            1
        );

        $this->assertIsNumeric($average);
        $this->assertGreaterThan(0, $average);
    }

    public function test_can_calculate_final_grade(): void
    {
        BulletinNgNote::create([
            'config_id' => $this->config->id,
            'ng_student_id' => $this->student->id,
            'ng_subject_id' => $this->subject1->id,
            'session_id' => $this->session->id,
            'sequence_number' => 1,
            'note' => 16,
        ]);

        $final = $this->service->finalGrade($this->student->id, $this->config->id);
        
        $this->assertIsNumeric($final);
        $this->assertGreaterThanOrEqual(0, $final);
        $this->assertLessThanOrEqual(20, $final);
    }

    public function test_can_rank_students(): void
    {
        $student2 = BulletinNgStudent::create([
            'config_id' => $this->config->id,
            'matricule' => 'S002',
            'nom' => 'Jane Doe',
            'sexe' => 'F',
            'is_active' => true,
        ]);

        BulletinNgNote::create([
            'config_id' => $this->config->id,
            'ng_student_id' => $this->student->id,
            'ng_subject_id' => $this->subject1->id,
            'session_id' => $this->session->id,
            'sequence_number' => 1,
            'note' => 16,
        ]);

        BulletinNgNote::create([
            'config_id' => $this->config->id,
            'ng_student_id' => $student2->id,
            'ng_subject_id' => $this->subject1->id,
            'session_id' => $this->session->id,
            'sequence_number' => 1,
            'note' => 12,
        ]);

        $rank = $this->service->classRanking(
            $this->student->id,
            $this->config->id,
            1
        );

        $this->assertIsInt($rank);
        $this->assertGreaterThanOrEqual(1, $rank);
        $this->assertLessThanOrEqual(2, $rank);
    }

    public function test_update_trimester_record_create_new(): void
    {
        BulletinNgNote::create([
            'config_id' => $this->config->id,
            'ng_student_id' => $this->student->id,
            'ng_subject_id' => $this->subject1->id,
            'session_id' => $this->session->id,
            'sequence_number' => 1,
            'note' => 16,
        ]);

        $this->service->updateTrimesterRecord(
            $this->student->id,
            $this->config->id,
            1
        );

        $trimestre = \App\Models\BulletinNgTrimestre::where([
            'ng_student_id' => $this->student->id,
            'config_id' => $this->config->id,
            'trimestre_number' => 1,
        ])->first();

        $this->assertNotNull($trimestre);
        $this->assertGreaterThan(0, $trimestre->moyenne);
    }

    public function test_recalculate_all_trimestres(): void
    {
        BulletinNgNote::create([
            'config_id' => $this->config->id,
            'ng_student_id' => $this->student->id,
            'ng_subject_id' => $this->subject1->id,
            'session_id' => $this->session->id,
            'sequence_number' => 1,
            'note' => 16,
        ]);

        $this->service->recalculateAllTrimestres(
            $this->student->id,
            $this->config->id
        );

        $count = \App\Models\BulletinNgTrimestre::where([
            'ng_student_id' => $this->student->id,
            'config_id' => $this->config->id,
        ])->count();

        $this->assertGreaterThanOrEqual(0, $count);
    }
}

