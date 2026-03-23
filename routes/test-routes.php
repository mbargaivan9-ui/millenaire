<?php

use Illuminate\Support\Facades\Route;
use App\Models\EstablishmentSetting;

Route::get('/test-logo-check', function () {
    $s = EstablishmentSetting::getInstance();
    
    return response()->json([
        'logo_path' => $s->logo_path,
        'platform_name' => $s->platform_name,
        'file_exists' => $s->logo_path ? file_exists(public_path($s->logo_path)) : false,
        'asset_url' => $s->logo_path ? asset($s->logo_path) : null,
        'carousel_images' => $s->carousel_images,
    ]);
});
