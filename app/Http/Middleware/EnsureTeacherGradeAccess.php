<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BulletinNgSession;
use App\Models\BulletinNgSubject;
use App\Services\BulletinVisibilityService;

/**
 * EnsureTeacherGradeAccess — Middleware pour vérifier accès enseignant
 * 
 * Vérifie que l'enseignant:
 * 1. A une matière affiliée à la session
 * 2. Session est visible aux enseignants
 * 3. Session est saisie_ouverte
 * 4. Notes ne sont pas verrouillées
 */
class EnsureTeacherGradeAccess
{
    public function __construct(private BulletinVisibilityService $visibilityService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Admin a accès total
        if ($user->is_admin) {
            return $next($request);
        }

        $sessionId = $request->route('session_id') ?? $request->route('sessionId');

        if (! $sessionId) {
            return response()->json(['error' => 'Session ID manquant'], 400);
        }

        // Vérifier l'accès du prof
        $canView = $this->visibilityService->canTeacherViewSession($user->id, $sessionId);

        if (! $canView) {
            return response()->json(['error' => 'Accès refusé: vous n\'avez pas accès à cette session'], 403);
        }

        // Vérifier que session est ouverte
        $session = BulletinNgSession::find($sessionId);
        if (! $session || ! $session->isEntryOpen()) {
            return response()->json(['error' => 'La saisie pour cette session n\'est pas ouverte'], 403);
        }

        return $next($request);
    }
}
