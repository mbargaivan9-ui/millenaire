<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Affiche les messages
     */
    public function index()
    {
        $userId = Auth::id();
        
        // Récupérer les conversations de l'utilisateur
        $conversations = Conversation::forUser($userId)
            ->with(['participants', 'lastMessage.sender'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        $unreadCount = Message::whereHas('conversation.participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('sender_id', '!=', $userId)
        ->where(function ($q) use ($userId) {
            // Check if unread for this user
            $q->whereDoesntHave('conversation.participants', function ($q2) use ($userId) {
                $q2->where('user_id', $userId)
                   ->where('last_read_at', '>=', \DB::raw('messages.created_at'));
            });
        })
        ->count();

        return view('messages.index', compact('conversations', 'unreadCount'));
    }

    /**
     * Affiche une conversation complète
     */
    public function show(Message $message)
    {
        $userId = Auth::id();
        $conversation = $message->conversation;

        // Check user is participant
        if (!$conversation->participants()->where('user_id', $userId)->exists()) {
            abort(403);
        }

        // Load participants and messages
        $conversation->load('participants');
        
        // Get all messages in conversation with pagination
        $messages = $conversation->messages()
            ->with('sender')
            ->paginate(30);

        // Mark as read for this user
        $conversation->participants()
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);

        return view('messages.show', compact('conversation', 'messages'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $users = User::where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();

        return view('messages.create', compact('users'));
    }

    /**
     * Sauvegarde un message
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id|different:' . Auth::id(),
            'body' => 'required|string',
        ]);

        $senderId = Auth::id();
        $receiverId = $validated['receiver_id'];
        
        // Chercher une conversation privée existante entre ces deux users
        $conversation = Conversation::whereType('private')
            ->whereHas('participants', function ($q) use ($senderId) {
                $q->where('user_id', $senderId);
            })
            ->whereHas('participants', function ($q) use ($receiverId) {
                $q->where('user_id', $receiverId);
            })
            ->first();

        // Si pas de conversation, la créer
        if (!$conversation) {
            $conversation = Conversation::create([
                'type' => 'private',
                'created_by' => $senderId,
            ]);

            // Ajouter les deux participants
            $conversation->participants()->attach([$senderId, $receiverId]);
        }

        // Créer le message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'content' => $validated['body'],
            'type' => 'text',
        ]);

        // Mettre à jour last_message_at de la conversation
        $conversation->update(['last_message_at' => now()]);

        // Réinitialiser unread_count pour les participants
        $conversation->participants()->update(['unread_count' => 0]);

        return redirect()->route('messages.index')
            ->with('success', 'Message envoyé');
    }

    /**
     * Supprime un message
     */
    public function destroy(Message $message)
    {
        // Check if user is participant of the conversation
        if (!$message->conversation->participants()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $message->delete();

        return redirect()->route('messages.index')
            ->with('success', 'Message supprimé');
    }
}
