<?php

/**
 * SettingsTabs Livewire Component
 * 
 * Gère les onglets de paramètres administrateur avec loadage dynamique.
 * Permet l'édition en temps réel et la sauvegarde AJAX.
 * 
 * Phase 10 — Real-time Settings Interface
 */

namespace App\Livewire\Admin;

use App\Models\EstablishmentSetting;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingsTabs extends Component
{
    use WithFileUploads;

    public EstablishmentSetting $settings;
    public string $activeTab = 'identity';
    public string $saveStatus = '';
    public bool $isSaving = false;

    public array $formData = [];
    public array $testimonials = [];

    public function mount()
    {
        $this->settings = EstablishmentSetting::getInstance();
        $this->testimonials = Testimonial::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        
        // Initialiser formData depuis settings
        $this->initializeFormData();
    }

    private function initializeFormData()
    {
        // Identité
        $this->formData['platform_name'] = $this->settings->platform_name ?? 'Millénaire Connect';
        $this->formData['slogan'] = $this->settings->slogan ?? '';
        $this->formData['primary_color'] = $this->settings->primary_color ?? '#0d9488';
        $this->formData['secondary_color'] = $this->settings->secondary_color ?? '#0f766e';
        $this->formData['years_existence'] = $this->settings->years_existence ?? 10;

        // Hero
        $this->formData['hero_title'] = $this->settings->hero_title ?? '';
        $this->formData['hero_subtitle'] = $this->settings->hero_subtitle ?? '';
        $this->formData['hero_cta_text'] = $this->settings->hero_cta_text ?? '';

        // Proviseur
        $this->formData['proviseur_name'] = $this->settings->proviseur_name ?? '';
        $this->formData['proviseur_title'] = $this->settings->proviseur_title ?? '';
        $this->formData['proviseur_bio'] = $this->settings->proviseur_bio ?? '';

        // À Propos
        $this->formData['about_title'] = $this->settings->about_title ?? '';
        $this->formData['about_description'] = $this->settings->about_description ?? '';

        // Contact
        $this->formData['phone'] = $this->settings->phone ?? '';
        $this->formData['email'] = $this->settings->email ?? '';
        $this->formData['address'] = $this->settings->address ?? '';
        $this->formData['social_facebook'] = $this->settings->social_facebook ?? '';
        $this->formData['social_twitter'] = $this->settings->social_twitter ?? '';
        $this->formData['google_maps_url'] = $this->settings->google_maps_url ?? '';

        // Académique
        $this->formData['anglophone_grading'] = $this->settings->anglophone_grading ?? 'letter';
        $this->formData['sequences_per_term'] = $this->settings->sequences_per_term ?? 2;

        // Notifications
        $this->formData['notify_absence_parent'] = $this->settings->notify_absence_parent ?? true;
        $this->formData['notify_new_bulletin'] = $this->settings->notify_new_bulletin ?? true;
        $this->formData['notify_payment_success'] = $this->settings->notify_payment_success ?? true;
        $this->formData['email_notifications'] = $this->settings->email_notifications ?? false;
    }

    public function setActiveTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    /**
     * Sauvegarde les données du formulaire via AJAX
     */
    public function saveTab()
    {
        $this->isSaving = true;
        $this->saveStatus = '';

        try {
            // Validation basée sur l'onglet actif
            $validated = $this->validateTabData();

            // Préparer les données pour sauvegarde
            $data = [];
            foreach ($validated as $key => $value) {
                if (!empty($value) || is_bool($value)) {
                    $data[$key] = $value;
                }
            }

            // Sauvegarder directement
            $this->settings->fill($data)->save();

            // Invalider le cache
            Cache::forget('public.announcements');
            Cache::forget('public.teachers');
            Cache::forget('public.testimonials');
            Cache::forget('public.stats');

            // Broadcaster l'événement
            if (class_exists(\App\Events\SettingsUpdated::class)) {
                broadcast(new \App\Events\SettingsUpdated($this->settings->toArray()))->toOthers();
            }

            // Log activité
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['tab' => $this->activeTab, 'fields' => array_keys($data)])
                ->log('Onglet paramètres mis à jour: ' . $this->activeTab);

            $this->saveStatus = 'success';
            $this->dispatch('settingsSaved', ['tab' => $this->activeTab]);
            
        } catch (\Exception $e) {
            $this->saveStatus = 'error: ' . $e->getMessage();
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['error' => $e->getMessage(), 'tab' => $this->activeTab])
                ->log('Erreur lors de la mise à jour des paramètres');
        } finally {
            $this->isSaving = false;
        }
    }

    /**
     * Valide et retourne les données de l'onglet actif
     */
    private function validateTabData(): array
    {
        return match($this->activeTab) {
            'identity' => [
                'platform_name' => $this->formData['platform_name'] ?? '',
                'slogan' => $this->formData['slogan'] ?? '',
                'primary_color' => $this->formData['primary_color'] ?? '#0d9488',
                'secondary_color' => $this->formData['secondary_color'] ?? '#0f766e',
                'years_existence' => $this->formData['years_existence'] ?? 10,
            ],
            'hero' => [
                'hero_title' => $this->formData['hero_title'] ?? '',
                'hero_subtitle' => $this->formData['hero_subtitle'] ?? '',
                'hero_cta_text' => $this->formData['hero_cta_text'] ?? '',
            ],
            'proviseur' => [
                'proviseur_name' => $this->formData['proviseur_name'] ?? '',
                'proviseur_title' => $this->formData['proviseur_title'] ?? '',
                'proviseur_bio' => $this->formData['proviseur_bio'] ?? '',
            ],
            'about' => [
                'about_title' => $this->formData['about_title'] ?? '',
                'about_description' => $this->formData['about_description'] ?? '',
            ],
            'contact' => [
                'phone' => $this->formData['phone'] ?? '',
                'email' => $this->formData['email'] ?? '',
                'address' => $this->formData['address'] ?? '',
                'social_facebook' => $this->formData['social_facebook'] ?? '',
                'social_twitter' => $this->formData['social_twitter'] ?? '',
                'google_maps_url' => $this->formData['google_maps_url'] ?? '',
            ],
            'academic' => [
                'anglophone_grading' => $this->formData['anglophone_grading'] ?? 'letter',
                'sequences_per_term' => $this->formData['sequences_per_term'] ?? 2,
            ],
            'notifications' => [
                'notify_absence_parent' => $this->formData['notify_absence_parent'] ?? true,
                'notify_new_bulletin' => $this->formData['notify_new_bulletin'] ?? true,
                'notify_payment_success' => $this->formData['notify_payment_success'] ?? true,
                'email_notifications' => $this->formData['email_notifications'] ?? false,
            ],
            default => [],
        };
    }

    public function render()
    {
        return view('livewire.admin.settings-tabs', [
            'settings' => $this->settings,
            'activeTab' => $this->activeTab,
            'isFr' => app()->getLocale() === 'fr',
        ]);
    }
}
