<?php

/**
 * Teacher\AvailabilityController — Créneaux de disponibilité RDV
 */

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index()
    {
        $teacher        = auth()->user()->teacher;
        $availabilities = TeacherAvailability::where('teacher_id', $teacher->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('teacher.appointments.availabilities', compact('availabilities'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'day_of_week' => 'required|integer|between:1,6',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        $teacher = auth()->user()->teacher;

        $avail = TeacherAvailability::create([
            'teacher_id'  => $teacher->id,
            'day_of_week' => $request->day_of_week,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'is_active'   => true,
        ]);

        return response()->json(['success' => true, 'availability' => $avail]);
    }

    public function destroy(int $id): JsonResponse
    {
        $teacher = auth()->user()->teacher;
        TeacherAvailability::where('teacher_id', $teacher->id)->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
