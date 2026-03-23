<?php

/**
 * SettingsApiController — API REST pour Settings 
 * 
 * Permet les mises à jour AJAX et la synchronisation en temps réel
 * 
 * Phase 10 — Real-time Settings API
 */

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\EstablishmentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsApiController extends Controller
{
    /**
     * Mise à jour partielle des paramètres via AJAX
     * Accepte les mises à jour par onglet/section
     */
    public function updateSection(Request $request, string $section)
    {
        $this->authorize('admin');

        try {
            $settings = EstablishmentSetting::getInstance();
            
            // Récupérer uniquement les champs du formulaire
            $data = $request->only($this->getAllowedFieldsForSection($section));

            // Validation simple
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = trim($value);
                }
            }

            // Sauvegarder
            $settings->fill($data)->save();

            // Invalider le cache
            $this->clearCache();

            // Broadcaster l'événement
            if (class_exists(\App\Events\SettingsUpdated::class)) {
                broadcast(new \App\Events\SettingsUpdated($settings->toArray()))->toOthers();
            }

            // Log
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['section' => $section, 'fields' => array_keys($data)])
                ->log('Paramètres section ' . $section . ' mis à jour via API');

            return response()->json([
                'success' => true,
                'message' => __('messages.settings_saved'),
                'section' => $section,
                'timestamp' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['error' => $e->getMessage(), 'section' => $section])
                ->log('Erreur API settings: ' . $section);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'section' => $section,
            ], 422);
        }
    }

    /**
     * Retourne les champs autorisés pour une section
     */
    private function getAllowedFieldsForSection(string $section): array
    {
        return match($section) {
            'identity' => [
                'platform_name', 'slogan', 'primary_color', 'secondary_color', 'years_existence'
            ],
            'hero' => [
                'hero_title', 'hero_subtitle', 'hero_cta_text'
            ],
            'proviseur' => [
                'proviseur_name', 'proviseur_title', 'proviseur_bio'
            ],
            'about' => [
                'about_title', 'about_description'
            ],
            'contact' => [
                'phone', 'email', 'address', 'social_facebook', 'social_twitter', 'google_maps_url'
            ],
            'academic' => [
                'anglophone_grading', 'sequences_per_term'
            ],
            'notifications' => [
                'notify_absence_parent', 'notify_new_bulletin', 'notify_payment_success', 'email_notifications'
            ],
            default => [],
        };
    }

    /**
     * Retourne tous les paramètres actuels
     */
    public function getAll()
    {
        $this->authorize('admin');

        $settings = EstablishmentSetting::getInstance();

        return response()->json([
            'success' => true,
            'data' => $settings->toArray(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Invalide les caches
     */
    private function clearCache()
    {
        Cache::forget('public.announcements');
        Cache::forget('public.teachers');
        Cache::forget('public.testimonials');
        Cache::forget('public.stats');
    }
}
