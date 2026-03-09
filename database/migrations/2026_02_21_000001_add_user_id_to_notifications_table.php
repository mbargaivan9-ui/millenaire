<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Add user_id column if it doesn't exist
            if (!Schema::hasColumn('notifications', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }

            // Add custom notification columns
            if (!Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->nullable();
            }
            if (!Schema::hasColumn('notifications', 'message')) {
                $table->text('message')->nullable();
            }
            if (!Schema::hasColumn('notifications', 'category')) {
                $table->string('category')->nullable();
            }
            if (!Schema::hasColumn('notifications', 'related_entity_type')) {
                $table->string('related_entity_type')->nullable();
            }
            if (!Schema::hasColumn('notifications', 'related_entity_id')) {
                $table->unsignedBigInteger('related_entity_id')->nullable();
            }
            if (!Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            try { $table->dropConstrainedForeignId('user_id'); } catch(\Exception $e) {}
            try { $table->dropColumn('title', 'message', 'category', 'related_entity_type', 'related_entity_id', 'is_read'); } catch(\Exception $e) {}
        });
    }
};
