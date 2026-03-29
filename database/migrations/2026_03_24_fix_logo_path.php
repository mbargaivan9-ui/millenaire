<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix logo_path to use correct directory (icons instead of img)
        // and handle migration from storage/ prefix if needed
        $settings = DB::table('establishment_settings')->find(1);
        
        if ($settings && $settings->logo_path) {
            $logoPath = $settings->logo_path;
            
            // Remove 'storage/' prefix if it exists
            if (stripos($logoPath, 'storage/') === 0) {
                $logoPath = substr($logoPath, 8); // Remove 'storage/'
            }
            
            // Replace 'img/' with 'icons/'
            $logoPath = str_replace('img/', 'icons/', $logoPath);
            
            // Use icon-512.png as default if file doesn't exist
            if (!file_exists(public_path($logoPath))) {
                $logoPath = 'icons/icon-512.png';
            }
            
            DB::table('establishment_settings')
                ->where('id', 1)
                ->update(['logo_path' => $logoPath]);
        } else {
            // Set default logo if none exists
            DB::table('establishment_settings')
                ->where('id', 1)
                ->update(['logo_path' => 'icons/icon-512.png']);
        }
        
        // Also fix carousel_images path
        if ($settings && $settings->carousel_images) {
            $images = json_decode($settings->carousel_images, true);
            if (is_array($images)) {
                $updated = false;
                foreach ($images as &$image) {
                    if (stripos($image, 'img/') === 0) {
                        $image = str_replace('img/', 'images/', $image);
                        $updated = true;
                    }
                }
                
                if ($updated) {
                    DB::table('establishment_settings')
                        ->where('id', 1)
                        ->update(['carousel_images' => json_encode($images)]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert logo_path changes if needed
        // This is optional for backward compatibility
    }
};
