<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class FinancialController extends Controller
{
    public function overview(): JsonResponse
    {
        return response()->json([]);
    }

    public function payments(): JsonResponse
    {
        return response()->json([]);
    }

    public function unpaidStudents(): JsonResponse
    {
        return response()->json([]);
    }

    public function statistics(): JsonResponse
    {
        return response()->json([]);
    }

    public function export(): JsonResponse
    {
        return response()->json([]);
    }
}
