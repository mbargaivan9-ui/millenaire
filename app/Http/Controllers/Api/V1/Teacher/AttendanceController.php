<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['attendance' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['success' => true], 201);
    }
}
