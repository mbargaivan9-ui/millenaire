<?php

namespace App\Http\Middleware;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatPermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckChatPermission
{
    protected $permissionService;

    public function __construct(ChatPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Routes concernées: /api/v1/chat/conversations/{conversationId}/messages
        if ($request->routeIs('api.chat.messages.*')) {
            $conversationId = $request->route('conversationId');
            $conversation = Conversation::find($conversationId);

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation introuvable',
                ], 404);
            }

            // Vérifier que l'utilisateur est participant
            if (!$conversation->participants->contains(Auth::id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à accéder à cette conversation',
                ], 403);
            }
        }

        return $next($request);
    }
}
