<?php

namespace App\Contracts;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

/**
 * ChatServiceInterface
 * 
 * Contract for chat and messaging functionality
 * Supports all 4 roles: Admin, Teacher, Parent, Student
 */
interface ChatServiceInterface
{
    /**
     * Send a message in a conversation
     * 
     * @param Conversation $conversation
     * @param User $sender
     * @param string|null $content
     * @param string $type
     * @return Message
     */
    public function sendMessage(
        Conversation $conversation,
        User $sender,
        ?string $content = null,
        string $type = 'text'
    ): Message;

    /**
     * Create a new conversation
     * 
     * @param User $creator
     * @param string $type ('private', 'group', 'class')
     * @param array $participantIds
     * @param string|null $name
     * @return Conversation
     */
    public function createConversation(
        User $creator,
        string $type,
        array $participantIds,
        ?string $name = null
    ): Conversation;

    /**
     * Mark conversation messages as read
     * 
     * @param Conversation $conversation
     * @param User $user
     * @return void
     */
    public function markAsRead(Conversation $conversation, User $user): void;

    /**
     * Get user's conversations
     * 
     * @param User $user
     * @param string $filter ('all', 'unread', 'groups')
     * @return \Illuminate\Support\Collection
     */
    public function getUserConversations(User $user, string $filter = 'all');

    /**
     * Check if user can message another user
     * 
     * @param User $sender
     * @param User $recipient
     * @return bool
     */
    public function canMessageUser(User $sender, User $recipient): bool;

    /**
     * Get available users for that can be messaged
     * 
     * @param User $user
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableUsersForChat(User $user);
}
