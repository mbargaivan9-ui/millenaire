<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChildrenController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['children' => []]);
    }

    public function performance(Request $request, $childId)
    {
        return response()->json(['performance' => []]);
    }
}
