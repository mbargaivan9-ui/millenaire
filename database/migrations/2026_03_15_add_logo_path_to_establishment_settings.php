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
            // Ajouter le champ logo_path s'il n'existe pas
            if (!Schema::hasColumn('establishment_settings', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('platform_name');
            }

            // Ajouter le champ proviseur_photo s'il n'existe pas
            if (!Schema::hasColumn('establishment_settings', 'proviseur_photo')) {
                $table->string('proviseur_photo')->nullable()->after('proviseur_name');
            }

            // Ajouter le champ proviseur_bio s'il n'existe pas
            if (!Schema::hasColumn('establishment_settings', 'proviseur_bio')) {
                $table->text('proviseur_bio')->nullable()->after('proviseur_photo');
            }

            // Ajouter le champ about_title s'il n'existe pas
            if (!Schema::hasColumn('establishment_settings', 'about_title')) {
                $table->string('about_title')->nullable()->after('proviseur_bio');
            }

            // Ajouter le champ about_description s'il n'existe pas
            if (!Schema::hasColumn('establishment_settings', 'about_description')) {
                $table->text('about_description')->nullable()->after('about_title');
            }

            // Ajouter le champ carousel_images s'il n'existe pas
            if (!Schema::hasColumn('establishment_settings', 'carousel_images')) {
                $table->json('carousel_images')->nullable()->after('about_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establishment_settings', function (Blueprint $table) {
            $columns = ['logo_path', 'proviseur_photo', 'proviseur_bio', 'about_title', 'about_description', 'carousel_images'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('establishment_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
