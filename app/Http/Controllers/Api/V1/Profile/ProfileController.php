<?php

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'preferred_language' => 'sometimes|in:en,fr',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $request->user()->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 422);
        }

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['success' => true, 'message' => 'Password changed']);
    }
}
