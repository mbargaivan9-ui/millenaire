<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Services\ChatPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConversationController extends Controller
{
    protected $conversationRepo;
    protected $permissionService;

    public function __construct(
        ConversationRepositoryInterface $conversationRepo,
        ChatPermissionService $permissionService
    ) {
        $this->conversationRepo = $conversationRepo;
        $this->permissionService = $permissionService;
    }

    /**
     * 🔥 Obtenir toutes les conversations de l'utilisateur
     * GET /api/v1/chat/conversations
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $conversations = $this->conversationRepo->getUserConversations(auth()->id());

            return response()->json([
                'success' => true,
                'conversations' => $conversations,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur récupération conversations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des conversations',
            ], 500);
        }
    }

    /**
     * 🔥 Créer une nouvelle conversation privée ou de groupe
     * POST /api/v1/chat/conversations
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:private,group,class',
            'name' => 'nullable|string|max:255',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'integer|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            // Vérifier les permissions pour chaque participant
            $currentUser = auth()->user();
            foreach ($validated['participant_ids'] as $participantId) {
                if ($participantId === $currentUser->id) continue;
                
                $participant = User::find($participantId);
                if (!$this->permissionService->canMessageUser($currentUser, $participant)) {
                    throw ValidationException::withMessages([
                        'participant_ids' => "Vous n'êtes pas autorisé à chatter avec {$participant->name}",
                    ]);
                }
            }

            // Vérifier si conversation privée existe déjà
            if ($validated['type'] === 'private' && count($validated['participant_ids']) === 1) {
                $existingConv = $this->conversationRepo->getPrivateConversation(
                    $currentUser->id,
                    $validated['participant_ids'][0]
                );

                if ($existingConv) {
                    return response()->json([
                        'success' => true,
                        'conversation' => $existingConv,
                        'message' => 'Conversation existante récupérée',
                    ]);
                }
            }

            // Créer la conversation
            $conversation = $this->conversationRepo->create([
                'type' => $validated['type'],
                'name' => $validated['name'] ?? null,
                'created_by' => $currentUser->id,
            ]);

            // Ajouter les participants (incluant le créateur)
            $userIds = array_merge($validated['participant_ids'], [$currentUser->id]);
            $this->conversationRepo->addParticipants($conversation->id, $userIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'conversation' => $conversation->load('participants'),
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur création conversation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la conversation',
            ], 500);
        }
    }

    /**
     * 🔥 Obtenir les détails d'une conversation
     * GET /api/v1/chat/conversations/{conversationId}
     */
    public function show($conversationId): JsonResponse
    {
        try {
            $conversation = $this->conversationRepo->getConversationWithDetails($conversationId);

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation introuvable',
                ], 404);
            }

            // Vérifier que l'utilisateur est participant
            if (!$conversation->participants->contains(auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à accéder à cette conversation',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'conversation' => $conversation,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération conversation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de la conversation',
            ], 500);
        }
    }

    /**
     * 🔥 Ajouter des participants à une conversation
     * POST /api/v1/chat/conversations/{conversationId}/participants
     */
    public function addParticipants(Request $request, $conversationId): JsonResponse
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);

            // Vérifier que l'utilisateur est participant
            if (!$conversation->participants->contains(auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à modifier cette conversation',
                ], 403);
            }

            $validated = $request->validate([
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'integer|exists:users,id',
            ]);

            $this->conversationRepo->addParticipants($conversationId, $validated['user_ids']);

            return response()->json([
                'success' => true,
                'conversation' => $conversation->load('participants'),
                'message' => 'Participants ajoutés avec succès',
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur ajout participants: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout des participants',
            ], 500);
        }
    }

    /**
     * 🔥 Retirer un participant d'une conversation
     * DELETE /api/v1/chat/conversations/{conversationId}/participants/{userId}
     */
    public function removeParticipant($conversationId, $userId): JsonResponse
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);

            // Seul le créateur ou un admin peut retirer
            if ($conversation->created_by !== auth()->id() && !auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à effectuer cette action',
                ], 403);
            }

            $conversation->participants()->detach($userId);

            return response()->json([
                'success' => true,
                'message' => 'Participant retiré avec succès',
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur retrait participant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait du participant',
            ], 500);
        }
    }

    /**
     * 🔥 Archiver une conversation
     * PATCH /api/v1/chat/conversations/{conversationId}/archive
     */
    public function archive($conversationId): JsonResponse
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);

            // Vérifier que l'utilisateur est participant
            if (!$conversation->participants->contains(auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à archiver cette conversation',
                ], 403);
            }

            DB::table('conversation_participants')
                ->where('conversation_id', $conversationId)
                ->where('user_id', auth()->id())
                ->update(['is_archived' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Conversation archivée',
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur archivage conversation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'archivage',
            ], 500);
        }
    }
}
