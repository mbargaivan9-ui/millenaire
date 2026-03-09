<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Classe;
use App\Services\AssignmentService;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class AssignmentPanel extends Component
{
    public $teachers = [];
    public $classes = [];
    public $selectedTeacher = null;
    public $selectedClass = null;
    public $searchTeacher = '';
    public $filterLevel = '';
    public $notification = null;

    protected AssignmentService $assignmentService;

    public function mount()
    {
        $this->assignmentService = app(AssignmentService::class);
        $this->loadData();
    }

    /**
     * Load teachers and classes from database
     */
    public function loadData()
    {
        // Load available teachers (those with teaching role)
        $this->teachers = User::where('role', 'teacher')
            ->where('is_active', true)
            ->with('mainTeacherClass')
            ->orderBy('name')
            ->get()
            ->map(fn($teacher) => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'current_class_id' => $teacher->mainTeacherClass?->id,
                'current_class_name' => $teacher->mainTeacherClass?->name,
            ])
            ->toArray();

        // Load classes organized by level
        $this->classes = Classe::where('is_active', true)
            ->with('getCurrentMainTeacher')
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->groupBy('level')
            ->map(function ($classesByLevel) {
                return $classesByLevel->map(fn($class) => [
                    'id' => $class->id,
                    'name' => $class->name,
                    'level' => $class->level,
                    'stream' => $class->stream,
                    'section' => $class->section,
                    'current_teacher_id' => $class->getCurrentMainTeacher()?->id,
                    'current_teacher_name' => $class->getCurrentMainTeacher()?->name,
                    'student_count' => $class->students->count(),
                ])->toArray();
            })
            ->toArray();
    }

    /**
     * Filter teachers based on search
     */
    public function updatedSearchTeacher()
    {
        if (empty($this->searchTeacher)) {
            $this->loadData();
            return;
        }

        $filtered = array_filter($this->teachers, function ($teacher) {
            return stripos($teacher['name'], $this->searchTeacher) !== false
                || stripos($teacher['email'], $this->searchTeacher) !== false;
        });

        $this->teachers = array_values($filtered);
    }

    /**
     * Filter classes by level
     */
    public function updatedFilterLevel()
    {
        if (empty($this->filterLevel)) {
            $this->loadData();
            return;
        }

        $this->classes = array_filter($this->classes, function ($classesByLevel) {
            return array_key_first($classesByLevel) === $this->filterLevel;
        });
    }

    /**
     * Select a teacher to view details
     */
    public function selectTeacher($teacherId)
    {
        $this->selectedTeacher = collect($this->teachers)
            ->firstWhere('id', $teacherId);
    }

    /**
     * Select a class to view details
     */
    public function selectClass($classId)
    {
        $classe = null;
        foreach ($this->classes as $level => $classesByLevel) {
            foreach ($classesByLevel as $c) {
                if ($c['id'] === $classId) {
                    $classe = $c;
                    break 2;
                }
            }
        }
        $this->selectedClass = $classe;
    }

    /**
     * Reassign teacher to a class (called from SortableJS drag & drop)
     * @param int $teacherId Teacher being reassigned
     * @param int $classId Target class
     */
    #[On('reassign-teacher')]
    public function reassignTeacher($teacherId, $classId)
    {
        try {
            $classModel = Classe::findOrFail($classId);
            $result = $this->assignmentService->assignTeacher(
                teacherId: $teacherId,
                classId: $classId,
                reason: 'assignment_panel_drag_drop',
                assignedById: auth()->id(),
            );

            if ($result['success']) {
                $this->showNotification('success', "Teacher assigned to {$classModel->name} successfully!");
                $this->loadData();
            } else {
                $this->showNotification('error', $result['message'] ?? 'Assignment failed');
            }
        } catch (\Exception $e) {
            Log::error("Reassignment error: " . $e->getMessage());
            $this->showNotification('error', 'Error assigning teacher: ' . $e->getMessage());
        }
    }

    /**
     * Revoke teacher assignment from a class
     */
    public function revokeAssignment($teacherId, $classId)
    {
        try {
            $classModel = Classe::findOrFail($classId);
            $result = $this->assignmentService->revokeAssignment(
                teacherId: $teacherId,
                classId: $classId,
                revokedById: auth()->id(),
            );

            if ($result['success']) {
                $this->showNotification('success', "Assignment revoked successfully!");
                $this->loadData();
            } else {
                $this->showNotification('error', $result['message'] ?? 'Revocation failed');
            }
        } catch (\Exception $e) {
            Log::error("Revocation error: " . $e->getMessage());
            $this->showNotification('error', 'Error revoking assignment: ' . $e->getMessage());
        }
    }

    /**
     * Transfer teacher from one class to another
     */
    public function transferTeacher($teacherId, $fromClassId, $toClassId)
    {
        try {
            $fromClass = Classe::findOrFail($fromClassId);
            $toClass = Classe::findOrFail($toClassId);

            $result = $this->assignmentService->transferTeacher(
                teacherId: $teacherId,
                fromClassId: $fromClassId,
                toClassId: $toClassId,
                reason: 'assignment_panel_transfer',
                transferredById: auth()->id(),
            );

            if ($result['success']) {
                $this->showNotification('success', "Teacher transferred from {$fromClass->name} to {$toClass->name}!");
                $this->loadData();
            } else {
                $this->showNotification('error', $result['message'] ?? 'Transfer failed');
            }
        } catch (\Exception $e) {
            Log::error("Transfer error: " . $e->getMessage());
            $this->showNotification('error', 'Error transferring teacher: ' . $e->getMessage());
        }
    }

    /**
     * Get assignment history for a teacher
     */
    public function getTeacherHistory($teacherId)
    {
        try {
            $history = $this->assignmentService->getTeacherAssignmentHistory($teacherId);
            return $history['success'] ? $history['data'] : [];
        } catch (\Exception $e) {
            Log::error("Error fetching teacher history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get assignment history for a class
     */
    public function getClassHistory($classId)
    {
        try {
            $history = $this->assignmentService->getClassAssignmentHistory($classId);
            return $history['success'] ? $history['data'] : [];
        } catch (\Exception $e) {
            Log::error("Error fetching class history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Show notification message
     */
    private function showNotification($type, $message)
    {
        $this->notification = [
            'type' => $type,
            'message' => $message,
        ];

        // Auto-dismiss after 5 seconds
        $this->dispatch('notification-shown');
    }

    /**
     * Get active main teachers
     */
    public function getActiveMainTeachers()
    {
        try {
            $mainTeachers = $this->assignmentService->getActiveMainTeachers();
            return $mainTeachers['success'] ? $mainTeachers['data'] : [];
        } catch (\Exception $e) {
            Log::error("Error fetching main teachers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear selection
     */
    public function clearSelection()
    {
        $this->selectedTeacher = null;
        $this->selectedClass = null;
    }

    /**
     * Reset filters
     */
    public function resetFilters()
    {
        $this->searchTeacher = '';
        $this->filterLevel = '';
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.admin.assignment-panel', [
            'mainTeachers' => $this->getActiveMainTeachers(),
        ]);
    }
}
