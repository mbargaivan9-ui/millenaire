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
        // Add first_name and last_name to users table
        if (Schema::hasTable('users')) {
            if (!Schema::hasColumn('users', 'first_name')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('first_name')->nullable()->after('name');
                });
            }
            
            if (!Schema::hasColumn('users', 'last_name')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('last_name')->nullable()->after('first_name');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'first_name')) {
                    $table->dropColumn('first_name');
                }
                if (Schema::hasColumn('users', 'last_name')) {
                    $table->dropColumn('last_name');
                }
            });
        }
    }
};
