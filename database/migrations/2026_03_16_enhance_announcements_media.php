<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Photo de couverture (banner/thumbnail)
            if (!Schema::hasColumn('announcements', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('image');
            }

            // Fichier joint
            if (!Schema::hasColumn('announcements', 'attached_file')) {
                $table->string('attached_file')->nullable()->after('cover_image');
            }

            // Nom du fichier joint
            if (!Schema::hasColumn('announcements', 'attachment_name')) {
                $table->string('attachment_name')->nullable()->after('attached_file');
            }

            // Type de fichier pour téléchargement
            if (!Schema::hasColumn('announcements', 'attachment_type')) {
                $table->string('attachment_type')->nullable()->after('attachment_name');
            }

            // Taille du fichier
            if (!Schema::hasColumn('announcements', 'attachment_size')) {
                $table->bigInteger('attachment_size')->nullable()->after('attachment_type');
            }

            // Statut de publication (pour meilleur contrôle)
            if (!Schema::hasColumn('announcements', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('is_featured');
            }

            // Catégorie (amélioration)
            if (!Schema::hasColumn('announcements', 'category')) {
                $table->string('category')->nullable()->after('is_featured');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $columns = ['cover_image', 'attached_file', 'attachment_name', 'attachment_type', 'attachment_size', 'is_published', 'category'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('announcements', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
