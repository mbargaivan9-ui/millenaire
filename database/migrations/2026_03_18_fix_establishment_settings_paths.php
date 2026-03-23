<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Corriger les chemins du logo
        DB::table('establishment_settings')->whereNotNull('logo_path')->update([
            'logo_path' => DB::raw("CONCAT('storage/', logo_path)")
        ]);

        // Corriger les chemins du favicon
        DB::table('establishment_settings')->whereNotNull('favicon_path')->update([
            'favicon_path' => DB::raw("CONCAT('storage/', favicon_path)")
        ]);

        // Corriger les chemins hero_image
        DB::table('establishment_settings')->whereNotNull('hero_image')->update([
            'hero_image' => DB::raw("CONCAT('storage/', hero_image)")
        ]);

        // Corriger les chemins proviseur_photo
        DB::table('establishment_settings')->whereNotNull('proviseur_photo')->update([
            'proviseur_photo' => DB::raw("CONCAT('storage/', proviseur_photo)")
        ]);

        // Corriger les chemins signature_image
        DB::table('establishment_settings')->whereNotNull('signature_image')->update([
            'signature_image' => DB::raw("CONCAT('storage/', signature_image)")
        ]);

        // Corriger les chemins about_image
        DB::table('establishment_settings')->whereNotNull('about_image')->update([
            'about_image' => DB::raw("CONCAT('storage/', about_image)")
        ]);

        // Corriger les carousel_images
        // C'est plus complexe car c'est un JSON array
        $settings = DB::table('establishment_settings')->first();
        if ($settings && $settings->carousel_images) {
            $images = json_decode($settings->carousel_images, true);
            if (is_array($images)) {
                $corrected = array_map(function($img) {
                    // Si l'image commence par 'settings/', ajouter 'storage/' avant
                    if (strpos($img, 'storage/') === 0) {
                        return $img; // Déjà corrigée
                    } elseif (strpos($img, 'settings/') === 0 || strpos($img, 'img/') === 0) {
                        return 'storage/' . $img;
                    }
                    return $img;
                }, $images);
                
                DB::table('establishment_settings')->update([
                    'carousel_images' => json_encode($corrected)
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse les corrections
        DB::table('establishment_settings')->whereNotNull('logo_path')->update([
            'logo_path' => DB::raw("REPLACE(logo_path, 'storage/', '')")
        ]);

        DB::table('establishment_settings')->whereNotNull('favicon_path')->update([
            'favicon_path' => DB::raw("REPLACE(favicon_path, 'storage/', '')")
        ]);

        DB::table('establishment_settings')->whereNotNull('hero_image')->update([
            'hero_image' => DB::raw("REPLACE(hero_image, 'storage/', '')")
        ]);

        DB::table('establishment_settings')->whereNotNull('proviseur_photo')->update([
            'proviseur_photo' => DB::raw("REPLACE(proviseur_photo, 'storage/', '')")
        ]);

        DB::table('establishment_settings')->whereNotNull('signature_image')->update([
            'signature_image' => DB::raw("REPLACE(signature_image, 'storage/', '')")
        ]);

        DB::table('establishment_settings')->whereNotNull('about_image')->update([
            'about_image' => DB::raw("REPLACE(about_image, 'storage/', '')")
        ]);

        $settings = DB::table('establishment_settings')->first();
        if ($settings && $settings->carousel_images) {
            $images = json_decode($settings->carousel_images, true);
            if (is_array($images)) {
                $corrected = array_map(function($img) {
                    return str_replace('storage/', '', $img);
                }, $images);
                
                DB::table('establishment_settings')->update([
                    'carousel_images' => json_encode($corrected)
                ]);
            }
        }
    }
};
