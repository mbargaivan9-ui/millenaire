<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Corriger les rôles obsolètes vers les rôles simplifiés
        DB::table('users')->where('role', 'professeur')->update(['role' => 'teacher']);
        DB::table('users')->where('role', 'prof_principal')->update(['role' => 'teacher']);
        DB::table('users')->where('role', 'censeur')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'intendant')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'secretaire')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'surveillant')->update(['role' => 'admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Impossible de revenir - les rôles précédents n'existent plus
    }
};
