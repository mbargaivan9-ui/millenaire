<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // Add is_published column if it doesn't exist
            if (!Schema::hasColumn('announcements', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('status');
            }

            // Add is_featured column if it doesn't exist
            if (!Schema::hasColumn('announcements', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_published');
            }

            // Rename created_by from author_id if needed - author_id already exists
            // Just ensure the created_by column for consistency
            if (!Schema::hasColumn('announcements', 'created_by')) {
                // We'll use author_id as created_by doesn't exist
                // So we don't add it
            }

            // Add slug if it doesn't exist
            if (!Schema::hasColumn('announcements', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('title');
            }
        });

        // Sync is_published based on status
        try {
            DB::table('announcements')->where('status', 'active')->update(['is_published' => true]);
        } catch (\Exception $e) {
            // Silent fail if update doesn't work
        }
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'is_published')) {
                $table->dropColumn('is_published');
            }
            if (Schema::hasColumn('announcements', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
            if (Schema::hasColumn('announcements', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
