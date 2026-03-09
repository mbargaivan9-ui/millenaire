<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Page profil principal (view like FlexAdmin)
     */
    public function show()
    {
        $user = Auth::user()->load(['student', 'teacher', 'guardian']);

        // Stats selon rôle
        $stats = $this->buildStats($user);

        // Activités récentes (via notifications)
        $activities = Notification::forUser($user->id)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('profile.show', compact('user', 'stats', 'activities'));
    }

    /**
     * Page modification de profil
     */
    public function edit()
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
            'phoneNumber'   => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date|before:today',
            'gender'        => 'nullable|in:M,F',
            'address'       => 'nullable|string|max:500',
            'city'          => 'nullable|string|max:100',
            'country'       => 'nullable|string|max:100',
            'bio'           => 'nullable|string|max:1000',
        ]);

        // Build name from first/last if provided
        if ($request->filled('first_name') && $request->filled('last_name')) {
            $validated['name'] = $request->first_name . ' ' . $request->last_name;
        }

        // Handle avatar upload
        if ($request->hasFile('profile_photo')) {
            $request->validate(['profile_photo' => 'image|mimes:jpg,jpeg,png,webp|max:2048']);

            // Delete old
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $path = $request->file('profile_photo')->store('avatars', 'public');
            $validated['profile_photo'] = $path;
        }

        $user->update($validated);

        // Log activity
        $this->logActivity($user->id, 'Profil mis à jour');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'user' => [
                'name'       => $user->fresh()->display_name,
                'avatar_url' => $user->fresh()->avatar_url,
            ]]);
        }

        return redirect()->route('profile.show')
            ->with('success', 'Profil mis à jour avec succès !');
    }

    /**
     * Sécurité — page
     */
    public function security()
    {
        $user     = Auth::user();
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
        $user->update(['password' => Hash::make($request->password), 'force_password_change' => false]);

        // Notification de sécurité
        Notification::send(
            $user->id,
            'Mot de passe modifié',
            'Votre mot de passe a été changé avec succès. Si ce n\'est pas vous, contactez l\'administrateur.',
            Notification::TYPE_WARNING,
            Notification::CAT_SECURITY
        );

        $this->logActivity($user->id, 'Mot de passe modifié');

        return redirect()->route('profile.security')
            ->with('success', 'Mot de passe changé avec succès !');
    }

    /**
     * Update password (alias)
     */
    public function updatePassword(Request $request)
    {
        return $this->changePassword($request);
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
     * API: Upload avatar seulement
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

    // ─── Private helpers ────────────────────────────────────

    private function buildStats(User $user): array
    {
        $stats = [];

        if ($user->isTeacher()) {
            $teacher = $user->teacher;
            $stats = [
                ['label' => 'Classes', 'value' => $teacher?->classSubjectTeachers()->distinct('class_id')->count() ?? 0, 'icon' => 'users'],
                ['label' => 'Notes saisies', 'value' => $teacher ? \App\Models\BulletinEntry::whereHas('classSubjectTeacher', fn($q) => $q->where('teacher_id', $teacher->id))->count() : 0, 'icon' => 'check-square'],
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
            // Count payments from the guardian's children
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

    private function getActiveSessions(): array
    {
        // Sessions from sessions table
        try {
            $sessions = \DB::table('sessions')
                ->where('user_id', Auth::id())
                ->orderByDesc('last_activity')
                ->limit(5)
                ->get()
                ->map(fn($s) => [
                    'ip'         => $s->ip_address,
                    'agent'      => $s->user_agent,
                    'last_active'=> \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
                    'is_current' => $s->id === request()->session()->getId(),
                ])->toArray();
        } catch (\Exception $e) {
            $sessions = [];
        }

        return $sessions;
    }

    private function logActivity(int $userId, string $action): void
    {
        try {
            \App\Models\ActivityLog::create([
                'user_id'    => $userId,
                'action'     => $action,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silent fail
        }
    }
}
