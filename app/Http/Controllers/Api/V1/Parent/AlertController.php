<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request, $childId)
    {
        return response()->json(['alerts' => []]);
    }
}
