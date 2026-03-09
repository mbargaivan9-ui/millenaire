<?php

/**
 * ProfileController — Gestion du Profil Utilisateur
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => auth()->user()]);
    }

    public function security(): View
    {
        return view('profile.security', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'first_name' => ['nullable', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:20'],
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $path = $request->file('avatar')->store("avatars/{$user->id}", 'public');
            $data['avatar_url'] = $path;
        }

        $user->update($data);
        activity()->causedBy($user)->log('Profil mis à jour');

        return back()->with('success', app()->getLocale() === 'fr' ? 'Profil mis à jour.' : 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => app()->getLocale() === 'fr' ? 'Mot de passe actuel incorrect.' : 'Current password is incorrect.']);
        }

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', app()->getLocale() === 'fr' ? 'Mot de passe modifié.' : 'Password updated.');
    }

    public function updateLanguage(Request $request)
    {
        $locale = in_array($request->locale, ['fr', 'en']) ? $request->locale : 'fr';
        auth()->user()->update(['preferred_language' => $locale]);
        session(['locale' => $locale]);
        return back();
    }
}
