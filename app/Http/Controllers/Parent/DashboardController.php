<?php

/**
 * Parent\DashboardController
 *
 * Tableau de bord espace parent.
 * Phase 7 — Section Parent
 *
 * @package App\Http\Controllers\Parent
 */

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bulletin;
use App\Models\Mark;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user     = auth()->user();
        $guardian = $user->guardian;

        // Tous les enfants rattachés à ce tuteur
        $children = $guardian
            ? $guardian->students()->with('user', 'classe.subjects')->get()
            : collect();

        // Sélection de l'enfant (multi-compte)
        $selectedChild = $children->firstWhere('id', $request->child_id)
            ?? $children->first();

        if (!$selectedChild) {
            return view('parent.dashboard', compact('children'));
        }

        // Données de l'enfant sélectionné
        $bulletins = Bulletin::where('student_id', $selectedChild->id)
            ->where('status', 'published')
            ->orderByDesc('term')
            ->orderByDesc('sequence')
            ->get();

        // Dernière séquence
        $lastBulletin = $bulletins->first();
        $bulletinData = $lastBulletin
            ? ['moyenne' => $lastBulletin->moyenne, 'rang' => $lastBulletin->rang]
            : [];

        // Notes récentes (15 dernières)
        $recentGrades = Mark::where('student_id', $selectedChild->id)
            ->with('subject', 'teacher.user')
            ->orderByDesc('updated_at')
            ->take(15)
            ->get();

        // Absences du mois
        $recentAbsences = \App\Models\Absence::where('student_id', $selectedChild->id)
            ->whereMonth('date', now()->month)
            ->with('subject')
            ->orderByDesc('date')
            ->get();

        // Paiements en attente
        $pendingPayments = Payment::where('student_id', $selectedChild->id)
            ->where('status', 'pending')
            ->get();

        // RDV à venir
        $upcomingAppointments = Appointment::where('student_id', $selectedChild->id)
            ->where('scheduled_at', '>', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('teacher.user')
            ->orderBy('scheduled_at')
            ->take(3)
            ->get();

        return view('parent.dashboard', compact(
            'children',
            'selectedChild',
            'bulletins',
            'bulletinData',
            'recentGrades',
            'recentAbsences',
            'pendingPayments',
            'upcomingAppointments'
        ));
    }
}
