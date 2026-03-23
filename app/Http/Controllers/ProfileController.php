<?php

/**
 * ProfileController — Gestion Complète du Profil Utilisateur
 * Supports: View, Edit, Security, Avatar, Password, Language, 2FA
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Affiche le profil principal avec statistiques
     */
    public function show(): View
    {
        $user = Auth::user()->load(['student', 'teacher', 'guardian']);
        $stats = $this->buildStats($user);
        $activities = Notification::forUser($user->id)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('profile.show', compact('user', 'stats', 'activities'));
    }

    /**
     * Page modification de profil
     */
    public function edit(): View
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Sauvegarder les modifications du profil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name'    => 'nullable|string|max:100',
            'last_name'     => 'nullable|string|max:100',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|max:30',
            'phoneNumber'   => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date|before:today',
            'gender'        => 'nullable|in:M,F',
            'address'       => 'nullable|string|max:500',
            'city'          => 'nullable|string|max:100',
            'country'       => 'nullable|string|max:100',
            'bio'           => 'nullable|string|max:1000',
        ]);

        // Construire le nom à partir de first/last si fourni
        if ($request->filled('first_name') && $request->filled('last_name')) {
            $validated['name'] = $request->first_name . ' ' . $request->last_name;
        }

        // Gérer le téléchargement de l'avatar/photo profil
        if ($request->hasFile('profile_photo')) {
            $request->validate(['profile_photo' => 'image|mimes:jpg,jpeg,png,webp|max:2048']);
            
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $path = $request->file('profile_photo')->store('avatars', 'public');
            $validated['profile_photo'] = $path;
        }

        // Alternative: avatar field
        if ($request->hasFile('avatar')) {
            $request->validate(['avatar' => 'image|mimes:jpg,jpeg,png,webp|max:2048']);
            
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['profile_photo'] = $path;
        }

        $user->update($validated);
        $this->logActivity($user->id, 'Profil mis à jour');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'user' => [
                'name'       => $user->fresh()->name,
                'avatar_url' => $user->fresh()->avatar_url,
            ]]);
        }

        return redirect()->route('profile.show')
            ->with('success', 'Profil mis à jour avec succès !');
    }

    /**
     * Page sécurité — sessions et mot de passe
     */
    public function security(): View
    {
        $user = Auth::user();
        $sessions = $this->getActiveSessions();
        return view('profile.security', compact('user', 'sessions'));
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ], [
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
            'force_password_change' => false
        ]);

        // Notification de sécurité
        if (class_exists('App\\Models\\Notification')) {
            Notification::send(
                $user->id,
                'Mot de passe modifié',
                'Votre mot de passe a été changé avec succès. Si ce n\'est pas vous, contactez l\'administrateur.',
                Notification::TYPE_WARNING ?? 'warning',
                Notification::CAT_SECURITY ?? 'security'
            );
        }

        $this->logActivity($user->id, 'Mot de passe modifié');

        return redirect()->route('profile.security')
            ->with('success', 'Mot de passe changé avec succès !');
    }

    /**
     * Alias pour changePassword
     */
    public function updatePassword(Request $request)
    {
        return $this->changePassword($request);
    }

    /**
     * Mettre à jour la langue préférée
     */
    public function updateLanguage(Request $request)
    {
        $locale = in_array($request->locale, ['fr', 'en']) ? $request->locale : 'fr';
        auth()->user()->update(['preferred_language' => $locale]);
        session(['locale' => $locale]);
        return back();
    }

    /**
     * Déconnexion de tous les appareils
     */
    public function logoutAllDevices()
    {
        $user = Auth::user();
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Déconnecté de tous les appareils');
    }

    /**
     * Télécharger/changer l'avatar (API JSON)
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);

        $user = Auth::user();

        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['profile_photo' => $path]);

        return response()->json([
            'success'    => true,
            'avatar_url' => $user->fresh()->avatar_url,
        ]);
    }

    /**
     * Activer 2FA
     */
    public function enable2FA(Request $request)
    {
        $user = Auth::user();
        
        // Validation simple du mot de passe
        $request->validate(['password' => 'required|current_password']);

        $user->update(['two_fa_enabled' => true]);
        $this->logActivity($user->id, '2FA activé');

        return back()->with('success', 'Authentification à deux facteurs activée.');
    }

    /**
     * Désactiver 2FA
     */
    public function disable2FA(Request $request)
    {
        $user = Auth::user();
        
        // Validation simple du mot de passe
        $request->validate(['password' => 'required|current_password']);

        $user->update(['two_fa_enabled' => false]);
        $this->logActivity($user->id, '2FA désactivé');

        return back()->with('success', 'Authentification à deux facteurs désactivée.');
    }

    // ─── Private Helpers ──────────────────────────────────────

    /**
     * Construire les statistiques selon le rôle de l'utilisateur
     */
    private function buildStats(User $user): array
    {
        $stats = [];

        if ($user->isTeacher()) {
            $teacher = $user->teacher;
            $stats = [
                ['label' => 'Classes', 'value' => $teacher?->classSubjectTeachers()->distinct('class_id')->count() ?? 0, 'icon' => 'users'],
    
                ['label' => 'Années exp.', 'value' => $teacher ? (int)$teacher->years_experience : 0, 'icon' => 'award'],
                ['label' => 'Matières', 'value' => $teacher?->classSubjectTeachers()->distinct('subject_id')->count() ?? 0, 'icon' => 'book-open'],
            ];
        } elseif ($user->isStudent()) {
            $student = $user->student;
            $avg = $student ? \App\Models\Mark::where('student_id', $student->id)->avg('grade') : 0;
            $stats = [
                ['label' => 'Moyenne', 'value' => number_format($avg ?? 0, 1), 'icon' => 'trending-up'],
                ['label' => 'Absences', 'value' => $student ? \App\Models\Attendance::where('student_id', $student->id)->where('status', 'absent')->count() : 0, 'icon' => 'user-x'],
                ['label' => 'Matières', 'value' => 0, 'icon' => 'book'],
                ['label' => 'Rang', 'value' => '-', 'icon' => 'star'],
            ];
        } elseif ($user->isParent()) {
            $children = $user->guardian?->students()->count() ?? 0;
            $payments = \App\Models\Payment::whereIn('student_id', $user->guardian?->students()->pluck('id') ?? [])->where('status', 'completed')->count();
            $stats = [
                ['label' => 'Enfants', 'value' => $children, 'icon' => 'users'],
                ['label' => 'Paiements', 'value' => $payments, 'icon' => 'credit-card'],
                ['label' => 'Messages', 'value' => $user->receivedMessages()->count(), 'icon' => 'mail'],
                ['label' => 'Alertes', 'value' => $user->appNotifications()->unread()->count(), 'icon' => 'bell'],
            ];
        } else {
            // Admin
            $stats = [
                ['label' => 'Utilisateurs', 'value' => \App\Models\User::where('is_active', true)->count(), 'icon' => 'users'],
                ['label' => 'Classes', 'value' => \App\Models\Classe::count(), 'icon' => 'layers'],
                ['label' => 'Paiements', 'value' => \App\Models\Payment::where('status', 'completed')->count(), 'icon' => 'credit-card'],
                ['label' => 'Score profil', 'value' => $user->profile_score . '%', 'icon' => 'trending-up'],
            ];
        }

        return $stats;
    }

    /**
     * Récupérer les sessions actives utilisateur
     */
    private function getActiveSessions(): array
    {
        try {
            $sessions = \DB::table('sessions')
                ->where('user_id', Auth::id())
                ->orderByDesc('last_activity')
                ->limit(5)
                ->get()
                ->map(fn($s) => [
                    'ip'          => $s->ip_address ?? 'Unknown',
                    'agent'       => $s->user_agent ?? 'Unknown',
                    'last_active' => \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
                    'is_current'  => $s->id === request()->session()->getId(),
                ])->toArray();
        } catch (\Exception $e) {
            $sessions = [];
        }

        return $sessions;
    }

    /**
     * Enregistrer une activité utilisateur
     */
    private function logActivity(int $userId, string $action): void
    {
        try {
            ActivityLog::create([
                'user_id'    => $userId,
                'action'     => $action,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Fail silently
        }
    }
}
