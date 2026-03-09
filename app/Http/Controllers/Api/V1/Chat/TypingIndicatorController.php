<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\TypingIndicator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypingIndicatorController extends Controller
{
    /**
     * 🔥 Marquer comme en train d'écrire
     * POST /api/v1/chat/conversations/{conversationId}/typing
     */
    public function markTyping($conversationId): JsonResponse
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);

            // Vérifier que l'utilisateur est participant
            if (!$conversation->participants->contains(auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé',
                ], 403);
            }

            // Créer ou mettre à jour l'indicateur
            TypingIndicator::updateOrCreate(
                [
                    'conversation_id' => $conversationId,
                    'user_id' => auth()->id(),
                ],
                [
                    'last_typed_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur marquage typing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur',
            ], 500);
        }
    }

    /**
     * 🔥 Obtenir les utilisateurs en train d'écrire
     * GET /api/v1/chat/conversations/{conversationId}/typing
     */
    public function getTypingUsers($conversationId): JsonResponse
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);

            // Vérifier que l'utilisateur est participant
            if (!$conversation->participants->contains(auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé',
                ], 403);
            }

            // Obtenir les utilisateurs en train d'écrire (dans les 3 dernières secondes)
            $typingUsers = TypingIndicator::query()
                ->where('conversation_id', $conversationId)
                ->where('user_id', '!=', auth()->id())
                ->where('last_typed_at', '>', now()->subSeconds(3))
                ->with('user:id,name,profile_photo')
                ->get();

            return response()->json([
                'success' => true,
                'typing_users' => $typingUsers->map(function ($indicator) {
                    return $indicator->user;
                }),
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération typing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur',
            ], 500);
        }
    }
}
