<?php

/**
 * Admin\FinanceController
 *
 * Gestion financière — paiements Mobile Money, exports, reçus.
 * Phase 10 — Section 11.2
 *
 * @package App\Http\Controllers\Admin
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Student;
use App\Services\BulletinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        // Stats financières
        $stats = Cache::remember('admin.finance.stats', 180, function () {
            $thisMonth = now()->startOfMonth();

            // Labels 6 derniers mois
            $labels = [];
            $data   = [];
            for ($i = 5; $i >= 0; $i--) {
                $m       = now()->subMonths($i);
                $labels[] = $m->locale(app()->getLocale())->isoFormat('MMM YY');
                $data[]   = Payment::whereYear('created_at', $m->year)
                    ->whereMonth('created_at', $m->month)
                    ->where('status', 'success')
                    ->sum('amount');
            }

            $totalStudents        = Student::count();
            $familiesUptodate     = Student::whereHas('payments', fn($q) => $q->where('status', 'success')->whereMonth('created_at', now()->month))->count();
            $familiesOverdue      = $totalStudents - $familiesUptodate;

            return [
                'total_collected'        => Payment::where('status', 'success')->whereBetween('created_at', [$thisMonth, now()])->sum('amount'),
                'pending_amount'         => Payment::where('status', 'pending')->sum('amount'),
                'families_uptodate'      => $familiesUptodate,
                'families_overdue'       => $familiesOverdue,
                'total_students'         => $totalStudents,
                'revenue_6months_labels' => $labels,
                'revenue_6months'        => $data,
            ];
        });

        // Paiements paginés avec filtres
        $query = Payment::with('student.user', 'student.classe')
            ->orderByDesc('created_at');

        if ($request->filled('operator')) {
            $query->where('operator', $request->operator);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student.user', fn($q) => $q->where('name', 'like', "%$search%"))
                  ->orWhere('transaction_ref', 'like', "%$search%")
                  ->orWhere('phone_number', 'like', "%$search%");
        }

        $payments = $query->paginate(25)->withQueryString();

        // Élèves en retard
        $overdueStudents = Student::whereDoesntHave('payments', fn($q) => $q->where('status', 'success')->whereMonth('created_at', now()->month))
            ->with('user', 'classe')
            ->take(10)
            ->get();

        return view('admin.finance', compact('payments', 'stats', 'overdueStudents'));
    }

    /**
     * Générer un reçu PDF pour un paiement.
     */
    public function receipt(int $id)
    {
        $payment = Payment::with('student.user', 'student.classe')->findOrFail($id);
        $pdf     = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.payment-receipt', compact('payment'));
        return $pdf->download("recu-{$payment->transaction_ref}.pdf");
    }

    /**
     * Exporter les paiements en Excel.
     */
    public function export()
    {
        $filename = 'paiements-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new \App\Exports\PaymentsExport(), $filename);
    }
}
