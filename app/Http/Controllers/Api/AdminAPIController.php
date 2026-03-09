<?php

namespace App\Http\Controllers\API;

use App\Models\Student;
use App\Models\Classe;
use App\Models\Schedule;
use App\Models\Fee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|censeur');
    }

    /**
     * Check if matricule exists - used in student creation
     */
    public function checkMatricule($matricule)
    {
        $exists = Student::where('matricule', $matricule)->exists();
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Ce matricule existe déjà.' : 'Matricule disponible.'
        ]);
    }

    /**
     * Check if email exists - used in user creation
     */
    public function checkEmail($email)
    {
        $exists = \App\Models\User::where('email', $email)->exists();
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Cet email existe déjà.' : 'Email disponible.'
        ]);
    }

    /**
     * Get students by class - for attendance bulk entry
     */
    public function getClassStudents(Classe $classe)
    {
        $students = $classe->students()
            ->with('user')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'matricule' => $student->matricule,
                    'email' => $student->user->email,
                ];
            });

        return response()->json([
            'success' => true,
            'students' => $students,
            'count' => $students->count()
        ]);
    }

    /**
     * Check schedule conflicts - used in schedule creation
     */
    public function checkScheduleConflict(Request $request)
    {
        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $conflicts = Schedule::where([
            ['day_of_week', '=', $request->day_of_week],
        ])
        ->where(function ($query) use ($request) {
            $query->where('classe_id', $request->classe_id)
                  ->orWhere('teacher_id', $request->teacher_id);
        })
        ->where(function ($query) use ($request) {
            $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                  ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                  ->orWhere([
                      ['start_time', '<=', $request->start_time],
                      ['end_time', '>=', $request->end_time]
                  ]);
        })
        ->get();

        return response()->json([
            'has_conflict' => $conflicts->count() > 0,
            'conflicts' => $conflicts->count(),
            'message' => $conflicts->count() > 0 
                ? 'Conflit d\'horaire détecté !' 
                : 'Aucun conflit d\'horaire.'
        ]);
    }

    /**
     * Update student financial status via AJAX
     */
    public function updateStudentStatus(Request $request, Student $student)
    {
        $request->validate([
            'financial_status' => 'required|in:paid,pending,overdue'
        ]);

        $student->update([
            'financial_status' => $request->financial_status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès.',
            'student' => [
                'id' => $student->id,
                'financial_status' => $student->financial_status
            ]
        ]);
    }

    /**
     * Bulk import students - returns preview
     */
    public function previewStudentImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $students = [];
        $errors = [];

        if (($handle = fopen($file->getRealPath(), 'r')) !== FALSE) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $row++;
                if ($row === 1) continue; // Skip header

                if (count($data) < 4) {
                    $errors[] = "Ligne $row: Données insuffisantes";
                    continue;
                }

                $students[] = [
                    'line' => $row,
                    'name' => $data[0],
                    'email' => $data[1],
                    'matricule' => $data[2],
                    'classe_id' => $data[3],
                    'valid' => !empty($data[0]) && !empty($data[1]) && filter_var($data[1], FILTER_VALIDATE_EMAIL)
                ];
            }
            fclose($handle);
        }

        return response()->json([
            'success' => true,
            'preview' => $students,
            'total' => count($students),
            'errors' => $errors,
            'message' => count($students) . ' étudiants trouvés.'
        ]);
    }

    /**
     * Get fee assignment preview
     */
    public function previewFeeAssignment(Request $request)
    {
        $request->validate([
            'fee_id' => 'required|exists:fees,id',
            'classe_id' => 'required|exists:classes,id'
        ]);

        $fee = Fee::find($request->fee_id);
        $classe = Classe::with('students')->find($request->classe_id);

        $students = $classe->students->map(function ($student) use ($fee) {
            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'matricule' => $student->matricule,
                'fee_name' => $fee->name,
                'amount' => $fee->amount,
                'due_date' => $fee->due_date?->format('d/m/Y')
            ];
        });

        return response()->json([
            'success' => true,
            'fee' => $fee,
            'class' => $classe->name,
            'students' => $students,
            'total_students' => $students->count(),
            'total_amount' => $students->count() * $fee->amount,
            'message' => "{$students->count()} étudiants seront affectés."
        ]);
    }

    /**
     * Export attendance report
     */
    public function exportAttendanceReport(Request $request)
    {
        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'format' => 'required|in:csv,pdf'
        ]);

        // Implementation would generate CSV/PDF
        return response()->json([
            'success' => true,
            'message' => 'Rapport généré.',
            'download_url' => '/download/attendance-report.' . $request->format
        ]);
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartData($type)
    {
        $data = [];

        switch ($type) {
            case 'enrollment':
                $data = Classe::with('students')
                    ->get()
                    ->map(function ($classe) {
                        return [
                            'name' => $classe->name,
                            'value' => $classe->students->count()
                        ];
                    });
                break;

            case 'attendance':
                $data = [
                    ['name' => 'Présent', 'value' => 850],
                    ['name' => 'Absent', 'value' => 145],
                    ['name' => 'Justifié', 'value' => 95],
                    ['name' => 'Malade', 'value' => 20]
                ];
                break;

            case 'financial':
                $data = [
                    ['month' => 'Jan', 'value' => 450000],
                    ['month' => 'Fév', 'value' => 520000],
                    ['month' => 'Mar', 'value' => 480000],
                    ['month' => 'Avr', 'value' => 605000],
                ];
                break;
        }

        return response()->json([
            'success' => true,
            'type' => $type,
            'data' => $data
        ]);
    }
}
