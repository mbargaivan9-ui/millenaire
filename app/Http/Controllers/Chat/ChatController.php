<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageReaction;
use App\Models\User;
use App\Services\ChatPermissionService;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * ChatController
 *
 * Gère l'interface de chat complète pour les 4 rôles :
 * - admin / censeur / intendant
 * - professeur / prof_principal
 * - parent
 * - student
 */
class ChatController extends Controller
{
    public function __construct(
        private readonly ChatPermissionService $permissions,
        private readonly MessageService $messageService
    ) {}

    // ════════════════════════════════════════════════
    //  PAGE PRINCIPALE DU CHAT
    // ════════════════════════════════════════════════

    /**
     * Affiche la page principale du chat avec la liste des conversations.
     * Ouvre optionnellement une conversation directement.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $user = Auth::user();

        // Charger les conversations de l'utilisateur triées par dernier message
        $conversations = $this->getConversationsForUser($user->id);

        // Conversation active (depuis URL ?conversation=X)
        $activeConversation = null;
        $messages = collect();

        if ($request->has('conversation')) {
            $activeConversation = Conversation::whereHas('participants', fn($q) =>
                $q->where('user_id', $user->id)
            )->find($request->conversation);

            if ($activeConversation) {
                $messages = $this->getMessages($activeConversation, $user->id);
            }
        } elseif ($conversations->isNotEmpty()) {
            // Ouvrir la première conversation par défaut
            $activeConversation = $conversations->first();
            $messages = $this->getMessages($activeConversation, $user->id);
        }

        // Utilisateurs disponibles pour démarrer une nouvelle conversation
        $availableUsers = $this->getAvailableUsersForChat($user);

        // Total messages non lus
        $totalUnread = $this->getTotalUnread($user->id);

        return view('chat.index', compact(
            'conversations',
            'activeConversation',
            'messages',
            'availableUsers',
            'totalUnread'
        ));
    }

    // ════════════════════════════════════════════════
    //  API : CHARGER UNE CONVERSATION (AJAX)
    // ════════════════════════════════════════════════

    /**
     * Charge les messages d'une conversation via AJAX.
     */
    public function loadConversation(Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est participant
        if (! $conversation->participants->contains($user->id)) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        $messages = $this->getMessages($conversation, $user->id);

        // Marquer comme lu
        $this->messageService->markAsRead($conversation->id, $user->id);

        return response()->json([
            'conversation' => $this->formatConversation($conversation, $user->id),
            'messages'     => $messages->map(fn($m) => $this->formatMessage($m, $user->id)),
        ]);
    }

    // ════════════════════════════════════════════════
    //  API : LISTE DES CONVERSATIONS (AJAX)
    // ════════════════════════════════════════════════

    public function listConversations(Request $request): JsonResponse
    {
        $user = Auth::user();
        $filter = $request->input('filter', 'all'); // all | unread | groups

        $conversations = $this->getConversationsForUser($user->id, $filter);

        return response()->json([
            'conversations' => $conversations->map(fn($c) => $this->formatConversation($c, $user->id)),
            'total_unread'  => $this->getTotalUnread($user->id),
        ]);
    }

    // ════════════════════════════════════════════════
    //  API : ENVOYER UN MESSAGE
    // ════════════════════════════════════════════════

    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|integer|exists:conversations,id',
            'content'         => 'nullable|string|max:5000',
            'type'            => 'in:text,image,file,audio',
            'attachment'      => 'nullable|file|max:20480', // 20MB
        ]);

        if (empty($validated['content']) && ! $request->hasFile('attachment')) {
            return response()->json(['error' => 'Le message ne peut pas être vide.'], 422);
        }

        $user = Auth::user();
        $conversation = Conversation::find($validated['conversation_id']);

        // Vérifier participation
        if (! $conversation->participants->contains($user->id)) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        try {
            DB::beginTransaction();

            $type = 'text';
            if ($request->hasFile('attachment')) {
                $mime = $request->file('attachment')->getMimeType();
                if (str_starts_with($mime, 'image/')) $type = 'image';
                elseif (str_starts_with($mime, 'audio/')) $type = 'audio';
                else $type = 'file';
            }

            // Créer le message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $user->id,
                'content'         => $validated['content'] ?? null,
                'type'            => $type,
            ]);

            // Pièce jointe
            if ($request->hasFile('attachment')) {
                $this->messageService->attachFile($message, $request->file('attachment'));
            }

            // Mettre à jour last_message_at et compteurs non-lus
            $conversation->update(['last_message_at' => now()]);
            $this->messageService->incrementUnreadCount($conversation, $user->id);

            DB::commit();

            // Charger les relations pour la réponse
            $message->load(['sender', 'attachments', 'reactions.user']);

            // Broadcaster (si Reverb/Pusher configuré)
            // broadcast(new \App\Events\MessageSent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => $this->formatMessage($message, $user->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Chat sendMessage error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'envoi.'], 500);
        }
    }

    // ════════════════════════════════════════════════
    //  API : CRÉER UNE CONVERSATION
    // ════════════════════════════════════════════════

    public function createConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'         => 'required|in:private,group,class',
            'name'         => 'nullable|string|max:100',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        $user = Auth::user();

        // Pour une conversation privée, vérifier qu'une conv n'existe pas déjà
        if ($validated['type'] === 'private' && count($validated['participants']) === 1) {
            $otherId = $validated['participants'][0];

            // Vérifier permissions
            $other = User::find($otherId);
            if (! $this->permissions->canMessageUser($user, $other)) {
                return response()->json(['error' => 'Vous ne pouvez pas contacter cet utilisateur.'], 403);
            }

            $existing = $this->findExistingPrivateConversation($user->id, $otherId);
            if ($existing) {
                return response()->json([
                    'success'         => true,
                    'conversation_id' => $existing->id,
                    'existing'        => true,
                ]);
            }
        }

        try {
            DB::beginTransaction();

            $conversation = Conversation::create([
                'type'       => $validated['type'],
                'name'       => $validated['name'],
                'created_by' => $user->id,
                'last_message_at' => now(),
            ]);

            $participants = array_unique(array_merge([$user->id], $validated['participants']));
            $conversation->participants()->attach($participants);

            DB::commit();

            $conversation->load(['participants', 'lastMessage']);

            return response()->json([
                'success'         => true,
                'conversation_id' => $conversation->id,
                'conversation'    => $this->formatConversation($conversation, $user->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la création.'], 500);
        }
    }

    // ════════════════════════════════════════════════
    //  API : MARQUER COMME LU
    // ════════════════════════════════════════════════

    public function markAsRead(Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if (! $conversation->participants->contains($user->id)) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        $this->messageService->markAsRead($conversation->id, $user->id);

        return response()->json(['success' => true, 'total_unread' => $this->getTotalUnread($user->id)]);
    }

    // ════════════════════════════════════════════════
    //  API : CHERCHER DES UTILISATEURS  
    // ════════════════════════════════════════════════

    /**
     * Recherche les utilisateurs disponibles pour une nouvelle conversation
     * avec permissions basées sur le rôle et la classe
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $user = Auth::user();
        $search = $request->get('q', '');

        // ========================================
        // LOGIQUE DE PERMISSIONS RÉVISÉE
        // ========================================
        
        $allowedRoles = $this->getAllowedRoles($user->role);
        
        $query = User::where('id', '!=', $user->id)
            ->where('is_active', true);

        // Appliquer les filtres de permissions basés sur le rôle
        if (!empty($allowedRoles)) {
            $query = $query->where(function ($q) use ($allowedRoles, $user) {
                // 1. Voir les rôles autorisés
                $q->whereIn('role', $allowedRoles);

                // 2. Pour les enseignants : voir aussi les étudiants de leur classe
                if (in_array($user->role, ['professeur', 'prof_principal']) && $user->class_id) {
                    $q->orWhere(function($subQ) use ($user) {
                        $subQ->where('role', 'student')
                             ->where('class_id', $user->class_id);
                    });
                }

                // 3. Pour les étudiants : voir les autres étudiants de leur classe
                if ($user->role === 'student' && $user->class_id) {
                    $q->orWhere(function($subQ) use ($user) {
                        $subQ->where('role', 'student')
                             ->where('class_id', $user->class_id);
                    });
                }

                // 4. Pour les parents : voir les enseignants de la classe de leur enfant
                if ($user->role === 'parent') {
                    $q->orWhereHas('students', function($sq) use ($user) {
                        $sq->where('class_id', $user->child_class_id ?? null);
                    });
                }
            });
        }

        // Recherche texte (insensible à la casse)
        if ($search && strlen($search) >= 1) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->select(['id', 'name', 'email', 'role', 'profile_photo', 'last_login', 'class_id'])
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'role_label' => $this->getRoleLabel($u->role),
                'profile_photo' => $u->profile_photo ? asset('storage/' . $u->profile_photo) : $this->getDefaultAvatar($u->name),
                'is_online' => $this->isOnline($u),
            ]),
        ]);
    }

    // ════════════════════════════════════════════════
    //  API : SUPPRIMER UN MESSAGE
    // ════════════════════════════════════════════════

    public function deleteMessage(Message $message): JsonResponse
    {
        $user = Auth::user();

        if ($message->sender_id !== $user->id && ! $user->isAdmin()) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        $message->update(['is_deleted' => true, 'content' => null]);

        return response()->json(['success' => true, 'message_id' => $message->id]);
    }

    // ════════════════════════════════════════════════
    //  API : RÉAGIR À UN MESSAGE (EMOJI)
    // ════════════════════════════════════════════════

    public function react(Request $request, Message $message): JsonResponse
    {
        $validated = $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $user = Auth::user();

        // Vérifier participation
        if (! $message->conversation->participants->contains($user->id)) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        // Toggle la réaction
        $existing = MessageReaction::where([
            'message_id' => $message->id,
            'user_id'    => $user->id,
            'emoji'      => $validated['emoji'],
        ])->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            MessageReaction::create([
                'message_id' => $message->id,
                'user_id'    => $user->id,
                'emoji'      => $validated['emoji'],
            ]);
            $action = 'added';
        }

        // Retourner les réactions mises à jour
        $reactions = MessageReaction::where('message_id', $message->id)
            ->select('emoji', DB::raw('count(*) as count'))
            ->groupBy('emoji')
            ->get();

        return response()->json([
            'success'    => true,
            'action'     => $action,
            'message_id' => $message->id,
            'reactions'  => $reactions,
        ]);
    }

    // ════════════════════════════════════════════════
    //  API : INDICATEUR DE FRAPPE (TYPING)
    // ════════════════════════════════════════════════

    public function typing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|integer|exists:conversations,id',
            'is_typing'       => 'required|boolean',
        ]);

        // broadcast(new \App\Events\UserTyping(
        //     Auth::id(),
        //     $validated['conversation_id'],
        //     $validated['is_typing']
        // ))->toOthers();

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════
    //  API : RECHERCHE DE CONVERSATIONS
    // ════════════════════════════════════════════════

    public function searchConversations(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $user  = Auth::user();

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = Conversation::whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhereHas('participants', fn($q2) =>
                        $q2->where('name', 'like', "%{$query}%")
                    )
                    ->orWhereHas('messages', fn($q3) =>
                        $q3->where('content', 'like', "%{$query}%")
                    );
            })
            ->with(['participants', 'lastMessage'])
            ->take(10)
            ->get();

        return response()->json([
            'results' => $results->map(fn($c) => $this->formatConversation($c, $user->id)),
        ]);
    }

    // ════════════════════════════════════════════════
    //  API : TÉLÉCHARGER UN FICHIER
    // ════════════════════════════════════════════════

    public function downloadAttachment(MessageAttachment $attachment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est dans la conversation
        if (! $attachment->message->conversation->participants->contains($user->id)) {
            abort(403);
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }

    // ════════════════════════════════════════════════
    //  API : POLLING — Nouveaux messages (fallback sans WebSocket)
    // ════════════════════════════════════════════════

    public function pollMessages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|integer|exists:conversations,id',
            'last_message_id' => 'required|integer',
        ]);

        $user = Auth::user();

        $newMessages = Message::where('conversation_id', $validated['conversation_id'])
            ->where('id', '>', $validated['last_message_id'])
            ->where('is_deleted', false)
            ->with(['sender', 'attachments', 'reactions.user'])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'messages'    => $newMessages->map(fn($m) => $this->formatMessage($m, $user->id)),
            'total_unread' => $this->getTotalUnread($user->id),
        ]);
    }

    // ════════════════════════════════════════════════
    //  API : UTILISATEURS DISPONIBLES POUR CHAT
    // ════════════════════════════════════════════════

    public function availableUsers(Request $request): JsonResponse
    {
        $user = Auth::user();
        $search = $request->input('q', '');

        $allowedRoles = $this->getAllowedRoles($user->role);
        
        $query = User::where('id', '!=', $user->id)
            ->where('is_active', true);

        // Appliquer les filtres de permissions basés sur le rôle
        if (!empty($allowedRoles)) {
            $query = $query->where(function ($q) use ($allowedRoles, $user) {
                // 1. Voir les rôles autorisés
                $q->whereIn('role', $allowedRoles);

                // 2. Pour les enseignants : voir aussi les étudiants de leur classe
                if (in_array($user->role, ['professeur', 'prof_principal']) && $user->class_id) {
                    $q->orWhere(function($subQ) use ($user) {
                        $subQ->where('role', 'student')
                             ->where('class_id', $user->class_id);
                    });
                }

                // 3. Pour les étudiants : voir les autres étudiants de leur classe
                if ($user->role === 'student' && $user->class_id) {
                    $q->orWhere(function($subQ) use ($user) {
                        $subQ->where('role', 'student')
                             ->where('class_id', $user->class_id);
                    });
                }

                // 4. Pour les parents : voir les enseignants potentiels
                if ($user->role === 'parent') {
                    $q->orWhereIn('role', ['professeur', 'prof_principal', 'admin']);
                }
            });
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json([
            'users' => $query->select(['id', 'name', 'role', 'profile_photo', 'last_login'])
                ->orderBy('name')
                ->limit(100)
                ->get()
                ->map(fn($u) => [
                    'id'            => $u->id,
                    'name'          => $u->name,
                    'role'          => $u->role,
                    'role_label'    => $this->getRoleLabel($u->role),
                    'profile_photo' => $u->profile_photo ? asset('storage/' . $u->profile_photo) : $this->getDefaultAvatar($u->name),
                    'is_online'     => $this->isOnline($u),
                ]),
        ]);
    }

    // ════════════════════════════════════════════════
    //  API : NOMBRE DE MESSAGES NON LUS (badge topbar)
    // ════════════════════════════════════════════════

    public function unreadCount(): JsonResponse
    {
        return response()->json(['count' => $this->getTotalUnread(Auth::id())]);
    }

    // ════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ════════════════════════════════════════════════

    private function getConversationsForUser(int $userId, string $filter = 'all')
    {
        $query = Conversation::whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->with(['participants', 'lastMessage.sender', 'lastMessage.attachments'])
            ->withCount(['messages as unread_count' => function ($q) use ($userId) {
                $q->where('is_deleted', false)
                    ->whereColumn('created_at', '>',
                        DB::raw("COALESCE((SELECT last_read_at FROM conversation_participants WHERE conversation_id = conversations.id AND user_id = {$userId}), '1970-01-01')")
                    )
                    ->where('sender_id', '!=', $userId);
            }])
            ->orderByDesc('last_message_at');

        if ($filter === 'unread') {
            $query->having('unread_count', '>', 0);
        } elseif ($filter === 'groups') {
            $query->whereIn('type', ['group', 'class']);
        }

        return $query->get();
    }

    private function getMessages(Conversation $conversation, int $userId)
    {
        // Marquer comme lu
        $this->messageService->markAsRead($conversation->id, $userId);

        return Message::where('conversation_id', $conversation->id)
            ->where('is_deleted', false)
            ->with(['sender', 'attachments', 'reactions.user'])
            ->orderBy('created_at')
            ->take(50)
            ->get();
    }

    private function formatConversation(Conversation $conversation, int $userId): array
    {
        $otherParticipant = null;
        $isPrivate = $conversation->type === 'private';

        if ($isPrivate) {
            $otherParticipant = $conversation->participants
                ->firstWhere('id', '!=', $userId);
        }

        $displayName = $isPrivate
            ? ($otherParticipant?->name ?? 'Conversation')
            : ($conversation->name ?? 'Groupe');

        $lastMsg = $conversation->lastMessage;
        $lastMsgText = null;
        if ($lastMsg) {
            if ($lastMsg->is_deleted) {
                $lastMsgText = 'Message supprimé';
            } elseif ($lastMsg->type !== 'text' && $lastMsg->attachments->isNotEmpty()) {
                $lastMsgText = '📎 ' . $lastMsg->attachments->first()->file_name;
            } else {
                $lastMsgText = \Illuminate\Support\Str::limit($lastMsg->content, 50);
            }
        }

        $unreadCount = $conversation->participants
            ->firstWhere('id', $userId)?->pivot?->unread_count ?? 0;

        return [
            'id'               => $conversation->id,
            'type'             => $conversation->type,
            'name'             => $displayName,
            'avatar'           => $isPrivate && $otherParticipant
                ? ($otherParticipant->profile_photo
                    ? asset('storage/' . $otherParticipant->profile_photo)
                    : $this->getDefaultAvatar($otherParticipant->name))
                : null,
            'is_online'        => $isPrivate && $otherParticipant ? $this->isOnline($otherParticipant) : false,
            'other_user_id'    => $isPrivate ? $otherParticipant?->id : null,
            'other_user_role'  => $isPrivate ? $otherParticipant?->role : null,
            'last_message'     => $lastMsgText,
            'last_message_at'  => $conversation->last_message_at?->diffForHumans() ?? '',
            'last_message_raw' => $conversation->last_message_at?->toIso8601String(),
            'unread_count'     => max(0, (int) ($conversation->unread_count ?? $unreadCount)),
            'participants_count' => $conversation->participants->count(),
        ];
    }

    private function formatMessage(Message $message, int $currentUserId): array
    {
        $isMine = $message->sender_id === $currentUserId;

        $attachments = $message->attachments->map(fn($a) => [
            'id'        => $a->id,
            'file_name' => $a->file_name,
            'file_type' => $a->file_type,
            'file_size' => $this->formatFileSize($a->file_size),
            'mime_type' => $a->mime_type,
            'url'       => asset('storage/' . $a->file_path),
            'download_url' => route('chat.attachment.download', $a->id),
        ])->toArray();

        $reactions = $message->reactions
            ->groupBy('emoji')
            ->map(fn($r, $emoji) => [
                'emoji'     => $emoji,
                'count'     => $r->count(),
                'mine'      => $r->contains('user_id', $currentUserId),
                'users'     => $r->pluck('user.name')->take(3)->join(', '),
            ])
            ->values()
            ->toArray();

        // Statut "vu" pour les messages envoyés
        $isSeen = false;
        if ($isMine) {
            $conv = $message->conversation;
            if ($conv) {
                $isSeen = $conv->participants()
                    ->where('user_id', '!=', $currentUserId)
                    ->where('last_read_at', '>=', $message->created_at)
                    ->exists();
            }
        }

        return [
            'id'            => $message->id,
            'sender_id'     => $message->sender_id,
            'sender_name'   => $message->sender?->name ?? 'Inconnu',
            'sender_avatar' => $message->sender?->avatar_url ?? $this->getDefaultAvatar($message->sender?->name ?? '?'),
            'content'       => $message->is_deleted ? null : $message->content,
            'type'          => $message->type,
            'is_mine'       => $isMine,
            'is_deleted'    => $message->is_deleted,
            'is_edited'     => $message->is_edited,
            'is_seen'       => $isSeen,
            'attachments'   => $attachments,
            'reactions'     => $reactions,
            'created_at'    => $message->created_at->format('H:i'),
            'created_at_full' => $message->created_at->toIso8601String(),
            'date_label'    => $message->created_at->isToday()
                ? 'Aujourd\'hui'
                : ($message->created_at->isYesterday() ? 'Hier' : $message->created_at->format('d/m/Y')),
        ];
    }

    private function getAvailableUsersForChat(User $user, string $search = '')
    {
        $allowedRoles = $this->getAllowedRoles($user->role);

        $query = User::where('id', '!=', $user->id)
            ->where('is_active', true);

        // Appliquer les filtres basés sur le rôle
        if (!empty($allowedRoles)) {
            $query = $query->where(function ($q) use ($allowedRoles, $user) {
                // 1. Voir les rôles autorisés
                $q->whereIn('role', $allowedRoles);

                // 2. Pour les enseignants : voir aussi les étudiants de leur classe
                if (in_array($user->role, ['teacher', 'professeur', 'prof_principal', 'prof_principal']) && $user->class_id) {
                    $q->orWhere(function($subQ) use ($user) {
                        $subQ->where('role', 'student')
                             ->where('class_id', $user->class_id);
                    });
                }

                // 3. Pour les étudiants : voir les autres étudiants de leur classe
                if ($user->role === 'student' && $user->class_id) {
                    $q->orWhere(function($subQ) use ($user) {
                        $subQ->where('role', 'student')
                             ->where('class_id', $user->class_id);
                    });
                }
            });
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->select(['id', 'name', 'role', 'profile_photo', 'last_login', 'class_id'])->limit(50)->get();
    }

    private function getAllowedRoles(string $role): array
    {
        return match($role) {
            // Admin, staff can message everyone
            'admin', 'censeur', 'intendant', 'secretaire', 'surveillant' =>
                ['admin', 'censeur', 'intendant', 'secretaire', 'surveillant', 'teacher', 'professeur', 'prof_principal', 'parent', 'student'],
            // Teachers can message other staff, teachers, parents, and students
            'teacher', 'professeur', 'prof_principal' =>
                ['admin', 'censeur', 'intendant', 'teacher', 'professeur', 'prof_principal', 'parent', 'student'],
            // Parents can message admin, teachers, and other parents
            'parent' =>
                ['admin', 'censeur', 'intendant', 'teacher', 'professeur', 'prof_principal', 'parent'],
            // Students can message admin, teachers, and classmates
            'student' =>
                ['admin', 'teacher', 'professeur', 'prof_principal', 'student'],
            default => [],
        };
    }

    /**
     * Applique les filtres de permissions selon le rôle de l'utilisateur.
     * Filtre par classe pour les enseignants et étudiants.
     */
    private function applyUserPermissionFilters($query, User $user)
    {
        $allowedRoles = $this->getAllowedRoles($user->role);

        if (empty($allowedRoles)) {
            return $query->where('id', '=', null); // Aucun accès
        }

        // Commencer avec les rôles autorisés
        $query = $query->where(function ($q) use ($allowedRoles, $user) {
            // 1. Voir les rôles autorisés
            $q->whereIn('role', $allowedRoles);

            // 2. Pour les enseignants : voir aussi les étudiants de leur classe
            if (in_array($user->role, ['professeur', 'prof_principal']) && $user->class_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('role', 'student')
                         ->where('class_id', $user->class_id);
                });
            }

            // 3. Pour les étudiants : voir les autres étudiants de leur classe
            if ($user->role === 'student' && $user->class_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('role', 'student')
                         ->where('class_id', $user->class_id);
                });
            }
        });

        return $query;
    }

    private function findExistingPrivateConversation(int $userId1, int $userId2): ?Conversation
    {
        return Conversation::where('type', 'private')
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId1))
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId2))
            ->first();
    }

    private function getTotalUnread(int $userId): int
    {
        return DB::table('conversation_participants')
            ->where('user_id', $userId)
            ->sum('unread_count') ?: 0;
    }

    private function isOnline(User $user): bool
    {
        return $user->last_login && $user->last_login->diffInMinutes(now()) < 5;
    }

    private function getRoleLabel(string $role): string
    {
        return match($role) {
            'admin'           => 'Administrateur',
            'censeur'         => 'Censeur',
            'intendant'       => 'Intendant',
            'secretaire'      => 'Secrétaire',
            'surveillant'     => 'Surveillant',
            'teacher'         => 'Enseignant',
            'professeur'      => 'Enseignant',
            'prof_principal'  => 'Prof. Principal',
            'parent'          => 'Parent',
            'student'         => 'Élève',
            default           => ucfirst($role),
        };
    }

    private function getDefaultAvatar(string $name): string
    {
        $initials = collect(explode(' ', $name))
            ->map(fn($w) => strtoupper($w[0] ?? ''))
            ->take(2)
            ->join('');

        return 'https://ui-avatars.com/api/?name=' . urlencode($initials)
            . '&background=1abc9c&color=fff&size=80&bold=true';
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    // ════════════════════════════════════════════════
    //  ÉDITION ET SUPPRESSION DE MESSAGES
    // ════════════════════════════════════════════════

    /**
     * Éditer un message.
     */
    public function editMessage(Request $request, Message $message): JsonResponse
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est l'auteur
        if ($message->user_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        try {
            $message->update([
                'body' => $validated['body'],
                'content' => $validated['body'],
                'is_edited' => true,
                'edited_at' => now(),
            ]);

            // Broadcast l'édition
            event(new \App\Events\MessageEdited($message));

            $message->load(['sender', 'attachments', 'reactions.user']);

            return response()->json([
                'success' => true,
                'message' => $this->formatMessage($message, $user->id),
            ]);
        } catch (\Exception $e) {
            \Log::error('Edit message error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la modification.'], 500);
        }
    }

    /**
     * Supprimer le message pour l'expéditeur uniquement.
     */
    public function deleteMessageForSender(Request $request, Message $message): JsonResponse
    {
        $user = Auth::user();

        if ($message->user_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        try {
            $message->update(['is_deleted_for_sender' => true]);

            event(new \App\Events\MessageEdited($message));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression.'], 500);
        }
    }

    /**
     * Supprimer le message pour tout le monde.
     */
    public function deleteMessageForAll(Request $request, Message $message): JsonResponse
    {
        $user = Auth::user();

        if ($message->user_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        try {
            $message->update([
                'is_deleted_for_all' => true,
                'body' => null,
                'content' => null,
            ]);

            event(new \App\Events\MessageEdited($message));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression.'], 500);
        }
    }

    // ════════════════════════════════════════════════
    //  STATUTS DE LECTURE
    // ════════════════════════════════════════════════

    /**
     * Marquer un message comme lu.
     */
    public function markMessageAsRead(Request $request, Message $message): JsonResponse
    {
        $user = Auth::user();

        try {
            // Créer un read receipt
            \App\Models\MessageReadReceipt::updateOrCreate(
                ['message_id' => $message->id, 'user_id' => $user->id],
                ['read_at' => now()]
            );

            // Broadcast l'événement
            event(new \App\Events\MessageRead(
                $message->conversation_id,
                $user->id,
                now()
            ));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Mark read error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur.'], 500);
        }
    }

    /**
     * Obtenir les statuts de lecture d'un message.
     */
    public function getReadStatus(Message $message): JsonResponse
    {
        $conversation = $message->conversation;
        
        $readReceipts = \App\Models\MessageReadReceipt::where('message_id', $message->id)
            ->with('user')
            ->get();

        $totalParticipants = $conversation->participants->count() - 1; // Excluding sender

        return response()->json([
            'message_id' => $message->id,
            'read_count' => $readReceipts->count(),
            'total_participants' => $totalParticipants,
            'readers' => $readReceipts->map(fn($r) => [
                'user_id' => $r->user_id,
                'user_name' => $r->user->name,
                'avatar' => $r->user->profile_photo ? asset('storage/' . $r->user->profile_photo) : null,
                'read_at' => $r->read_at->diffForHumans(),
            ]),
            'status' => $readReceipts->isNotEmpty() ? 'read' : 'sent',
        ]);
    }

    // ════════════════════════════════════════════════
    //  APPELS WEBRTC
    // ════════════════════════════════════════════════

    /**
     * Initier un appel (audio ou vidéo).
     */
    public function initiateCall(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'call_type' => 'required|in:audio,video',
            'room_id' => 'nullable|string',
        ]);

        try {
            $recipient = User::findOrFail($validated['recipient_id']);

            // Vérifier les permissions
            if (!app(\App\Services\ChatService::class)->canMessageUser($user, $recipient)) {
                return response()->json(['error' => 'Pas d\'autorisation pour appeler.'], 403);
            }

            $peerId = uniqid('peer_', true);
            $roomId = $validated['room_id'] ?? uniqid('room_', true);

            // Broadcast l'appel
            event(new \App\Events\CallInitiated(
                $user->id,
                $user->name,
                $recipient->id,
                $peerId,
                $validated['call_type'],
                $roomId
            ));

            return response()->json([
                'success' => true,
                'peer_id' => $peerId,
                'room_id' => $roomId,
                'call_type' => $validated['call_type'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Initiate call error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'appel.'], 500);
        }
    }

    /**
     * Répondre à un appel.
     */
    public function answerCall(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'caller_id' => 'required|integer|exists:users,id',
            'room_id' => 'required|string',
        ]);

        try {
            $caller = User::findOrFail($validated['caller_id']);
            $peerId = uniqid('peer_', true);

            event(new \App\Events\CallAnswered(
                $caller->id,
                $user->id,
                $user->name,
                $peerId,
                $validated['room_id']
            ));

            return response()->json([
                'success' => true,
                'peer_id' => $peerId,
                'room_id' => $validated['room_id'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Answer call error: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la réponse.'], 500);
        }
    }

    /**
     * Terminer un appel.
     */
    public function endCall(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'reason' => 'nullable|in:rejected,ended,timeout',
            'room_id' => 'nullable|string',
        ]);

        try {
            event(new \App\Events\CallEnded(
                $user->id,
                $validated['recipient_id'],
                $validated['reason'] ?? 'ended',
                $validated['room_id'] ?? null
            ));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur.'], 500);
        }
    }

    // ════════════════════════════════════════════════
    //  PRÉSENCE UTILISATEURS
    // ════════════════════════════════════════════════

    /**
     * Mettre à jour le statut en ligne.
     */
    public function updateOnlineStatus(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'is_online' => 'required|boolean',
        ]);

        try {
            $user->update([
                'is_online' => $validated['is_online'],
                'last_login' => now(),
            ]);

            event(new \App\Events\UserOnlineStatusChanged(
                $user->id,
                $user->name,
                $validated['is_online'],
                $user->last_login
            ));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur.'], 500);
        }
    }
}
