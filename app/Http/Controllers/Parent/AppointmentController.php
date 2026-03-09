<?php

/**
 * Parent\AppointmentController
 *
 * Gestion des rendez-vous parent → enseignant
 * Phase 7 — Section 8.3
 *
 * @package App\Http\Controllers\Parent
 */

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAvailability;
use App\Notifications\AppointmentRequestedNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Formulaire de création d'un RDV.
     */
    public function create(Request $request)
    {
        $user     = auth()->user();
        $guardian = $user->guardian;

        $student = $guardian
            ? $guardian->students()->with('user', 'classe')->find($request->student_id)
            : null;

        if (!$student) {
            return redirect()->route('parent.dashboard')->with('error', 'Élève introuvable.');
        }

        // Enseignants disponibles pour la classe de l'élève
        $teachers = Teacher::whereHas('classes', fn($q) => $q->where('classes.id', $student->class_id))
            ->with('user', 'subjects', 'availabilities')
            ->where('is_active', true)
            ->get();

        return view('parent.appointments.create', compact('student', 'teachers'));
    }

    /**
     * Retourner les créneaux disponibles d'un enseignant (AJAX).
     */
    public function slots(int $teacherId): JsonResponse
    {
        $availabilities = TeacherAvailability::where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->get();

        $slots = [];
        foreach ($availabilities as $avail) {
            // Générer créneaux de 30 minutes dans le plage
            $start = Carbon::parse($avail->start_time);
            $end   = Carbon::parse($avail->end_time);

            while ($start->lt($end)) {
                $slotEnd  = $start->copy()->addMinutes(30);
                $label    = $start->format('H:i') . '–' . $slotEnd->format('H:i');

                // Vérifier si ce créneau est déjà pris
                $taken = Appointment::where('teacher_id', $teacherId)
                    ->where('status', '!=', 'cancelled')
                    ->whereDate('scheduled_at', now()->next($avail->day_of_week))
                    ->whereTime('scheduled_at', $start->format('H:i:s'))
                    ->exists();

                $slots[] = [
                    'id'        => "{$avail->id}_{$start->format('Hi')}",
                    'day'       => $avail->day_of_week,
                    'label'     => $label,
                    'taken'     => $taken,
                    'avail_id'  => $avail->id,
                    'time'      => $start->format('H:i'),
                ];

                $start->addMinutes(30);
            }
        }

        return response()->json(['slots' => $slots]);
    }

    /**
     * Enregistrer la demande de RDV.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_id'        => 'required|exists:teachers,id',
            'student_id'        => 'required|exists:students,id',
            'availability_slot' => 'required|string',
            'notes'             => 'nullable|string|max:500',
        ]);

        // Vérifier que l'élève appartient au tuteur
        $guardian = auth()->user()->guardian;
        $student  = $guardian?->students()->find($request->student_id);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        // Parser le slot: avail_id_HHMM
        [$availId, $time] = explode('_', $request->availability_slot, 2);
        $avail = TeacherAvailability::find($availId);
        if (!$avail) {
            return response()->json(['success' => false, 'message' => 'Créneau invalide'], 422);
        }

        // Calculer la date du prochain jour de la semaine concerné
        $scheduledAt = now()->next($avail->day_of_week)->setTimeFromTimeString(
            substr($time, 0, 2) . ':' . substr($time, 2, 2) . ':00'
        );

        $appointment = Appointment::create([
            'teacher_id'   => $request->teacher_id,
            'parent_id'    => auth()->id(),
            'student_id'   => $request->student_id,
            'scheduled_at' => $scheduledAt,
            'status'       => 'pending',
            'notes'        => $request->notes,
        ]);

        // Notifier l'enseignant
        $teacher = Teacher::with('user')->find($request->teacher_id);
        $teacher?->user?->notify(new AppointmentRequestedNotification($appointment));

        // Logger
        activity()
            ->causedBy(auth()->user())
            ->performedOn($appointment)
            ->log('RDV demandé');

        return response()->json(['success' => true, 'appointment_id' => $appointment->id]);
    }

    /**
     * Annuler un RDV.
     */
    public function cancel(int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);

        // Vérifier ownership
        if ($appointment->parent_id !== auth()->id()) {
            return response()->json(['success' => false], 403);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json(['success' => true]);
    }
}
