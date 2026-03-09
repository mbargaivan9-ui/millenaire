<?php

namespace App\Http\Controllers;

use App\Models\KpiSnapshot;
use App\Models\ActivityLog;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display dashboard with key performance indicators
     */
    public function dashboard()
    {
        $kpis = KpiSnapshot::latest()->first();
        
        return view('reports.dashboard', compact('kpis'));
    }

    /**
     * Display activity logs
     */
    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with('user');
        
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }
        
        if ($request->has('model')) {
            $query->where('loggable_type', $request->model);
        }
        
        if ($request->has('user_id')) {
            $query->where('changed_by', $request->user_id);
        }
        
        $logs = $query->latest()->paginate(50);
        
        return view('reports.activity-logs', compact('logs'));
    }

    /**
     * Display admin audit logs
     */
    public function auditLogs(Request $request)
    {
        $query = AdminAuditLog::with('user');
        
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }
        
        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $logs = $query->latest()->paginate(50);
        
        return view('reports.audit-logs', compact('logs'));
    }

    /**
     * Generate financial report
     */
    public function financialReport(Request $request)
    {
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : now()->endOfMonth();
        
        $payments = \App\Models\Payment::whereBetween('paid_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();
        
        $totalRevenue = $payments->sum('amount');
        $transactionCount = $payments->count();
        $averageAmount = $transactionCount > 0 ? $totalRevenue / $transactionCount : 0;
        
        return view('reports.financial', compact('payments', 'totalRevenue', 'transactionCount', 'averageAmount', 'startDate', 'endDate'));
    }

    /**
     * Generate student performance report
     */
    public function studentPerformance(Request $request)
    {
        $classId = $request->class_id;
        
        $students = \App\Models\Student::where('classe_id', $classId)
            ->with(['grades', 'attendances'])
            ->get();
        
        $performance = $students->map(function ($student) {
            return [
                'student' => $student,
                'average_grade' => $student->grades->avg('score'),
                'present_count' => $student->attendances->where('status', 'present')->count(),
                'absent_count' => $student->attendances->where('status', 'absent')->count(),
            ];
        });
        
        return view('reports.student-performance', compact('performance', 'classId'));
    }

    /**
     * Generate attendance report
     */
    public function attendanceReport(Request $request)
    {
        $classId = $request->class_id;
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : now()->endOfMonth();
        
        $attendances = \App\Models\Attendance::whereBetween('date', [$startDate, $endDate])
            ->with(['student.classe', 'student.user'])
            ->get();
        
        return view('reports.attendance', compact('attendances', 'startDate', 'endDate'));
    }

    /**
     * Export report as PDF
     */
    public function exportPdf($reportType, Request $request)
    {
        // This would typically generate a PDF using a library like TCPDF or DomPDF
        return response()->json(['message' => 'PDF export endpoint']);
    }

    /**
     * Export report as CSV
     */
    public function exportCsv($reportType, Request $request)
    {
        // This would generate CSV data
        return response()->json(['message' => 'CSV export endpoint']);
    }
}
