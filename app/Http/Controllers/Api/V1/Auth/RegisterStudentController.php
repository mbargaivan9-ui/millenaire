<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Http\Requests\StudentRegisterRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class RegisterStudentController extends Controller
{
    public function register(StudentRegisterRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
                'is_active' => true,
            ]);

            Student::create([
                'user_id' => $user->id,
                'matricule' => 'STU-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student registered successfully',
                'user' => $user,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Registration failed: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
