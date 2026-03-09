<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\MessageAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Service de gestion des messages
 * SOLID - Single Responsibility Principle
 */
class MessageService
{
    /**
     * Attacher un fichier à un message
     */
    public function attachFile(Message $message, UploadedFile $file): void
    {
        $path = $file->store('chat/attachments/' . date('Y/m'), 'public');

        MessageAttachment::create([
            'message_id' => $message->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $this->getFileType($file),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
    }

    /**
     * Marquer les messages comme lus
     */
    public function markAsRead($conversationId, $userId): void
    {
        DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->update([
                'unread_count' => 0,
                'last_read_at' => now(),
            ]);
    }

    /**
     * Incrémenter le compteur de messages non lus
     */
    public function incrementUnreadCount(Conversation $conversation, $senderId): void
    {
        DB::table('conversation_participants')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $senderId)
            ->increment('unread_count');
    }

    /**
     * Déterminer le type de fichier
     */
    private function getFileType(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        return 'document';
    }

    /**
     * Obtenir les statistiques de conversation pour un utilisateur
     */
    public function getConversationStats($conversationId, $userId): array
    {
        $totalMessages = Message::where('conversation_id', $conversationId)->active()->count();
        $unreadCount = DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->value('unread_count') ?? 0;

        return [
            'total_messages' => $totalMessages,
            'unread_count' => $unreadCount,
        ];
    }
}
