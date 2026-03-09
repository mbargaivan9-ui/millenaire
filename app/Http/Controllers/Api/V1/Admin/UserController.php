<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Users List',
            'data' => []
        ]);
    }

    public function store(): JsonResponse
    {
        return response()->json([
            'message' => 'User created',
            'data' => []
        ]);
    }

    public function show($id): JsonResponse
    {
        return response()->json([
            'message' => 'User details',
            'data' => []
        ]);
    }

    public function update($id): JsonResponse
    {
        return response()->json([
            'message' => 'User updated',
            'data' => []
        ]);
    }

    public function destroy($id): JsonResponse
    {
        return response()->json([
            'message' => 'User deleted',
            'data' => []
        ]);
    }
}
