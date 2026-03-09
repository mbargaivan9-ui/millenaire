<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Services\ChatPermissionService;
use App\Services\MessageService;
use App\Services\ContentModerationService;
use App\Events\MessageSent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    protected $messageRepo;
    protected $permissionService;
    protected $messageService;
    protected $moderationService;

    public function __construct(
        MessageRepositoryInterface $messageRepo,
        ChatPermissionService $permissionService,
        MessageService $messageService,
        ContentModerationService $moderationService
    ) {
        $this->messageRepo = $messageRepo;
        $this->permissionService = $permissionService;
        $this->messageService = $messageService;
        $this->moderationService = $moderationService;
    }

    /**
     * 🔥 Récupérer les messages d'une conversation
     * GET /api/v1/chat/conversations/{conversationId}/messages
     */
    public function index($conversationId): JsonResponse
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);

            // Vérifier que l'utilisateur est participant
            if (!$conversation->participants->contains(auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à voir cette conversation',
                ], 403);
            }

            $messages = $this->messageRepo->getMessagesForConversation($conversationId);

            // Marquer les messages comme lus
            $this->messageService->markAsRead($conversationId, auth()->id());

            return response()->json([
                'success' => true,
                'messages' => $messages,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des messages',
            ], 500);
        }
    }

    /**
     * 🔥 Envoyer un message
     * POST /api/v1/chat/conversations/{conversationId}/messages
     */
    public function store(Request $request, $conversationId): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required_without:attachments|string|max:5000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240', // 10MB max
            'type' => 'in:text,image,file,audio,video',
        ]);

        try {
            $conversation = Conversation::findOrFail($conversationId);
            $user = auth()->user();

            // Vérifier permissions
            if (!$conversation->participants->contains($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à envoyer des messages dans cette conversation',
                ], 403);
            }

            // Vérifier modération pour les élèves
            if ($this->moderationService->shouldModerateUser($user)) {
                if ($this->moderationService->shouldBlockMessage($validated['content'] ?? '')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Votre message contient du contenu inapproprié',
                    ], 422);
                }
            }

            DB::beginTransaction();

            // Créer le message
            $message = $this->messageRepo->create([
                'conversation_id' => $conversationId,
                'sender_id' => $user->id,
                'content' => $validated['content'] ?? null,
                'type' => $validated['type'] ?? 'text',
            ]);

            // Upload des fichiers joints (si présents)
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->messageService->attachFile($message, $file);
                }
            }

            // Mettre à jour la conversation
            $conversation->update([
                'last_message_at' => now(),
            ]);

            // Incrémenter le compteur non-lu pour les autres participants
            $this->messageService->incrementUnreadCount($conversation, $user->id);

            DB::commit();

            // Charger les relations
            $message->load(['sender', 'attachments', 'reactions.user']);

            // 🔥 Broadcast en temps réel via WebSocket
            broadcast(new MessageSent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => $message,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur envoi message: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi du message',
            ], 500);
        }
    }

    /**
     * 🔥 Modifier un message
     * PUT /api/v1/chat/messages/{messageId}
     */
    public function update(Request $request, $messageId): JsonResponse
    {
        try {
            $message = Message::findOrFail($messageId);

            // Vérifier que l'utilisateur est l'expéditeur
            if ($message->sender_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez modifier que vos propres messages',
                ], 403);
            }

            $validated = $request->validate([
                'content' => 'required|string|max:5000',
            ]);

            $message->update([
                'content' => $validated['content'],
                'is_edited' => true,
                'edited_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur modification message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification',
            ], 500);
        }
    }

    /**
     * 🔥 Supprimer un message
     * DELETE /api/v1/chat/messages/{messageId}
     */
    public function destroy($messageId): JsonResponse
    {
        try {
            $message = Message::findOrFail($messageId);

            // Vérifier permissions
            if ($message->sender_id !== auth()->id() && !auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez supprimer que vos propres messages',
                ], 403);
            }

            $this->messageRepo->delete($messageId);

            return response()->json([
                'success' => true,
                'message' => 'Message supprimé avec succès',
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur suppression message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
            ], 500);
        }
    }

    /**
     * 🔥 Ajouter une réaction (emoji)
     * POST /api/v1/chat/messages/{messageId}/reactions
     */
    public function addReaction(Request $request, $messageId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'emoji' => 'required|string|max:10',
            ]);

            $message = Message::findOrFail($messageId);

            // Ajouter la réaction
            $this->messageRepo->addReaction($messageId, auth()->id(), $validated['emoji']);

            return response()->json([
                'success' => true,
                'message' => 'Réaction ajoutée',
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur ajout réaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la réaction',
            ], 500);
        }
    }

    /**
     * 🔥 Retirer une réaction
     * DELETE /api/v1/chat/messages/{messageId}/reactions/{emoji}
     */
    public function removeReaction($messageId, $emoji): JsonResponse
    {
        try {
            $this->messageRepo->removeReaction($messageId, auth()->id(), $emoji);

            return response()->json([
                'success' => true,
                'message' => 'Réaction supprimée',
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur retrait réaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait de la réaction',
            ], 500);
        }
    }
}
