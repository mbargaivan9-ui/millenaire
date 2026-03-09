<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BulletinController extends Controller
{
    public function getClassBulletins(Request $request, $classId, $sequence)
    {
        return response()->json(['bulletins' => []]);
    }

    public function show(Request $request, $id)
    {
        return response()->json(['bulletin' => []]);
    }

    public function lockSequence(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function exportPDF(Request $request)
    {
        return response()->json(['success' => true]);
    }
}
