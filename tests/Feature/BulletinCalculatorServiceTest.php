<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\BulletinTemplate;
use App\Models\StudentBulletin;
use App\Models\BulletinGrade;
use App\Models\User;
use App\Models\Student;
use App\Models\Classe;
use App\Models\Subject;
use App\Services\BulletinCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * BulletinCalculatorServiceTest
 * 
 * Test de la logique de calcul des bulletins
 */
class BulletinCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    private BulletinCalculatorService $calculator;
    private User $profPrincipal;
    private Classe $classroom;
    private StudentBulletin $bulletin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new BulletinCalculatorService();

        // Setup test data
        $this->profPrincipal = User::factory()->create(['role' => 'principal_teacher']);
        $this->classroom = Classe::factory()->create();
        
        $student = Student::factory()->create();
        
        $template = BulletinTemplate::create([
            'classroom_id' => $this->classroom->id,
            'created_by' => $this->profPrincipal->id,
            'name' => 'Test Template',
            'academic_year' => '2025-2026',
            'trimester' => 1,
            'structure_json' => json_encode(['subjects' => []]),
            'is_validated' => true,
        ]);

        $this->bulletin = StudentBulletin::create([
            'template_id' => $template->id,
            'student_id' => $student->id,
            'classroom_id' => $this->classroom->id,
            'academic_year' => '2025-2026',
            'trimester' => 1,
            'status' => 'draft',
        ]);
    }

    /**
     * Test: Subject average calculation
     * (note_classe + note_composition) / 2
     */
    public function test_calculate_subject_average()
    {
        $subject = Subject::factory()->create(['coefficient' => 2]);

        $grade = BulletinGrade::create([
            'bulletin_id' => $this->bulletin->id,
            'subject_id' => $subject->id,
            'teacher_id' => User::factory()->create()->id,
            'note_classe' => 16,
            'note_composition' => 14,
        ]);

        $average = $this->calculator->calculateSubjectAverage($grade);

        $this->assertEquals(15.0, $average);
    }

    /**
     * Test: General average calculation
     * Σ(subject_average × coefficient) / Σ(coefficients)
     */
    public function test_calculate_general_average()
    {
        // Create 2 subjects with different coefficients
        $math = Subject::factory()->create(['name' => 'Mathématiques', 'coefficient' => 3]);
        $french = Subject::factory()->create(['name' => 'Français', 'coefficient' => 2]);

        // Math: (15 + 17) / 2 = 16
        BulletinGrade::create([
            'bulletin_id' => $this->bulletin->id,
            'subject_id' => $math->id,
            'teacher_id' => User::factory()->create()->id,
            'note_classe' => 15,
            'note_composition' => 17,
            'subject_average' => 16,
        ]);

        // French: (14 + 12) / 2 = 13
        BulletinGrade::create([
            'bulletin_id' => $this->bulletin->id,
            'subject_id' => $french->id,
            'teacher_id' => User::factory()->create()->id,
            'note_classe' => 14,
            'note_composition' => 12,
            'subject_average' => 13,
        ]);

        $generalAverage = $this->calculator->calculateGeneralAverage($this->bulletin);

        // (16*3 + 13*2) / (3+2) = (48 + 26) / 5 = 74 / 5 = 14.8
        $this->assertEquals(14.8, $generalAverage);
    }

    /**
     * Test: Class ranking with DENSE_RANK logic
     */
    public function test_calculate_class_rankings()
    {
        // Create 3 students with different averages
        $template = $this->bulletin->template;

        $student1 = Student::factory()->create();
        $bulletin1 = StudentBulletin::create([
            'template_id' => $template->id,
            'student_id' => $student1->id,
            'classroom_id' => $this->classroom->id,
            'academic_year' => '2025-2026',
            'trimester' => 1,
            'general_average' => 16.5,
        ]);

        $student2 = Student::factory()->create();
        $bulletin2 = StudentBulletin::create([
            'template_id' => $template->id,
            'student_id' => $student2->id,
            'classroom_id' => $this->classroom->id,
            'academic_year' => '2025-2026',
            'trimester' => 1,
            'general_average' => 16.5, // Same as student1
        ]);

        $student3 = Student::factory()->create();
        $bulletin3 = StudentBulletin::create([
            'template_id' => $template->id,
            'student_id' => $student3->id,
            'classroom_id' => $this->classroom->id,
            'academic_year' => '2025-2026',
            'trimester' => 1,
            'general_average' => 14.0,
        ]);

        $rankings = $this->calculator->calculateClassRankings($bulletin1);

        // bulletin1 and bulletin2 should have rank 1 (tied)
        $this->assertEquals(1, $rankings[$bulletin1->id]);
        $this->assertEquals(1, $rankings[$bulletin2->id]);
        
        // bulletin3 should have rank 2 (DENSE_RANK skips to next)
        $this->assertEquals(2, $rankings[$bulletin3->id]);
    }

    /**
     * Test: Appreciation scale
     */
    public function test_get_appreciation()
    {
        $this->assertEquals('Très Bien', $this->calculator->getAppreciation(18.5));
        $this->assertEquals('Bien', $this->calculator->getAppreciation(14.5));
        $this->assertEquals('Assez Bien', $this->calculator->getAppreciation(13.0));
        $this->assertEquals('Passable', $this->calculator->getAppreciation(10.5));
        $this->assertEquals('Insuffisant', $this->calculator->getAppreciation(8.0));
    }

    /**
     * Test: Custom appreciation scale
     */
    public function test_set_custom_appreciation_scale()
    {
        $custom = [
            ['min' => 18, 'max' => 20, 'label' => 'Excellent'],
            ['min' => 15, 'max' => 17.99, 'label' => 'Very Good'],
            ['min' => 0, 'max' => 14.99, 'label' => 'Needs Improvement'],
        ];

        $this->calculator->setAppreciationScale($custom);

        $this->assertEquals('Excellent', $this->calculator->getAppreciation(19));
        $this->assertEquals('Very Good', $this->calculator->getAppreciation(16));
        $this->assertEquals('Needs Improvement', $this->calculator->getAppreciation(12));
    }

    /**
     * Test: Update bulletin with all calculations
     */
    public function test_update_bulletin_calculations()
    {
        $subject = Subject::factory()->create(['coefficient' => 1]);
        $teacher = User::factory()->create();

        BulletinGrade::create([
            'bulletin_id' => $this->bulletin->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'note_classe' => 16,
            'note_composition' => 14,
        ]);

        // Run full calculation
        $this->calculator->updateBulletinCalculations($this->bulletin);

        // Reload from DB
        $this->bulletin->refresh();

        $this->assertEquals(15.0, $this->bulletin->general_average);
        $this->assertEquals('Bien', $this->bulletin->appreciation);
        $this->assertNotNull($this->bulletin->class_rank);
    }
}
