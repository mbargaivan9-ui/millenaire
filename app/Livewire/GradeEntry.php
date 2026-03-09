<?php

namespace App\Livewire;

use App\Models\BulletinTemplate;
use App\Models\StudentBulletin;
use App\Models\BulletinGrade;
use App\Models\TemplateSubjectAssignment;
use App\Services\BulletinCalculatorService;
use Livewire\Component;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Collection;

class GradeEntry extends Component
{
    /**
     * Reactive properties
     */
    #[Reactive]
    public BulletinTemplate $template;

    #[Reactive]
    public string $subjectFilter = '';

    /**
     * Component state
     */
    public $studentBulletins;
    public $subjectAssignments;
    public $grades = [];
    public $editingGrade = null;
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $sortDir = 'asc';
    public $showCalculations = false;
    public $calculationResults = [];
    
    // Filter & UI state
    public $selectedSubjectId = '';
    public $filterStatus = 'all';
    public $searchStudent = '';
    public $lastSavedAt = null;
    public $unsavedChanges = [];
    public $showSaveIndicator = false;

    /**
     * Listeners
     */
    protected $listeners = [
        'gradeUpdated' => 'handleGradeUpdated',
        'refreshCalculations' => 'recalculateAll',
        'lockBulletin' => 'handleBulletinLock',
    ];

    /**
     * Mount component
     */
    public function mount(BulletinTemplate $template)
    {
        $this->template = $template;
        $this->authorize();
        $this->loadData();
    }

    /**
     * Load initial data
     */
    private function loadData()
    {
        // Get all student bulletins for this template
        $this->studentBulletins = StudentBulletin::where('bulletin_template_id', $this->template->id)
            ->with(['student', 'grades.subject', 'grades.enteredBy'])
            ->get()
            ->map(function ($bulletin) {
                return [
                    'id' => $bulletin->id,
                    'student_id' => $bulletin->student_id,
                    'student_name' => $bulletin->student->name,
                    'student_matricule' => $bulletin->student->matricule ?? '-',
                    'general_average' => $bulletin->general_average,
                    'class_rank' => $bulletin->class_rank,
                    'appreciation' => $bulletin->appreciation,
                    'status' => $bulletin->status,
                    'is_locked' => $bulletin->is_locked,
                    'grades' => $bulletin->grades->mapWithKeys(fn($g) => [
                        $g->subject_id => [
                            'id' => $g->id,
                            'note_classe' => $g->note_classe,
                            'note_composition' => $g->note_composition,
                            'average' => $g->average,
                            'rank' => $g->rank,
                            'appreciation' => $g->appreciation,
                            'entered_by' => $g->enteredBy?->name ?? 'system',
                            'entered_at' => $g->updated_at?->format('d/m/Y H:i'),
                        ]
                    ])->toArray(),
                ];
            });

        // Load subject assignments for current user
        $currentUser = Auth::user();
        $this->subjectAssignments = TemplateSubjectAssignment::where('bulletin_template_id', $this->template->id)
            ->with('subject', 'teacher')
            ->when($currentUser->role !== 'director', function ($query) {
                // Only show own subject if not director
                return $query->where('teacher_id', $currentUser->id);
            })
            ->get()
            ->mapWithKeys(fn($a) => [
                $a->subject_id => [
                    'id' => $a->id,
                    'subject_id' => $a->subject_id,
                    'subject_name' => $a->subject->name,
                    'teacher_id' => $a->teacher_id,
                    'teacher_name' => $a->teacher->name,
                    'coefficient' => $a->coefficient,
                    'is_assigned_to_me' => $a->teacher_id === $currentUser->id || $currentUser->role === 'director',
                ]
            ]);

        // Initialize grades working copy
        $this->initializeGrades();
    }

    /**
     * Initialize grades working copy
     */
    private function initializeGrades()
    {
        $this->grades = [];
        foreach ($this->studentBulletins as $bulletinData) {
            foreach ($bulletinData['grades'] as $subjectId => $gradeData) {
                $key = "{$bulletinData['id']}_{$subjectId}";
                $this->grades[$key] = [
                    'bulletin_id' => $bulletinData['id'],
                    'subject_id' => $subjectId,
                    'note_classe' => $gradeData['note_classe'],
                    'note_composition' => $gradeData['note_composition'],
                ];
            }
        }
    }

    /**
     * Handle grade input change
     */
    public function updateGrade(int $bulletinId, int $subjectId, string $field, $value)
    {
        $this->authorize('updateGrade', $bulletinId, $subjectId);

        // Validate the value
        $validated = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($validated === false) {
            $this->dispatch('notification', type: 'error', message: 'Valeur invalide');
            return;
        }

        // Clamp between 0-20
        $value = max(0, min(20, $validated));

        $key = "{$bulletinId}_{$subjectId}";
        if (!isset($this->grades[$key])) {
            $this->grades[$key] = [
                'bulletin_id' => $bulletinId,
                'subject_id' => $subjectId,
                'note_classe' => null,
                'note_composition' => null,
            ];
        }

        $this->grades[$key][$field] = $value;
        $this->editingGrade = $key;

        // Auto-save after 1 second of inactivity
        $this->debounce('saveGrade', 1000, [$bulletinId, $subjectId, $field]);
    }

    /**
     * Save a single grade to database
     */
    public function saveGrade(int $bulletinId, int $subjectId, string $field)
    {
        try {
            $this->authorize('updateGrade', $bulletinId, $subjectId);

            $key = "{$bulletinId}_{$subjectId}";
            $gradeData = $this->grades[$key] ?? null;

            if (!$gradeData) {
                return;
            }

            // Find or create the grade record
            $bulletin = StudentBulletin::findOrFail($bulletinId);
            $this->authorize('update', $bulletin);

            if ($bulletin->is_locked) {
                $this->dispatch('notification', type: 'warning', message: 'Ce bulletin est verrouillé');
                return;
            }

            $grade = BulletinGrade::firstOrCreate(
                [
                    'student_bulletin_id' => $bulletinId,
                    'subject_id' => $subjectId,
                ],
                [
                    'note_classe' => null,
                    'note_composition' => null,
                    'entered_by' => Auth::id(),
                ]
            );

            // Update the grade
            $updateData = [];
            if ($gradeData['note_classe'] !== null) {
                $updateData['note_classe'] = $gradeData['note_classe'];
            }
            if ($gradeData['note_composition'] !== null) {
                $updateData['note_composition'] = $gradeData['note_composition'];
            }

            $grade->fill($updateData);
            $grade->entered_by = Auth::id();
            $grade->save();

            // Trigger recalculation
            $this->recalculateBulletin($bulletinId);

            $this->editingGrade = null;
            $this->dispatch('notification', type: 'success', message: 'Note sauvegardée');
        } catch (\Exception $e) {
            $this->dispatch('notification', type: 'error', message: 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Recalculate a single student bulletin
     */
    public function recalculateBulletin(int $bulletinId)
    {
        try {
            $bulletin = StudentBulletin::findOrFail($bulletinId);
            $this->authorize('update', $bulletin);

            $calculator = app(BulletinCalculatorService::class);
            $calculator->updateBulletinCalculations($bulletin);

            // Reload data
            $this->loadData();

            $this->dispatch('bulletinRecalculated', bulletinId: $bulletinId);
        } catch (\Exception $e) {
            \Log::error('Recalculation error: ' . $e->getMessage());
        }
    }

    /**
     * Handle grade updated event from external source
     */
    public function handleGradeUpdated($bulletinId, $subjectId)
    {
        $this->recalculateBulletin($bulletinId);
    }

    /**
     * Recalculate all bulletins (class ranking, etc.)
     */
    public function recalculateAll()
    {
        try {
            $calculator = app(BulletinCalculatorService::class);

            foreach ($this->studentBulletins as $bulletinData) {
                $bulletin = StudentBulletin::find($bulletinData['id']);
                if ($bulletin) {
                    $calculator->updateBulletinCalculations($bulletin);
                }
            }

            $this->loadData();
            $this->dispatch('notification', type: 'success', message: 'Tous les bulletins recalculés');
        } catch (\Exception $e) {
            $this->dispatch('notification', type: 'error', message: 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Handle bulletin lock
     */
    public function handleBulletinLock(int $bulletinId)
    {
        $bulletin = StudentBulletin::find($bulletinId);
        if ($bulletin) {
            $bulletin->update(['is_locked' => true, 'locked_at' => now(), 'locked_by' => Auth::id()]);
            $this->loadData();
            $this->dispatch('notification', type: 'success', message: 'Bulletin verrouillé');
        }
    }

    /**
     * Lock multiple bulletins
     */
    public function lockBulletins($bulletinIds)
    {
        StudentBulletin::whereIn('id', $bulletinIds)->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => Auth::id(),
        ]);

        $this->loadData();
        $this->dispatch('notification', type: 'success', message: count($bulletinIds) . ' bulletin(s) verrouillé(s)');
    }

    /**
     * Unlock a bulletin
     */
    public function unlockBulletin(int $bulletinId)
    {
        $bulletin = StudentBulletin::findOrFail($bulletinId);
        $this->authorize('unlock', $bulletin);

        $bulletin->update(['is_locked' => false, 'locked_at' => null, 'locked_by' => null]);
        $this->loadData();
        $this->dispatch('notification', type: 'success', message: 'Bulletin déverrouillé');
    }

    /**
     * Export to PDF
     */
    public function exportPDF(int $bulletinId)
    {
        $bulletin = StudentBulletin::findOrFail($bulletinId);
        $this->authorize('export', $bulletin);

        // Dispatch event for PDF generation
        $this->dispatch('generatePDF', bulletinId: $bulletinId);
    }

    /**
     * Export entire class
     */
    public function exportClassroom()
    {
        $this->authorize('exportClassroom', $this->template);
        $this->dispatch('generateClassroomPDF', templateId: $this->template->id);
    }

    /**
     * Get filtered subject assignments
     */
    #[Reactive]
    private function getFilteredSubjects(): Collection
    {
        $subjects = collect($this->subjectAssignments);

        if ($this->subjectFilter) {
            $subjects = $subjects->filter(fn($s) => str_contains(
                strtolower($s['subject_name']),
                strtolower($this->subjectFilter)
            ));
        }

        return $subjects;
    }

    /**
     * Sort student list
     */
    public function sort(string $field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->sortBulletins();
    }

    /**
     * Apply sorting to bulletins
     */
    private function sortBulletins()
    {
        usort($this->studentBulletins, function ($a, $b) {
            $aVal = match($this->sortBy) {
                'name' => $a['student_name'],
                'average' => $a['general_average'] ?? 0,
                'rank' => $a['class_rank'] ?? PHP_INT_MAX,
                default => $a['student_name'],
            };

            $bVal = match($this->sortBy) {
                'name' => $b['student_name'],
                'average' => $b['general_average'] ?? 0,
                'rank' => $b['class_rank'] ?? PHP_INT_MAX,
                default => $b['student_name'],
            };

            $result = $aVal <=> $bVal;
            return $this->sortDirection === 'asc' ? $result : -$result;
        });
    }

    /**
     * Check authorization
     */
    private function authorize()
    {
        $user = Auth::user();

        // Director can access all
        if ($user->role === 'director') {
            return true;
        }

        // Professor principal can access their classroom
        if ($user->role === 'professor_principal') {
            if ($this->template->classroom->professor_principal_id === $user->id) {
                return true;
            }
        }

        // Teacher can access only if assigned
        if ($user->role === 'teacher') {
            $hasAssignment = TemplateSubjectAssignment::where('bulletin_template_id', $this->template->id)
                ->where('teacher_id', $user->id)
                ->exists();

            if ($hasAssignment) {
                return true;
            }
        }

        abort(403, 'Non autorisé');
    }

    /**
     * Check grade update authorization
     */
    private function authorize(string $action, int $bulletinId, int $subjectId)
    {
        $user = Auth::user();
        $bulletin = StudentBulletin::find($bulletinId);

        if (!$bulletin) {
            abort(404);
        }

        // Check if bulletin is locked
        if ($bulletin->is_locked && $user->role !== 'director') {
            throw new \Exception('Ce bulletin est verrouillé');
        }

        // Director can do anything
        if ($user->role === 'director') {
            return true;
        }

        // Professor principal can access their classroom
        if ($user->role === 'professor_principal') {
            if ($bulletin->classroom->professor_principal_id === $user->id) {
                return true;
            }
        }

        // Teacher can only edit their subject
        if ($user->role === 'teacher') {
            $hasAssignment = TemplateSubjectAssignment::where('bulletin_template_id', $bulletin->bulletin_template_id)
                ->where('subject_id', $subjectId)
                ->where('teacher_id', $user->id)
                ->exists();

            if ($hasAssignment) {
                return true;
            }
        }

        throw new \Exception('Non autorisé pour cette matière');
    }

    /**
     * Check if user is professor principal
     */
    public function isProfessorPrincipal(): bool
    {
        return Auth::user()->role === 'professor_principal' && 
               Auth::user()->id === $this->template->classroom->professor_principal_id;
    }

    /**
     * Check if user can edit a grade
     */
    public function canEditGrade(BulletinGrade $grade): bool
    {
        if ($grade->bulletin->is_locked && !$this->isProfessorPrincipal()) {
            return false;
        }

        $user = Auth::user();
        
        // Director can edit everything
        if ($user->role === 'director') {
            return true;
        }

        // Professor principal can edit everything for their classroom
        if ($this->isProfessorPrincipal()) {
            return true;
        }

        // Teacher can only edit their assigned subject
        $assignment = TemplateSubjectAssignment::where('bulletin_template_id', $this->template->id)
            ->where('subject_id', $grade->subject_id)
            ->where('teacher_id', $user->id)
            ->exists();

        return $assignment;
    }

    /**
     * Get professor principal for display
     */
    public function getProfessorPrincipal()
    {
        return $this->template->classroom->professorPrincipal ?? null;
    }

    /**
     * Toggle bulletin lock
     */
    public function toggleLock(int $bulletinId)
    {
        if (!$this->isProfessorPrincipal()) {
            $this->dispatch('error', message: 'Non autorisé');
            return;
        }

        try {
            $bulletin = StudentBulletin::findOrFail($bulletinId);
            
            $bulletin->update([
                'is_locked' => !$bulletin->is_locked,
                'locked_at' => !$bulletin->is_locked ? now() : null,
                'locked_by' => !$bulletin->is_locked ? Auth::id() : null,
            ]);

            $this->loadData();
            $this->dispatch('success', message: $bulletin->is_locked ? 'Bulletin verrouillé' : 'Bulletin déverrouillé');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Render the component
     */
    public function render()
    {
        $filteredSubjects = $this->getFilteredSubjects();

        return view('livewire.grade-entry', [
            'studentBulletins' => $this->studentBulletins,
            'subjectAssignments' => $this->subjectAssignments,
            'filteredSubjects' => $filteredSubjects,
            'grades' => $this->grades,
            'editingGrade' => $this->editingGrade,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ]);
    }
}
