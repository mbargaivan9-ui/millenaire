<?php

namespace App\Http\Controllers;

use App\Models\Bulletin;
use Illuminate\Http\Request;

/**
 * BulletinVerifyController — Vérification Authenticité Bulletin via QR Code
 *
 * Phase 6 — Section Bulletins
 * Accessible publiquement (sans authentification)
 */
class BulletinVerifyController extends Controller
{
    /**
     * Vérifier un bulletin via son token (scanné depuis QR code).
     * Route: GET /bulletin/verify/{token}
     */
    public function verify(string $token)
    {
        $bulletin = Bulletin::where('verification_token', $token)
            ->with('student.user', 'student.classe')
            ->first();

        if (!$bulletin) {
            return view('public.bulletin-verify', [
                'valid'    => false,
                'bulletin' => null,
                'message'  => app()->getLocale() === 'fr'
                    ? 'Ce bulletin est introuvable ou le code est invalide.'
                    : 'This bulletin could not be found or the code is invalid.',
            ]);
        }

        return view('public.bulletin-verify', [
            'valid'    => true,
            'bulletin' => $bulletin,
            'message'  => app()->getLocale() === 'fr'
                ? '✅ Ce bulletin est authentique.'
                : '✅ This bulletin is authentic.',
        ]);
    }
}
