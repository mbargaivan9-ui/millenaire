<?php

/**
 * Teacher\AppointmentController — Gestion des RDV (côté enseignant)
 */

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Liste des RDV de l'enseignant.
     */
    public function index()
    {
        $teacher = auth()->user()->teacher;

        $appointments = Appointment::where('teacher_id', $teacher->id)
            ->with('student.user', 'student.classe')
            ->orderByDesc('scheduled_at')
            ->paginate(20);

        $upcoming = Appointment::where('teacher_id', $teacher->id)
            ->where('scheduled_at', '>', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('student.user')
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        return view('teacher.appointments.index', compact('appointments', 'upcoming'));
    }

    /**
     * Confirmer ou refuser un RDV.
     */
    public function update(Request $request, int $id)
    {
        $teacher     = auth()->user()->teacher;
        $appointment = Appointment::where('teacher_id', $teacher->id)->findOrFail($id);

        $request->validate(['status' => 'required|in:confirmed,cancelled', 'note' => 'nullable|string|max:500']);

        $appointment->update([
            'status'       => $request->status,
            'teacher_note' => $request->note,
        ]);

        // Notify parent
        $appointment->student?->guardian?->user?->notify(
            new \App\Notifications\AppointmentStatusNotification($appointment)
        );

        return response()->json(['success' => true]);
    }
}
