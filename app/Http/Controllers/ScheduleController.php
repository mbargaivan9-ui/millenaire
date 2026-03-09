<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Classe;
use App\Models\ClassSubjectTeacher;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['classe', 'classSubjectTeacher.subject', 'classSubjectTeacher.teacher']);
        
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->has('day')) {
            $query->where('day_of_week', $request->day);
        }
        
        $schedules = $query->paginate(20);
        
        return view('schedules.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new schedule
     */
    public function create()
    {
        $classes = Classe::get();
        $classSubjectTeachers = ClassSubjectTeacher::with(['classe', 'subject', 'teacher'])->get();
        
        return view('schedules.create', compact('classes', 'classSubjectTeachers'));
    }

    /**
     * Store a newly created schedule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'class_subject_teacher_id' => 'required|exists:class_subject_teacher,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room_number' => 'nullable|string|max:50',
        ]);

        Schedule::create($validated);

        return redirect()->route('schedules.index')
            ->with('success', 'Emploi du temps créé avec succès');
    }

    /**
     * Display the specified schedule
     */
    public function show(Schedule $schedule)
    {
        return view('schedules.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified schedule
     */
    public function edit(Schedule $schedule)
    {
        $classes = Classe::get();
        $classSubjectTeachers = ClassSubjectTeacher::with(['classe', 'subject', 'teacher'])->get();
        
        return view('schedules.edit', compact('schedule', 'classes', 'classSubjectTeachers'));
    }

    /**
     * Update the specified schedule
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'class_subject_teacher_id' => 'required|exists:class_subject_teacher,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room_number' => 'nullable|string|max:50',
        ]);

        $schedule->update($validated);

        return redirect()->route('schedules.index')
            ->with('success', 'Emploi du temps mis à jour avec succès');
    }

    /**
     * Remove the specified schedule
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('schedules.index')
            ->with('success', 'Emploi du temps supprimé avec succès');
    }

    /**
     * View class schedule
     */
    public function viewClass(Classe $classe)
    {
        $schedules = Schedule::where('class_id', $classe->id)
            ->with(['classSubjectTeacher.subject', 'classSubjectTeacher.teacher'])
            ->get()
            ->groupBy('day_of_week');
        
        return view('schedules.class-view', compact('classe', 'schedules'));
    }

    /**
     * Export schedule as PDF
     */
    public function export(Classe $classe)
    {
        $schedules = Schedule::where('class_id', $classe->id)
            ->with(['classSubjectTeacher.subject', 'classSubjectTeacher.teacher'])
            ->get();
        
        return view('schedules.export', compact('classe', 'schedules'));
    }
}
