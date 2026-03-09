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
        Schema::table('establishment_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('establishment_settings', 'school_name_fr')) {
                $table->string('school_name_fr')->nullable()->after('platform_name');
            }
            if (!Schema::hasColumn('establishment_settings', 'school_name_en')) {
                $table->string('school_name_en')->nullable()->after('school_name_fr');
            }
            if (!Schema::hasColumn('establishment_settings', 'address')) {
                $table->string('address')->nullable()->after('school_name_en');
            }
            if (!Schema::hasColumn('establishment_settings', 'phone')) {
                $table->string('phone')->nullable()->after('address');
            }
            if (!Schema::hasColumn('establishment_settings', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('establishment_settings', 'proviseur_name')) {
                $table->string('proviseur_name')->nullable()->after('proviseur_title');
            }
            if (!Schema::hasColumn('establishment_settings', 'current_academic_year')) {
                $table->string('current_academic_year')->nullable()->after('sequences_per_term');
            }
            if (!Schema::hasColumn('establishment_settings', 'current_term')) {
                $table->integer('current_term')->default(1)->after('current_academic_year');
            }
            if (!Schema::hasColumn('establishment_settings', 'current_sequence')) {
                $table->integer('current_sequence')->default(1)->after('current_term');
            }
            if (!Schema::hasColumn('establishment_settings', 'grading_system')) {
                $table->string('grading_system')->default('20')->after('current_sequence');
            }
            if (!Schema::hasColumn('establishment_settings', 'pass_mark')) {
                $table->decimal('pass_mark', 5, 2)->default(10.00)->after('grading_system');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establishment_settings', function (Blueprint $table) {
            //
        });
    }
};
