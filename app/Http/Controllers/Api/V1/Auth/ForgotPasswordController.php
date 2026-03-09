<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            return response()->json([
                'success' => $status === Password::RESET_LINK_SENT,
                'message' => __($status),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send reset link'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => bcrypt($password),
                        'remember_token' => Str::random(60),
                    ])->save();
                }
            );

            return response()->json([
                'success' => $status === Password::PASSWORD_RESET,
                'message' => __($status),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Password reset failed'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
