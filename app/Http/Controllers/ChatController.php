<?php

/**
 * ChatController — Messagerie Interne Temps Réel
 *
 * Phase 8 — Système de Chat
 * Conversations privées + groupes + messagerie rôles
 * Broadcasting via Laravel Reverb (WebSockets)
 *
 * @package App\Http\Controllers
 */

namespace App\Http\Controllers;

use App\Events\ChatEvents;
use App\Models\User;
use App\Services\ChatPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatPermissionService $permissions
    ) {}

    /**
     * Page principale de messagerie.
     */
    public function index()
    {
        $user          = auth()->user();
        $conversations = $this->getUserConversations($user);
        $contacts      = $this->permissions->getAllowedContacts($user);

        return view('chat.index', compact('conversations', 'contacts'));
    }

    /**
     * Ouvrir une conversation spécifique.
     */
    public function show(int $conversationId)
    {
        $user          = auth()->user();
        $conversations = $this->getUserConversations($user);
        $contacts      = $this->permissions->getAllowedContacts($user);

        // Find or fail conversation that belongs to user
        $activeConversation = \App\Models\Conversation::whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->with(['participants.user', 'messages' => fn($q) => $q->latest()->take(50)])
            ->findOrFail($conversationId);

        // Mark messages as read
        $activeConversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('chat.index', compact('conversations', 'contacts', 'activeConversation'));
    }

    /**
     * Créer ou trouver une conversation privée.
     */
    public function createConversation(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
        ]);

        $user      = auth()->user();
        $recipient = User::findOrFail($request->recipient_id);

        // Check permissions
        if (!$this->permissions->canMessageUser($user, $recipient)) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'fr'
                    ? 'Vous n\'êtes pas autorisé à contacter cet utilisateur.'
                    : 'You are not allowed to contact this user.',
            ], 403);
        }

        // Find or create private conversation
        $conversation = \App\Models\Conversation::whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->whereHas('participants', fn($q) => $q->where('user_id', $recipient->id))
            ->where('type', 'private')
            ->first();

        if (!$conversation) {
            $conversation = \App\Models\Conversation::create([
                'type'       => 'private',
                'created_by' => $user->id,
            ]);
            $conversation->participants()->createMany([
                ['user_id' => $user->id],
                ['user_id' => $recipient->id],
            ]);
        }

        return response()->json([
            'success'         => true,
            'conversation_id' => $conversation->id,
            'redirect'        => route('chat.show', $conversation->id),
        ]);
    }

    /**
     * Envoyer un message (AJAX).
     */
    public function sendMessage(Request $request, int $conversationId): JsonResponse
    {
        $request->validate([
            'content'      => 'required_without:file|nullable|string|max:2000',
            'file'         => 'nullable|file|max:10240',
            'reply_to_id'  => 'nullable|exists:messages,id',
        ]);

        $user         = auth()->user();
        $conversation = \App\Models\Conversation::whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->findOrFail($conversationId);

        $filePath = null;
        $fileType = null;
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $filePath = $request->file('file')->store("chat/{$conversationId}", 'private');
            $fileType = $request->file('file')->getMimeType();
        }

        $message = $conversation->messages()->create([
            'sender_id'   => $user->id,
            'content'     => $request->content,
            'file_path'   => $filePath,
            'file_type'   => $fileType,
            'reply_to_id' => $request->reply_to_id,
        ]);

        $message->load('sender');

        // Update conversation last_message_at
        $conversation->touch();

        // Broadcast to other participants
        broadcast(new ChatEvents\MessageSent($message, $conversation))->toOthers();

        return response()->json([
            'success' => true,
            'message' => [
                'id'         => $message->id,
                'content'    => $message->content,
                'sender'     => $message->sender->display_name ?? $message->sender->name,
                'created_at' => $message->created_at->toISOString(),
                'file_path'  => $filePath ? asset('storage/' . $filePath) : null,
            ],
        ]);
    }

    /**
     * Modifier un message.
     */
    public function editMessage(Request $request, int $messageId): JsonResponse
    {
        $request->validate(['content' => 'required|string|max:2000']);

        $message = \App\Models\Message::where('sender_id', auth()->id())->findOrFail($messageId);
        $message->update(['content' => $request->content, 'is_edited' => true]);

        broadcast(new ChatEvents\MessageEdited($message))->toOthers();

        return response()->json(['success' => true]);
    }

    /**
     * Supprimer un message.
     */
    public function deleteMessage(int $messageId): JsonResponse
    {
        $message = \App\Models\Message::where('sender_id', auth()->id())->findOrFail($messageId);
        $message->update(['content' => null, 'is_deleted' => true]);

        broadcast(new ChatEvents\MessageDeleted($message))->toOthers();

        return response()->json(['success' => true]);
    }

    /**
     * Marquer les messages d'une conversation comme lus.
     */
    public function markRead(int $conversationId): JsonResponse
    {
        \App\Models\Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    private function getUserConversations(User $user): \Illuminate\Support\Collection
    {
        return \App\Models\Conversation::whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->with([
                'participants.user',
                'messages' => fn($q) => $q->latest()->limit(1),
            ])
            ->withCount(['messages as unread_count' => fn($q) => $q->where('sender_id', '!=', $user->id)->whereNull('read_at')])
            ->orderByDesc('updated_at')
            ->get();
    }
}
