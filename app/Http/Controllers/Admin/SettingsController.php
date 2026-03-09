<?php

/**
 * SettingsController — Gestion des Paramètres de la Plateforme
 *
 * Phase 3 — Section 4.1 — Interface Paramètres Admin
 * Contrôle total du contenu de la page d'accueil publique.
 * Chaque modification est broadcastée via Laravel Reverb (SettingsUpdated event).
 *
 * @package App\Http\Controllers\Admin
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EstablishmentSetting;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Affiche la page de paramètres.
     */
    public function index(): View
    {
        $settings     = EstablishmentSetting::getInstance();
        $testimonials = Testimonial::orderBy('created_at', 'desc')->get();

        return view('admin.settings.edit', compact('settings', 'testimonials'));
    }

    /**
     * Alias pour l'édition (même vue).
     */
    public function edit(): View
    {
        return $this->index();
    }

    /**
     * Met à jour les paramètres de la plateforme.
     * Invalide le cache et broadcaste un événement Reverb.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'platform_name'     => 'nullable|string|max:100',
            'slogan'            => 'nullable|string|max:200',
            'logo'              => 'nullable|image|mimes:jpeg,png,gif,webp,svg|max:2048',
            'favicon'           => 'nullable|image|mimes:ico,png|max:512',
            'hero_title'        => 'nullable|string|max:200',
            'hero_subtitle'     => 'nullable|string|max:500',
            'hero_cta_text'     => 'nullable|string|max:80',
            'hero_image'        => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'carousel_images.*' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'proviseur_name'    => 'nullable|string|max:150',
            'proviseur_title'   => 'nullable|string|max:150',
            'proviseur_photo'   => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'proviseur_bio'     => 'nullable|string|max:2000',
            'signature_image'   => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'about_title'       => 'nullable|string|max:200',
            'about_description' => 'nullable|string|max:3000',
            'about_image'       => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'primary_color'     => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color'   => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'years_existence'   => 'nullable|integer|min:1|max:200',
            'phone'             => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:100',
            'address'           => 'nullable|string|max:300',
            'social_facebook'   => 'nullable|url|max:300',
            'social_twitter'    => 'nullable|url|max:300',
            'google_maps_url'   => 'nullable|url|max:1000',
        ]);

        $settings = EstablishmentSetting::getInstance();
        $data     = [];

        // ─── Champs texte directs ────────────────────────────────────────────
        $textFields = [
            'platform_name', 'slogan', 'hero_title', 'hero_subtitle', 'hero_cta_text',
            'proviseur_name', 'proviseur_title', 'proviseur_bio',
            'about_title', 'about_description',
            'primary_color', 'secondary_color', 'years_existence',
            'phone', 'email', 'address', 'social_facebook', 'social_twitter', 'google_maps_url',
            'anglophone_grading', 'sequences_per_term',
        ];

        foreach ($textFields as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->$field;
            }
        }

        // ─── Checkboxes notifications ────────────────────────────────────────
        $data['notify_absence_parent']  = $request->boolean('notify_absence_parent');
        $data['notify_new_bulletin']    = $request->boolean('notify_new_bulletin');
        $data['notify_payment_success'] = $request->boolean('notify_payment_success');
        $data['email_notifications']    = $request->boolean('email_notifications');

        // ─── Upload Logo ─────────────────────────────────────────────────────
        if ($request->hasFile('logo')) {
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('settings', 'public');
        }

        // ─── Upload Favicon ──────────────────────────────────────────────────
        if ($request->hasFile('favicon')) {
            $data['favicon_path'] = $request->file('favicon')->store('settings', 'public');
        }

        // ─── Upload Hero Image ───────────────────────────────────────────────
        if ($request->hasFile('hero_image')) {
            if ($settings->hero_image && Storage::disk('public')->exists($settings->hero_image)) {
                Storage::disk('public')->delete($settings->hero_image);
            }
            $data['hero_image'] = $request->file('hero_image')->store('settings', 'public');
        }

        // ─── Upload Proviseur Photo ──────────────────────────────────────────
        if ($request->hasFile('proviseur_photo')) {
            if ($settings->proviseur_photo && Storage::disk('public')->exists($settings->proviseur_photo)) {
                Storage::disk('public')->delete($settings->proviseur_photo);
            }
            $data['proviseur_photo'] = $request->file('proviseur_photo')->store('settings/proviseur', 'public');
        }

        // ─── Upload Signature ────────────────────────────────────────────────
        if ($request->hasFile('signature_image')) {
            $data['signature_image'] = $request->file('signature_image')->store('settings', 'public');
        }

        // ─── Upload About Image ──────────────────────────────────────────────
        if ($request->hasFile('about_image')) {
            $data['about_image'] = $request->file('about_image')->store('settings', 'public');
        }

        // ─── Carousel Images ─────────────────────────────────────────────────
        $carouselImages = $request->input('carousel_keep', []);
        if ($request->hasFile('carousel_images')) {
            foreach ($request->file('carousel_images') as $img) {
                $carouselImages[] = $img->store('settings/carousel', 'public');
            }
        }
        if (!empty($carouselImages)) {
            $data['carousel_images'] = $carouselImages;
        }

        // ─── Barème appréciations ────────────────────────────────────────────
        if ($request->has('grade_labels')) {
            foreach ($request->input('grade_labels', []) as $i => $label) {
                $data["grade_label_{$i}"] = $label;
            }
        }

        // ─── Sauvegarde ──────────────────────────────────────────────────────
        $settings->fill($data)->save();

        // ─── Invalider le cache ──────────────────────────────────────────────
        Cache::forget('public.announcements');
        Cache::forget('public.teachers');
        Cache::forget('public.testimonials');
        Cache::forget('public.stats');

        // ─── Broadcaster l'événement Reverb ──────────────────────────────────
        if (class_exists(\App\Events\SettingsUpdated::class)) {
            broadcast(new \App\Events\SettingsUpdated($settings->toArray()))->toOthers();
        }

        // ─── Log activité ────────────────────────────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['updated_fields' => array_keys($data)])
            ->log('Paramètres de la plateforme mis à jour');

        return redirect()->route('admin.settings.index')
            ->with('success', app()->getLocale() === 'fr'
                ? 'Paramètres enregistrés avec succès !'
                : 'Settings saved successfully!');
    }

    /**
     * Show create testimonial form
     */
    public function createTestimonial(): View
    {
        return view('admin.testimonials.create');
    }

    /**
     * Store a new testimonial
     */
    public function storeTestimonial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_role' => 'nullable|string|max:255',
            'content' => 'required|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        Testimonial::create($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Testimonial créé avec succès');
    }

    /**
     * Show edit testimonial form
     */
    public function editTestimonial(Testimonial $testimonial): View
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    /**
     * Update testimonial
     */
    public function updateTestimonial(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_role' => 'nullable|string|max:255',
            'content' => 'required|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $testimonial->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Testimonial mis à jour avec succès');
    }

    /**
     * Delete testimonial
     */
    public function destroyTestimonial(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Testimonial supprimé avec succès');
    }
}
