<?php

namespace App\Jobs;

use App\Models\BulletinTemplate;
use App\Models\StudentBulletin;
use App\Models\Subject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * GenerateStudentBulletinsJob
 *
 * Generate individual student bulletins from a validated template
 * 
 * Triggered after template validation
 * Creates StudentBulletin + BulletinGrade rows for all students in class
 *
 * @implements ShouldQueue
 */
class GenerateStudentBulletinsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    public function __construct(
        private BulletinTemplate $template
    ) {}

    /**
     * Execute the job
     */
    public function handle(): void
    {
        try {
            Log::info('Starting GenerateStudentBulletinsJob', [
                'template_id' => $this->template->id,
                'classroom_id' => $this->template->classroom_id,
            ]);

            DB::beginTransaction();

            // Get all students in this classroom
            $students = $this->template->classroom
                ->students()
                ->whereNull('deleted_at')
                ->get();

            Log::info('Found students for bulletin generation', [
                'count' => $students->count(),
            ]);

            if ($students->isEmpty()) {
                Log::warning('No students found in classroom', [
                    'classroom_id' => $this->template->classroom_id,
                ]);
                DB::commit();
                return;
            }

            // Parse subjects from template structure
            $templateStructure = $this->template->structure_json;
            $subjects = $templateStructure['subjects'] ?? [];

            if (empty($subjects)) {
                Log::error('No subjects found in template structure', [
                    'template_id' => $this->template->id,
                ]);
                DB::rollBack();
                throw new Exception('Template structure contains no subjects');
            }

            $createdCount = 0;
            $gradeCount = 0;

            // Create bulletin for each student
            foreach ($students as $student) {
                // Check if bulletin already exists
                $existingBulletin = StudentBulletin::where('student_id', $student->id)
                    ->where('template_id', $this->template->id)
                    ->where('academic_year', $this->template->academic_year)
                    ->where('trimester', $this->template->trimester)
                    ->first();

                if ($existingBulletin) {
                    Log::info('Bulletin already exists, skipping', [
                        'student_id' => $student->id,
                        'template_id' => $this->template->id,
                    ]);
                    continue;
                }

                // Create bulletin
                $bulletin = StudentBulletin::create([
                    'template_id' => $this->template->id,
                    'student_id' => $student->id,
                    'classroom_id' => $this->template->classroom_id,
                    'academic_year' => $this->template->academic_year,
                    'trimester' => $this->template->trimester,
                    'status' => 'draft',
                ]);

                $createdCount++;
                Log::debug('Created student bulletin', [
                    'bulletin_id' => $bulletin->id,
                    'student_id' => $student->id,
                ]);

                // Create grade entries for each subject
                foreach ($subjects as $subjectData) {
                    $subjectId = $subjectData['id'] ?? null;
                    
                    if (!$subjectId) {
                        Log::warning('Subject ID missing from template', [
                            'subject_data' => $subjectData,
                        ]);
                        continue;
                    }

                    // Find actual subject in DB
                    $subject = Subject::find($subjectId);
                    if (!$subject) {
                        Log::warning('Subject not found in database', [
                            'subject_id' => $subjectId,
                        ]);
                        continue;
                    }

                    // Create grade entry (without notes initially - awaiting teacher input)
                    $bulletin->grades()->create([
                        'subject_id' => $subject->id,
                        'teacher_id' => null, // Will be assigned via TemplateSubjectAssignment
                        // note_classe, note_composition remain NULL until teacher enters them
                    ]);

                    $gradeCount++;
                }
            }

            DB::commit();

            Log::info('GenerateStudentBulletinsJob completed successfully', [
                'template_id' => $this->template->id,
                'bulletins_created' => $createdCount,
                'grades_created' => $gradeCount,
            ]);

            // Fire event or notification
            event(new \App\Events\BulletinsGenerated($this->template, $createdCount));

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('GenerateStudentBulletinsJob failed', [
                'template_id' => $this->template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::error('GenerateStudentBulletinsJob failed permanently', [
            'template_id' => $this->template->id,
            'exception' => $exception->getMessage(),
        ]);

        // TODO: Send notification to professor principal
    }
}
