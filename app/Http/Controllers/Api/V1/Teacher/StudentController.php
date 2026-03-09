<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function myStudents(Request $request)
    {
        return response()->json(['students' => []]);
    }

    public function search(Request $request)
    {
        return response()->json(['results' => []]);
    }
}
