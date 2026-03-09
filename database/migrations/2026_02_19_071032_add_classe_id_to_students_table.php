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
        Schema::table('students', function (Blueprint $table) {
            // Add classe_id column if it doesn't exist
            if (!Schema::hasColumn('students', 'classe_id')) {
                $table->foreignId('classe_id')
                    ->nullable()
                    ->constrained('classes')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'class_id')) {
                $table->dropConstrainedForeignId('class_id');
            }
        });
    }
};
