<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('assignments', 'assignment_type')) {
                $table->string('assignment_type')->default('primary')->after('subject_id');
            }
            
            if (!Schema::hasColumn('assignments', 'can_teach_multiple_classes')) {
                $table->boolean('can_teach_multiple_classes')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Remove columns on rollback
            if (Schema::hasColumn('assignments', 'assignment_type')) {
                $table->dropColumn('assignment_type');
            }
            
            if (Schema::hasColumn('assignments', 'can_teach_multiple_classes')) {
                $table->dropColumn('can_teach_multiple_classes');
            }
        });
    }
};
