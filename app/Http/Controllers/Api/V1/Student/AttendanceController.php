<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Student Attendance',
            'data' => []
        ]);
    }
}
