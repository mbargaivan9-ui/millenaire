<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crée une table pour suivre les statuts de lecture des messages
     * et ajoute des champs pour la suppression sélective.
     */
    public function up(): void
    {
        // Table pour tracker la lecture des messages
        Schema::create('message_read_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['message_id', 'user_id']);
            $table->index(['message_id', 'read_at']);
            $table->index('user_id');
        });

        // Ajouter colonnes de suppression si elles n'existent pas
        if (! Schema::hasColumn('messages', 'is_deleted_for_sender')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->boolean('is_deleted_for_sender')->default(false)->after('is_deleted');
                $table->boolean('is_deleted_for_all')->default(false)->after('is_deleted_for_sender');
            });
        }

        // Ajouter user_id si ce n'est pas sender_id
        if (! Schema::hasColumn('messages', 'body')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->text('body')->nullable()->after('content');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('message_read_receipts');

        if (Schema::hasColumn('messages', 'is_deleted_for_sender')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn(['is_deleted_for_sender', 'is_deleted_for_all']);
            });
        }

        if (Schema::hasColumn('messages', 'body')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('body');
            });
        }
    }
};
