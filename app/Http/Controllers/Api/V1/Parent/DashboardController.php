<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['data' => 'Parent dashboard']);
    }
}
