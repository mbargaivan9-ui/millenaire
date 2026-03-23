<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\EstablishmentSetting;
use Illuminate\Support\Facades\Storage;

class DiagnosticSettingsController
{
    /**
     * Diagnostic Route - Test upload and display of images
     */
    public function diagnostic()
    {
        $settings = EstablishmentSetting::getInstance();
        
        $diagnostic = [
            'logo_path' => $settings->logo_path,
            'logo_exists' => $settings->logo_path ? file_exists(public_path($settings->logo_path)) : false,
            'logo_storage_exists' => $settings->logo_path ? Storage::disk('public')->exists($settings->logo_path) : false,
            'logo_url' => $settings->logo_path ? asset($settings->logo_path) : null,
            'carousel_images' => $settings->carousel_images,
            'carousel_images_valid' => [],
        ];

        if ($settings->carousel_images) {
            foreach ($settings->carousel_images as $i => $img) {
                $diagnostic['carousel_images_valid'][$i] = [
                    'path' => $img,
                    'file_exists' => file_exists(public_path($img)),
                    'storage_exists' => Storage::disk('public')->exists($img),
                    'url' => asset($img),
                ];
            }
        }

        return response()->json($diagnostic, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
