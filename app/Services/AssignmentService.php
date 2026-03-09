<?php

namespace App\Services;

use App\Models\User;
use App\Models\Classe;
use App\Models\AssignmentHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AssignmentService
{
    /**
     * Assign a teacher to a class as main teacher (professeur principal)
     * 
     * This method handles the complete assignment workflow including:
     * - Validation of current assignments
     * - Archiving previous assignments if any
     * - Creating assignment history entry
     * - Updating user and class data
     *
     * @param int $teacherId The ID of the teacher to assign
     * @param int $classId The ID of the class to assign to
     * @param string|null $reason The reason for the assignment
     * @param string|null $notes Additional notes about the assignment
     * @param int|null $assignedById The ID of the user making the assignment
     * 
     * @return array Result array with success status and assignment data
     * 
     * @throws Exception If assignment fails
     */
    public function assignTeacher(
        int $teacherId,
        int $classId,
        ?string $reason = null,
        ?string $notes = null,
        ?int $assignedById = null,
    ): array {
        try {
            // Start database transaction
            DB::beginTransaction();

            // Validate teacher exists and is active
            $teacher = $this->validateTeacher($teacherId);
            if (!$teacher) {
                throw new Exception('Teacher not found or is not active.');
            }

            // Validate class exists and is active
            $class = $this->validateClass($classId);
            if (!$class) {
                throw new Exception('Class not found or is not active.');
            }

            // Get the old teacher if class already has one
            $oldTeacher = $class->profPrincipal;

            // Archive previous assignment if class had a main teacher
            if ($oldTeacher) {
                $this->archivePreviousAssignment($oldTeacher, $class, $assignedById);
            }

            // Create new assignment history entry
            $assignmentHistory = $this->createAssignmentHistory(
                oldTeacherId: $oldTeacher?->id,
                newTeacherId: $teacherId,
                classId: $classId,
                reason: $reason,
                notes: $notes,
                assignedById: $assignedById,
            );

            // Update teacher record
            $this->updateTeacherAssignment($teacher, $classId);

            // Update class record with new main teacher
            $this->updateClassMainTeacher($class, $teacherId);

            // Commit transaction
            DB::commit();

            // Log the successful assignment
            Log::info("Teacher assignment successful", [
                'teacher_id' => $teacherId,
                'teacher_name' => $teacher->name,
                'class_id' => $classId,
                'class_name' => $class->name,
                'old_teacher' => $oldTeacher?->name,
                'assignment_history_id' => $assignmentHistory->id,
                'reason' => $reason,
            ]);

            return [
                'success' => true,
                'message' => "Teacher {$teacher->name} successfully assigned to class {$class->name}",
                'assignment' => $assignmentHistory,
                'teacher' => $teacher,
                'class' => $class,
                'old_teacher' => $oldTeacher,
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            Log::error("Teacher assignment failed: " . $e->getMessage(), [
                'teacher_id' => $teacherId,
                'class_id' => $classId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => "Assignment failed: " . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Revoke a teacher's assignment from a class
     *
     * @param int $teacherId The teacher to revoke
     * @param int $classId The class to revoke from
     * @param string|null $reason The reason for revocation
     * @param int|null $revokedById The user revoking the assignment
     * 
     * @return array Result array
     */
    public function revokeAssignment(
        int $teacherId,
        int $classId,
        ?string $reason = null,
        ?int $revokedById = null,
    ): array {
        try {
            DB::beginTransaction();

            // Find the assignment history
            $assignment = AssignmentHistory::where('new_teacher_id', $teacherId)
                ->where('class_id', $classId)
                ->active()
                ->first();

            if (!$assignment) {
                throw new Exception('No active assignment found for this teacher and class.');
            }

            // Mark assignment as ended
            $assignment->markAsEnded($reason);

            // Update teacher
            $teacher = User::find($teacherId);
            $teacher->update([
                'is_main_teacher' => false,
                'class_id' => null,
            ]);

            // Update class
            $class = Classe::find($classId);
            $class->update(['prof_principal_id' => null]);

            DB::commit();

            Log::info("Teacher assignment revoked", [
                'teacher_id' => $teacherId,
                'class_id' => $classId,
                'reason' => $reason,
                'revoked_by' => $revokedById,
            ]);

            return [
                'success' => true,
                'message' => "Assignment revoked successfully for {$teacher->name}",
                'assignment' => $assignment,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            Log::error("Failed to revoke assignment: " . $e->getMessage(), [
                'teacher_id' => $teacherId,
                'class_id' => $classId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Transfer a teacher from one class to another
     *
     * @param int $teacherId The teacher to transfer
     * @param int $fromClassId The current class
     * @param int $toClassId The new class
     * @param string|null $reason The reason for transfer
     * @param int|null $transferredById The user making the transfer
     * 
     * @return array Result array
     */
    public function transferTeacher(
        int $teacherId,
        int $fromClassId,
        int $toClassId,
        ?string $reason = null,
        ?int $transferredById = null,
    ): array {
        try {
            // Revoke from current class
            $revokeResult = $this->revokeAssignment(
                $teacherId,
                $fromClassId,
                "Transfer: $reason",
                $transferredById,
            );

            if (!$revokeResult['success']) {
                throw new Exception("Failed to revoke previous assignment: {$revokeResult['message']}");
            }

            // Assign to new class
            $assignResult = $this->assignTeacher(
                $teacherId,
                $toClassId,
                $reason ?? 'transfer',
                "Transferred from class ID {$fromClassId}",
                $transferredById,
            );

            if (!$assignResult['success']) {
                throw new Exception("Failed to assign to new class: {$assignResult['message']}");
            }

            Log::info("Teacher transferred successfully", [
                'teacher_id' => $teacherId,
                'from_class' => $fromClassId,
                'to_class' => $toClassId,
                'reason' => $reason,
            ]);

            return [
                'success' => true,
                'message' => "Teacher transferred successfully",
                'previous_assignment' => $revokeResult['assignment'],
                'new_assignment' => $assignResult['assignment'],
            ];
        } catch (Exception $e) {
            Log::error("Failed to transfer teacher: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get assignment history for a specific teacher
     */
    public function getTeacherAssignmentHistory(int $teacherId): array
    {
        try {
            $assignments = AssignmentHistory::forTeacher($teacherId)
                ->with(['classe', 'oldTeacher', 'newTeacher', 'assignedBy'])
                ->orderBy('assigned_at', 'desc')
                ->get();

            return [
                'success' => true,
                'data' => $assignments,
                'count' => $assignments->count(),
            ];
        } catch (Exception $e) {
            Log::error("Failed to get assignment history: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get assignment history for a specific class
     */
    public function getClassAssignmentHistory(int $classId): array
    {
        try {
            $assignments = AssignmentHistory::forClass($classId)
                ->with(['oldTeacher', 'newTeacher', 'assignedBy'])
                ->orderBy('assigned_at', 'desc')
                ->get();

            return [
                'success' => true,
                'data' => $assignments,
                'count' => $assignments->count(),
            ];
        } catch (Exception $e) {
            Log::error("Failed to get class assignment history: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all active main teachers
     */
    public function getActiveMainTeachers(): array
    {
        try {
            $teachers = User::where('is_main_teacher', true)
                ->where('is_active', true)
                ->with('mainTeacherClass')
                ->get();

            return [
                'success' => true,
                'data' => $teachers,
                'count' => $teachers->count(),
            ];
        } catch (Exception $e) {
            Log::error("Failed to get active main teachers: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate teacher exists and is active
     */
    private function validateTeacher(int $teacherId): ?User
    {
        return User::where('id', $teacherId)
            ->where('is_active', true)
            ->whereIn('role', ['professeur', 'prof_principal'])
            ->first();
    }

    /**
     * Validate class exists and is active
     */
    private function validateClass(int $classId): ?Classe
    {
        return Classe::where('id', $classId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Archive previous assignment for the old teacher
     */
    private function archivePreviousAssignment(
        User $oldTeacher,
        Classe $class,
        ?int $assignedById = null,
    ): void {
        // Mark the old assignment as archived
        $previousAssignment = AssignmentHistory::where('new_teacher_id', $oldTeacher->id)
            ->where('class_id', $class->id)
            ->active()
            ->first();

        if ($previousAssignment) {
            $previousAssignment->markAsEnded("Replaced by new main teacher assignment");
        }

        // Update the old teacher's record
        $oldTeacher->update([
            'is_main_teacher' => false,
            'class_id' => null,
        ]);
    }

    /**
     * Create a new assignment history entry
     */
    private function createAssignmentHistory(
        ?int $oldTeacherId,
        int $newTeacherId,
        int $classId,
        ?string $reason,
        ?string $notes,
        ?int $assignedById = null,
    ): AssignmentHistory {
        return AssignmentHistory::create([
            'old_teacher_id' => $oldTeacherId,
            'new_teacher_id' => $newTeacherId,
            'class_id' => $classId,
            'reason' => $reason,
            'notes' => $notes,
            'status' => 'active',
            'assigned_by' => $assignedById,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Update teacher's assignment status
     */
    private function updateTeacherAssignment(User $teacher, int $classId): void
    {
        $teacher->update([
            'is_main_teacher' => true,
            'class_id' => $classId,
        ]);
    }

    /**
     * Update class's main teacher
     */
    private function updateClassMainTeacher(Classe $class, int $teacherId): void
    {
        $class->update([
            'prof_principal_id' => $teacherId,
        ]);
    }
}
