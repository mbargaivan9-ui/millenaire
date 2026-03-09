<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Affiche tous les horaires
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['classe', 'subject', 'teacher.user']);

        if ($request->filled('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }

        $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->paginate(30);
        $classes = Classe::active()->get();

        return view('admin.schedule.index', compact('schedules', 'classes'));
    }

    /**
     * Crée un nouvel horaire
     */
    public function create()
    {
        $classes = Classe::active()->get();
        $subjects = Subject::active()->get();
        $teachers = Teacher::with('user')->get();

        return view('admin.schedule.create', compact('classes', 'subjects', 'teachers'));
    }

    /**
     * Stocke un nouvel horaire
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:50',
        ]);

        Schedule::create($validated);

        return redirect()->route('admin.schedule.index')
                        ->with('success', 'Horaire créé');
    }

    /**
     * Édite un horaire
     */
    public function edit(Schedule $schedule)
    {
        $schedule->load(['classe', 'subject', 'teacher']);
        $classes = Classe::active()->get();
        $subjects = Subject::active()->get();
        $teachers = Teacher::with('user')->get();

        return view('admin.schedule.edit', compact('schedule', 'classes', 'subjects', 'teachers'));
    }

    /**
     * Met à jour un horaire
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:50',
        ]);

        $schedule->update($validated);

        return redirect()->route('admin.schedule.index')
                        ->with('success', 'Horaire mis à jour');
    }

    /**
     * Supprime un horaire
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('admin.schedule.index')
                        ->with('success', 'Horaire supprimé');
    }

    /**
     * Vue d'emploi du temps pour une classe
     */
    public function viewClass(Classe $classe)
    {
        $schedules = Schedule::where('classe_id', $classe->id)
            ->with(['subject', 'teacher.user'])
            ->get()
            ->groupBy('day_of_week');

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return view('admin.schedule.view', compact('classe', 'schedules', 'days'));
    }

    /**
     * Vue d'emploi du temps pour un enseignant
     */
    public function viewTeacher(Teacher $teacher)
    {
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->with(['classe', 'subject'])
            ->get()
            ->groupBy('day_of_week');

        $days = ['monday'=>'Lundi', 'tuesday'=>'Mardi', 'wednesday'=>'Mercredi', 'thursday'=>'Jeudi', 'friday'=>'Vendredi', 'saturday'=>'Samedi', 'sunday'=>'Dimanche'];

        return view('admin.schedule.teacher', compact('teacher', 'schedules', 'days'));
    }

    /**
     * Export en format iCal
     */
    public function export(Classe $classe)
    {
        $schedules = Schedule::where('classe_id', $classe->id)
            ->with(['subject', 'teacher.user'])
            ->get();

        $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Millenaire//EN\r\n";

        foreach ($schedules as $schedule) {
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "DTSTART:20260101T" . str_replace(':', '', $schedule->start_time) . "00Z\r\n";
            $ical .= "DTEND:20260101T" . str_replace(':', '', $schedule->end_time) . "00Z\r\n";
            $ical .= "SUMMARY:{$schedule->subject->name}\r\n";
            $ical .= "LOCATION:{$schedule->room}\r\n";
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        return response($ical, 200)
                ->header('Content-Type', 'text/calendar')
                ->header('Content-Disposition', 'attachment; filename="emploi_temps.ics"');
    }
}
