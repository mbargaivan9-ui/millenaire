<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class GradesController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Student Grades',
            'data' => []
        ]);
    }

    public function performance(): JsonResponse
    {
        return response()->json([
            'message' => 'Student Performance',
            'data' => []
        ]);
    }
}
