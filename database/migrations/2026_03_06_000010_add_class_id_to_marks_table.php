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
        // Add class_id column to marks table for easier filtering
        if (Schema::hasTable('marks') && !Schema::hasColumn('marks', 'class_id')) {
            Schema::table('marks', function (Blueprint $table) {
                $table->foreignId('class_id')
                    ->nullable()
                    ->after('class_subject_teacher_id')
                    ->constrained('classes')
                    ->nullOnDelete();
                $table->index('class_id');
            });

            // Populate class_id from class_subject_teacher relationship
            \DB::statement('
                UPDATE marks m
                JOIN class_subject_teacher cst ON m.class_subject_teacher_id = cst.id
                SET m.class_id = cst.class_id
                WHERE m.class_id IS NULL
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('marks') && Schema::hasColumn('marks', 'class_id')) {
            Schema::table('marks', function (Blueprint $table) {
                $table->dropForeign(['class_id']);
                $table->dropIndex(['class_id']);
                $table->dropColumn('class_id');
            });
        }
    }
};
