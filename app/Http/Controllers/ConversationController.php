<?php

namespace App\Http\Controllers;

use App\Models\ConversationParticipant;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * Display a listing of conversations
     */
    public function index()
    {
        $conversations = auth()->user()->conversations()
            ->with(['lastMessage', 'participants'])
            ->latest('last_message_at')
            ->paginate(20);
        
        return view('conversations.index', compact('conversations'));
    }

    /**
     * Show the form for creating a new conversation
     */
    public function create()
    {
        $users = User::where('id', '!=', auth()->id())->get();
        
        return view('conversations.create', compact('users'));
    }

    /**
     * Store a newly created conversation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:private,group,class',
            'name' => 'nullable|string|max:255',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        $conversation = Conversation::create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'created_by' => auth()->id(),
        ]);

        // Add current user as participant
        $participants = [$request->user()->id, ...$validated['participants']];
        $conversation->participants()->attach(array_unique($participants));

        return redirect()->route('conversations.show', $conversation)
            ->with('success', 'Conversation créée avec succès');
    }

    /**
     * Display the specified conversation
     */
    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->with(['sender', 'attachments', 'reactions'])
            ->latest()
            ->paginate(30);

        $messages = $messages->reverse();

        // Mark messages as read
        $conversation->participants()
            ->where('user_id', auth()->id())
            ->update([
                'last_read_at' => now(),
                'unread_count' => 0,
            ]);

        return view('conversations.show', compact('conversation', 'messages'));
    }

    /**
     * Show the form for editing the specified conversation
     */
    public function edit(Conversation $conversation)
    {
        $this->authorize('update', $conversation);
        
        $users = User::where('id', '!=', auth()->id())->get();
        
        return view('conversations.edit', compact('conversation', 'users'));
    }

    /**
     * Update the specified conversation
     */
    public function update(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        $conversation->update(['name' => $validated['name']]);

        $conversation->participants()->sync($validated['participants']);

        return redirect()->route('conversations.show', $conversation)
            ->with('success', 'Conversation mise à jour avec succès');
    }

    /**
     * Remove the specified conversation
     */
    public function destroy(Conversation $conversation)
    {
        $this->authorize('delete', $conversation);

        $conversation->delete();

        return redirect()->route('conversations.index')
            ->with('success', 'Conversation supprimée avec succès');
    }

    /**
     * Add participant to conversation
     */
    public function addParticipant(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $conversation->participants()->attach($validated['user_id']);

        return redirect()->back()
            ->with('success', 'Participant ajouté avec succès');
    }

    /**
     * Remove participant from conversation
     */
    public function removeParticipant(Conversation $conversation, User $user)
    {
        $this->authorize('update', $conversation);

        $conversation->participants()->detach($user->id);

        return redirect()->back()
            ->with('success', 'Participant supprimé avec succès');
    }

    /**
     * Archive conversation for current user
     */
    public function archive(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->participants()
            ->where('user_id', auth()->id())
            ->update(['is_archived' => true]);

        return redirect()->route('conversations.index')
            ->with('success', 'Conversation archivée');
    }

    /**
     * Mute conversation for current user
     */
    public function mute(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $conversation->participants()
            ->where('user_id', auth()->id())
            ->update(['is_muted' => true]);

        return redirect()->back()
            ->with('success', 'Conversation muette');
    }
}
