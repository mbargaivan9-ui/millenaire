<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Fee;
use App\Models\Classe;
use Illuminate\Pagination\Paginator;

/**
 * AdminService - Centralized operations for admin panel
 */
class AdminService
{
    /**
     * Get enrolled students count
     */
    public function getEnrolledStudentsCount(): int
    {
        return Student::count();
    }

    /**
     * Get active classes count
     */
    public function getActiveClassesCount(): int
    {
        return Classe::where('status', 'active')->count();
    }

    /**
     * Get today's attendance summary
     */
    public function getTodayAttendanceSummary(): array
    {
        $today = now()->toDateString();

        return [
            'total' => Attendance::whereDate('date', $today)->count(),
            'present' => Attendance::whereDate('date', $today)->where('status', 'present')->count(),
            'absent' => Attendance::whereDate('date', $today)->where('status', 'absent')->count(),
            'justified' => Attendance::whereDate('date', $today)->where('status', 'justified')->count(),
            'ill' => Attendance::whereDate('date', $today)->where('status', 'ill')->count(),
        ];
    }

    /**
     * Get enrollment by class
     */
    public function getEnrollmentByClass()
    {
        return Classe::with('students')
            ->get()
            ->map(function ($classe) {
                return [
                    'name' => $classe->name,
                    'students' => $classe->students->count(),
                    'level' => $classe->level ?? 'N/A'
                ];
            })->toArray();
    }

    /**
     * Get monthly financial summary
     */
    public function getMonthlyFinancialSummary(int $month = null): array
    {
        $month = $month ?? now()->month;

        return [
            'month' => $month,
            'pending' => Student::where('financial_status', 'pending')
                ->count(),
            'paid' => Student::where('financial_status', 'paid')
                ->count(),
            'overdue' => Student::where('financial_status', 'overdue')
                ->count(),
        ];
    }

    /**
     * Get student financial status distribution
     */
    public function getStudentFinancialDistribution(): array
    {
        return [
            'paid' => Student::where('financial_status', 'paid')->count(),
            'pending' => Student::where('financial_status', 'pending')->count(),
            'overdue' => Student::where('financial_status', 'overdue')->count(),
        ];
    }

    /**
     * Calculate attendance rate for student
     */
    public function getStudentAttendanceRate(Student $student): float
    {
        $totalClasses = Attendance::where('student_id', $student->id)->count();
        if ($totalClasses === 0) return 0;

        $present = Attendance::where('student_id', $student->id)
            ->where('status', 'present')
            ->count();

        return round(($present / $totalClasses) * 100, 2);
    }

    /**
     * Get class schedule for week
     */
    public function getClassWeekSchedule(Classe $classe, string $startDate = null)
    {
        $startDate = $startDate ? new \DateTime($startDate) : now();

        return Schedule::where('classe_id', $classe->id)
            ->get()
            ->groupBy('day_of_week')
            ->map(function ($schedules) {
                return $schedules->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'subject' => $schedule->subject->name ?? '—',
                        'teacher' => $schedule->teacher->user->name ?? '—',
                        'time' => "{$schedule->start_time} - {$schedule->end_time}",
                        'room' => $schedule->room ?? 'À définir',
                    ];
                });
            })->toArray();
    }

    /**
     * Export students to CSV
     */
    public function exportStudentsCSV(array $filters = [])
    {
        $students = $this->filterStudents($filters);

        return $students->map(function ($student) {
            return [
                'ID' => $student->id,
                'Nom' => $student->user->name,
                'Email' => $student->user->email,
                'Matricule' => $student->matricule,
                'Classe' => $student->classe->name,
                'Téléphone' => $student->phone,
                'Sexe' => $student->gender,
                'Statut Financier' => $student->financial_status,
                'Date d\'Inscription' => $student->created_at->format('Y-m-d'),
            ];
        })->toArray();
    }

    /**
     * Export attendance to CSV
     */
    public function exportAttendanceCSV(array $filters = [])
    {
        $query = Attendance::with('student', 'student.user', 'student.classe');

        if (!empty($filters['month'])) {
            $query->whereMonth('date', $filters['month']);
        }

        if (!empty($filters['classe_id'])) {
            $query->whereRelation('student', 'classe_id', '=', $filters['classe_id']);
        }

        return $query->get()->map(function ($record) {
            return [
                'Date' => $record->date->format('d/m/Y'),
                'Étudiant' => $record->student->user->name,
                'Classe' => $record->student->classe->name,
                'Matricule' => $record->student->matricule,
                'Statut' => $record->status,
                'Notes' => $record->notes,
            ];
        })->toArray();
    }

    /**
     * Filter students based on criteria
     */
    private function filterStudents(array $filters)
    {
        $query = Student::with('user', 'classe');

        if (!empty($filters['classe_id'])) {
            $query->where('classe_id', $filters['classe_id']);
        }

        if (!empty($filters['financial_status'])) {
            $query->where('financial_status', $filters['financial_status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhere('matricule', 'like', "%{$search}%");
        }

        return $query->get();
    }

    /**
     * Check system health/readiness
     */
    public function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabaseConnection(),
            'classes_count' => $this->getActiveClassesCount(),
            'students_count' => $this->getEnrolledStudentsCount(),
            'today_attendance' => $this->getTodayAttendanceSummary(),
            'schedules' => Schedule::count(),
            'fees' => Fee::where('status', 'active')->count(),
        ];
    }

    /**
     * Check database connection
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
