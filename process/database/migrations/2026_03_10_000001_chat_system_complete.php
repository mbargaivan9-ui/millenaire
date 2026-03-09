<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration Phase Chat — Système de chat complet Millenaire Connect
 *
 * Tables créées / modifiées :
 *   - conversations (existante — aucun changement nécessaire)
 *   - conversation_participants (existante — aucun changement nécessaire)
 *   - messages (existante — aucun changement nécessaire)
 *   - message_attachments (existante — aucun changement nécessaire)
 *   - message_reactions (existante — aucun changement nécessaire)
 *   - chat_typing_indicators (NOUVELLE — indicateurs de frappe temps réel)
 *   - user_online_status (NOUVELLE — statut en ligne)
 *
 * Toutes les tables existantes sont protégées par des guards if (!hasTable)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. S'assurer que les tables de base existent ──

        if (! Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['private', 'group', 'class'])->default('private');
                $table->string('name')->nullable();
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->timestamp('last_message_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
                $table->index(['type', 'last_message_at']);
                $table->index('created_by');
            });
        }

        if (! Schema::hasTable('conversation_participants')) {
            Schema::create('conversation_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->integer('unread_count')->default(0);
                $table->timestamp('last_read_at')->nullable();
                $table->boolean('is_muted')->default(false);
                $table->boolean('is_archived')->default(false);
                $table->timestamps();
                $table->unique(['conversation_id', 'user_id']);
                $table->index('user_id');
                $table->index('conversation_id');
            });
        }

        if (! Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
                $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
                $table->text('content')->nullable();
                $table->enum('type', ['text', 'image', 'file', 'audio', 'video'])->default('text');
                $table->boolean('is_edited')->default(false);
                $table->timestamp('edited_at')->nullable();
                $table->boolean('is_deleted')->default(false);
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();
                $table->index(['conversation_id', 'created_at']);
                $table->index('sender_id');
                $table->index('is_deleted');
            });
        }

        if (! Schema::hasTable('message_attachments')) {
            Schema::create('message_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->onDelete('cascade');
                $table->string('file_name');
                $table->string('file_path');
                $table->enum('file_type', ['image', 'document', 'audio', 'video']);
                $table->bigInteger('file_size');
                $table->string('mime_type');
                $table->timestamps();
                $table->index('message_id');
            });
        }

        if (! Schema::hasTable('message_reactions')) {
            Schema::create('message_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('emoji');
                $table->timestamps();
                $table->unique(['message_id', 'user_id', 'emoji']);
                $table->index('message_id');
                $table->index('user_id');
            });
        }

        // ── 2. Nouvelles tables pour le chat ──

        if (! Schema::hasTable('chat_typing_indicators')) {
            Schema::create('chat_typing_indicators', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamp('typing_at')->nullable();
                $table->timestamps();
                $table->unique(['conversation_id', 'user_id']);
                $table->index(['conversation_id', 'typing_at']);
            });
        }

        // ── 3. Ajouter colonnes manquantes ──

        // Ajouter receiver_id aux messages s'il n'existe pas (compat ancienne structure)
        if (Schema::hasTable('messages') && ! Schema::hasColumn('messages', 'receiver_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->foreignId('receiver_id')->nullable()->after('sender_id')
                    ->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_typing_indicators');
    }
};
