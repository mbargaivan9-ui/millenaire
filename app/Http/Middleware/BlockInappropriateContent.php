<?php

namespace App\Http\Middleware;

use App\Services\ContentModerationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class BlockInappropriateContent
{
    protected $moderationService;

    public function __construct(ContentModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Appliquer la modération uniquement pour les messages envoyés
        if ($request->isMethod('post') && $request->routeIs('api.chat.messages.store')) {
            $user = Auth::user();

            // Vérifier si c'est un élève
            if ($this->moderationService->shouldModerateUser($user)) {
                $content = $request->input('content', '');

                // Vérifier le contenu
                if (!$this->moderationService->isContentAppropriate($content)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Votre message contient du contenu inapproprié et ne peut pas être envoyé',
                    ], 422);
                }
            }
        }

        return $next($request);
    }
}
