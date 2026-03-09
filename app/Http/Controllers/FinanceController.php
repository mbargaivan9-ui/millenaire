<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index()
    {
        $totalRevenue = Payment::where('status', 'completed')
            ->sum('amount');
        
        $paidAmount = Payment::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
        
        $unpaidAmount = Payment::where('status', 'pending')
            ->sum('amount');
        
        // Get unpaid students - those with pending payments
        $unpaidStudents = Student::whereHas('payments', function($q) {
            $q->where('status', 'pending');
        })->count();
        
        $collectionRate = $totalRevenue > 0 
            ? round(($paidAmount / $totalRevenue) * 100) 
            : 0;
        
        $transactions = Payment::with('student')
            ->latest()
            ->take(10)
            ->get();
        
        $unpaidStudentsList = Student::whereHas('payments', function($q) {
            $q->where('status', 'pending');
        })->with('user')->get();
        
        $students = User::where('role', 'student')
            ->get();
        
        // Get paid students count
        $paidStudentsCount = Student::whereHas('payments', function($q) {
            $q->where('status', 'completed');
        })->count();
        
        // Get pending students count
        $pendingStudentsCount = Student::whereHas('payments', function($q) {
            $q->where('status', 'pending');
        })->count();
        
        // Get classes with payment statistics
        $classes = Classe::with('students')
            ->get()
            ->map(function($class) {
                $classStudents = $class->students;
                $studentIds = $classStudents->pluck('id');
                
                $totalDue = Payment::whereIn('student_id', $studentIds)->sum('amount');
                $totalPaid = Payment::whereIn('student_id', $studentIds)
                    ->where('status', 'completed')
                    ->sum('amount');
                $pending = Payment::whereIn('student_id', $studentIds)
                    ->where('status', 'pending')
                    ->sum('amount');
                
                return [
                    'class_id' => $class->id,
                    'class_name' => $class->name,
                    'total_students' => $classStudents->count(),
                    'total_class_due' => $totalDue,
                    'total_class_paid' => $totalPaid,
                    'total_pending' => $pending,
                    'collection_rate' => $totalDue > 0 ? round(($totalPaid / $totalDue) * 100) : 0,
                ];
            })
            ->all();
        
        $statistics = [
            'total_paid' => $paidAmount,
            'total_pending' => $unpaidAmount,
            'total_due' => $totalRevenue,
            'total_revenue' => $totalRevenue,
            'collection_rate' => $collectionRate,
            'unpaid_students' => $unpaidStudents,
            'paid_students' => $paidStudentsCount,
            'pending_students' => $pendingStudentsCount
        ];
        
        return view('admin.finance.index', [
            'totalRevenue' => $totalRevenue,
            'paidAmount' => $paidAmount,
            'unpaidAmount' => $unpaidAmount,
            'collectionRate' => $collectionRate,
            'unpaidStudents' => $unpaidStudents,
            'overdueCount' => $pendingStudentsCount,
            'classes' => $classes,
            'transactions' => $transactions,
            'unpaidStudentsList' => $unpaidStudentsList,
            'students' => $students,
            'monthlyRevenue' => $this->getMonthlyRevenue(),
            'statistics' => $statistics,
            'paidStudents' => $paidStudentsCount,
            'pendingStudents' => $pendingStudentsCount
        ]);
    }
    
    private function getMonthlyRevenue()
    {
        return Payment::selectRaw(
            'MONTH(created_at) as month, SUM(amount) as total'
        )
        ->where('status', 'completed')
        ->whereYear('created_at', now()->year)
        ->groupByRaw('MONTH(created_at)')
        ->pluck('total', 'month')
        ->toArray();
    }
    
    public function export()
    {
        // Implémentation d'export CSV
    }
    
    public function apiOverdueStudents()
    {
        $overdueStudents = Student::whereHas('payments', function($q) {
            $q->where('status', 'pending');
        })->with(['user', 'classe'])
        ->get()
        ->map(function($student) {
            $pendingPayments = $student->payments()
                ->where('status', 'pending')
                ->latest()
                ->first();
            
            return [
                'student_id' => $student->id,
                'student_name' => $student->user->name ?? 'N/A',
                'class_name' => $student->classe?->name ?? 'N/A',
                'final_amount_due' => $student->payments()->sum('amount'),
                'days_until_deadline' => $pendingPayments ? 
                    now()->diffInDays($pendingPayments->created_at) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $overdueStudents
        ]);
    }

    public function apiUnpaidStudents()
    {
        $unpaidStudents = Student::whereHas('payments', function($q) {
            $q->where('status', 'pending');
        })->with(['user', 'classe'])
        ->get()
        ->map(function($student) {
            $totalDue = $student->payments()->sum('amount');
            $totalPaid = $student->payments()
                ->where('status', 'completed')
                ->sum('amount');
            
            return [
                'student_id' => $student->id,
                'student_name' => $student->user->name ?? 'N/A',
                'class_name' => $student->classe?->name ?? 'N/A',
                'total_due' => $totalDue,
                'total_paid' => $totalPaid,
                'pending_amount' => $totalDue - $totalPaid,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $unpaidStudents
        ]);
    }

    public function apiSchoolReport()
    {
        $report = [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'total_pending' => Payment::where('status', 'pending')->sum('amount'),
            'total_students' => Student::count(),
            'students_paid' => Student::whereHas('payments', function($q) {
                $q->where('status', 'completed');
            })->count(),
            'students_pending' => Student::whereHas('payments', function($q) {
                $q->where('status', 'pending');
            })->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    public function apiTreasuryReport()
    {
        $monthlyData = Payment::selectRaw(
            'MONTH(created_at) as month, SUM(amount) as total'
        )
        ->where('status', 'completed')
        ->whereYear('created_at', now()->year)
        ->groupByRaw('MONTH(created_at)')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $monthlyData
        ]);
    }

    public function apiTransactions()
    {
        $transactions = Payment::with(['student.user', 'payer'])
            ->latest()
            ->take(50)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'student_name' => $transaction->student?->user?->name ?? 'N/A',
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'date' => $transaction->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    public function student(User $student)
    {
        // Vue détaillée paiement étudiant
    }
}
