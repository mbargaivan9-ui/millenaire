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
        Schema::table('student_guardian', function (Blueprint $table) {
            if (!Schema::hasColumn('student_guardian', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('relationship');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_guardian', function (Blueprint $table) {
            if (Schema::hasColumn('student_guardian', 'is_primary')) {
                $table->dropColumn('is_primary');
            }
        });
    }
};
