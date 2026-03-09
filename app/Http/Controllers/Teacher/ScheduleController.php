<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Schedule;
use App\Models\Subject;
use Illuminate\Http\Request;

/**
 * Teacher\ScheduleController — Emploi du Temps Enseignant
 */
class ScheduleController extends Controller
{
    public function index()
    {
        $teacher = auth()->user()->teacher;

        $schedules = Schedule::where('teacher_id', $teacher?->id)
            ->where('is_active', true)
            ->with('subject', 'classe')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $days = app()->getLocale() === 'fr'
            ? ['monday'=>'Lundi', 'tuesday'=>'Mardi', 'wednesday'=>'Mercredi', 'thursday'=>'Jeudi', 'friday'=>'Vendredi', 'saturday'=>'Samedi', 'sunday'=>'Dimanche']
            : ['monday'=>'Monday', 'tuesday'=>'Tuesday', 'wednesday'=>'Wednesday', 'thursday'=>'Thursday', 'friday'=>'Friday', 'saturday'=>'Saturday', 'sunday'=>'Sunday'];

        return view('teacher.schedule.index', compact('schedules', 'days'));
    }
}
