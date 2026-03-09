<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\DTOs\LoginCredentialsDTO;
use App\Contracts\AuthenticationServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct(
        private AuthenticationServiceInterface $authService,
    ) {}

    /**
     * API Login endpoint
     * Returns a Sanctum token for API authentication
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = LoginCredentialsDTO::fromRequest($validated);

        try {
            $user = $this->authService->authenticate($credentials);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication service unavailable'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if (!$user || !$this->authService->isUserActive($user)) {
            return response()->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        // Log the login
        try {
            $this->authService->logLogin($user);
        } catch (\Exception $e) {
            // Continue anyway if logging fails
        }

        // Create token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * API Logout endpoint
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logLogout($request->user());
            $request->user()->currentAccessToken()->delete();
        } catch (\Exception $e) {
            // Continue anyway
        }

        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }
}
