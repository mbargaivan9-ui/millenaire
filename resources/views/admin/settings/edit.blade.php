{{--
    |--------------------------------------------------------------------------
    | admin/settings/edit.blade.php — Interface Paramètres Admin
    |--------------------------------------------------------------------------
    | Phase 3 — Section 4.1 — Contrôle total du contenu de la page d'accueil
    | L'admin peut modifier INTÉGRALEMENT le contenu de la page publique
    --}}

@extends('layouts.app')

@section('title', __('admin.settings'))

@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,var(--primary),var(--primary-light))">
            <i data-lucide="settings"></i>
        </div>
        <div>
            <h1 class="page-title">{{ __('admin.platform_settings') }}</h1>
            <p class="page-subtitle text-muted">{{ __('admin.settings_subtitle') }}</p>
        </div>
    </div>
</div>

{{-- Success / Error Flash --}}
@if(session('success'))
<div class="alert alert-success d-flex align-items-center gap-2 mb-4">
    <i data-lucide="check-circle" style="width:18px"></i>
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="settings-form">
    @csrf
    @method('PUT')

    {{-- ─── Tabs Navigation ─────────────────────────────────────────────── --}}
    <div class="settings-tabs mb-4" x-data="{ tab: 'identity' }">
        <div class="tab-nav d-flex gap-2 flex-wrap mb-4">
            @foreach([
                ['id' => 'identity',     'icon' => 'building-2',    'label_fr' => 'Identité',        'label_en' => 'Identity'],
                ['id' => 'hero',         'icon' => 'image',          'label_fr' => 'Hero Section',    'label_en' => 'Hero Section'],
                ['id' => 'proviseur',    'icon' => 'user-tie',       'label_fr' => 'Proviseur',       'label_en' => 'Director'],
                ['id' => 'about',        'icon' => 'info',           'label_fr' => 'À Propos',        'label_en' => 'About'],
                ['id' => 'testimonials', 'icon' => 'message-square', 'label_fr' => 'Témoignages',     'label_en' => 'Testimonials'],
                ['id' => 'contact',      'icon' => 'phone',          'label_fr' => 'Contact',         'label_en' => 'Contact'],
                ['id' => 'academic',     'icon' => 'graduation-cap', 'label_fr' => 'Académique',      'label_en' => 'Academic'],
                ['id' => 'notifications','icon' => 'bell',           'label_fr' => 'Notifications',   'label_en' => 'Notifications'],
            ] as $t)
            <button type="button"
                class="btn tab-btn"
                :class="tab === '{{ $t['id'] }}' ? 'btn-primary' : 'btn-light'"
                @click="tab = '{{ $t['id'] }}'"
            >
                <i data-lucide="{{ $t['icon'] }}" style="width:16px;height:16px"></i>
                {{ app()->getLocale() === 'fr' ? $t['label_fr'] : $t['label_en'] }}
            </button>
            @endforeach
        </div>

        {{-- ════════════ TAB: IDENTITÉ PLATEFORME ════════════ --}}
        <div x-show="tab === 'identity'" class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i data-lucide="building-2" style="width:18px" class="me-2"></i>
                    {{ app()->getLocale() === 'fr' ? 'Identité de la Plateforme' : 'Platform Identity' }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row gy-4">

                    {{-- Nom plateforme --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Nom de la Plateforme' : 'Platform Name' }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="platform_name" class="form-control"
                               value="{{ old('platform_name', $settings->platform_name ?? 'Millénaire Connect') }}"
                               placeholder="Millénaire Connect">
                    </div>

                    {{-- Slogan --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Slogan' : 'Tagline' }}
                        </label>
                        <input type="text" name="slogan" class="form-control"
                               value="{{ old('slogan', $settings->slogan ?? '') }}"
                               placeholder="{{ app()->getLocale() === 'fr' ? 'Excellence académique...' : 'Academic excellence...' }}">
                    </div>

                    {{-- Logo --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Logo</label>
                        @if($settings->logo_path)
                        <div class="mb-2">
                            <img src="{{ asset($settings->logo_path) }}" alt="Logo actuel" height="60"
                                 class="rounded border p-1">
                            <small class="d-block text-muted mt-1">{{ app()->getLocale() === 'fr' ? 'Logo actuel' : 'Current logo' }}</small>
                        </div>
                        @endif
                        <input type="file" name="logo" class="form-control" accept="image/*">
                        <small class="text-muted">{{ app()->getLocale() === 'fr' ? 'PNG/JPG recommandé. Max 2MB.' : 'PNG/JPG recommended. Max 2MB.' }}</small>
                    </div>

                    {{-- Favicon --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Favicon</label>
                        <input type="file" name="favicon" class="form-control" accept="image/x-icon,image/png">
                        <small class="text-muted">{{ app()->getLocale() === 'fr' ? 'Format .ico ou PNG 32x32' : '.ico or PNG 32x32 format' }}</small>
                    </div>

                    {{-- Couleur primaire --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Couleur Principale' : 'Primary Color' }}
                        </label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="primary_color" class="form-control form-control-color"
                                   value="{{ old('primary_color', $settings->primary_color ?? '#0d9488') }}"
                                   style="width:60px;height:40px">
                            <input type="text" name="primary_color_text" class="form-control"
                                   value="{{ old('primary_color', $settings->primary_color ?? '#0d9488') }}"
                                   placeholder="#0d9488">
                        </div>
                    </div>

                    {{-- Couleur secondaire --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Couleur Secondaire' : 'Secondary Color' }}
                        </label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="secondary_color" class="form-control form-control-color"
                                   value="{{ old('secondary_color', $settings->secondary_color ?? '#0f766e') }}"
                                   style="width:60px;height:40px">
                            <input type="text" name="secondary_color_text" class="form-control"
                                   value="{{ old('secondary_color', $settings->secondary_color ?? '#0f766e') }}"
                                   placeholder="#0f766e">
                        </div>
                    </div>

                    {{-- Années d'existence --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? "Années d'Existence" : 'Years of Existence' }}
                        </label>
                        <input type="number" name="years_existence" class="form-control" min="1" max="200"
                               value="{{ old('years_existence', $settings->years_existence ?? 10) }}">
                    </div>

                </div>
            </div>
        </div>

        {{-- ════════════ TAB: HERO SECTION ════════════ --}}
        <div x-show="tab === 'hero'" class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i data-lucide="image" style="width:18px" class="me-2"></i>
                    Hero Section
                </h5>
            </div>
            <div class="card-body">
                <div class="row gy-4">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Titre Principal (FR)' : 'Main Title (FR)' }}
                        </label>
                        <input type="text" name="hero_title" class="form-control"
                               value="{{ old('hero_title', $settings->hero_title ?? '') }}"
                               placeholder="{{ app()->getLocale() === 'fr' ? "L'Excellence Académique au Bout des Doigts" : 'Academic Excellence at Your Fingertips' }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Texte Bouton CTA' : 'CTA Button Text' }}
                        </label>
                        <input type="text" name="hero_cta_text" class="form-control"
                               value="{{ old('hero_cta_text', $settings->hero_cta_text ?? '') }}"
                               placeholder="Espace Connexion">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Sous-titre / Description' : 'Subtitle / Description' }}
                        </label>
                        <textarea name="hero_subtitle" class="form-control" rows="3"
                                  placeholder="{{ app()->getLocale() === 'fr' ? 'Description de l\'établissement...' : 'School description...' }}">{{ old('hero_subtitle', $settings->hero_subtitle ?? '') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Image Hero' : 'Hero Image' }}
                        </label>
                        @if($settings->hero_image ?? null)
                        <div class="mb-2">
                            <img src="{{ asset($settings->hero_image) }}" alt="Hero" height="100" class="rounded">
                        </div>
                        @endif
                        <input type="file" name="hero_image" class="form-control" accept="image/*">
                        <small class="text-muted">{{ app()->getLocale() === 'fr' ? 'Recommandé: 1200x800px' : 'Recommended: 1200x800px' }}</small>
                    </div>

                    {{-- Carrousel --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Images du Carrousel' : 'Carousel Images' }}
                        </label>
                        @if(!empty($settings->carousel_images))
                        <div class="carousel-previews d-flex gap-2 flex-wrap mb-2" id="carousel-previews">
                            @foreach($settings->carousel_images as $i => $img)
                            <div class="carousel-item-preview position-relative" data-index="{{ $i }}">
                                <img src="{{ asset($img) }}" height="80" class="rounded border" alt="">
                                <button type="button"
                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0"
                                    style="width:20px;height:20px;line-height:1;border-radius:50%"
                                    onclick="removeCarousel({{ $i }})">×</button>
                                <input type="hidden" name="carousel_keep[]" value="{{ $img }}">
                            </div>
                            @endforeach
                        </div>
                        @endif
                        <input type="file" name="carousel_images[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">{{ app()->getLocale() === 'fr' ? 'Sélectionner plusieurs images. Glisser-déposer pour réordonner.' : 'Select multiple images. Drag to reorder.' }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════ TAB: PROVISEUR ════════════ --}}
        <div x-show="tab === 'proviseur'" class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i data-lucide="user-tie" style="width:18px" class="me-2"></i>
                    {{ app()->getLocale() === 'fr' ? 'Profil du Proviseur / Directeur' : 'Director Profile' }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row gy-4">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Nom Complet' : 'Full Name' }}
                        </label>
                        <input type="text" name="proviseur_name" class="form-control"
                               value="{{ old('proviseur_name', $settings->proviseur_name ?? '') }}"
                               placeholder="M. Jean Dupont">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Titre du Poste' : 'Job Title' }}
                        </label>
                        <input type="text" name="proviseur_title" class="form-control"
                               value="{{ old('proviseur_title', $settings->proviseur_title ?? '') }}"
                               placeholder="{{ app()->getLocale() === 'fr' ? 'Directeur de l\'Établissement' : 'School Director' }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Photo du Proviseur' : 'Director Photo' }}
                        </label>
                        @if($settings->proviseur_photo ?? null)
                        <div class="mb-2">
                            <img src="{{ asset($settings->proviseur_photo) }}" alt="Photo Proviseur"
                                 style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #0d9488">
                        </div>
                        @endif
                        <input type="file" name="proviseur_photo" class="form-control" accept="image/*">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Signature Numérique' : 'Digital Signature' }}
                        </label>
                        <small class="text-muted d-block mb-1">{{ app()->getLocale() === 'fr' ? '(Utilisée sur les bulletins PDF)' : '(Used on PDF report cards)' }}</small>
                        @if($settings->signature_image ?? null)
                        <div class="mb-2">
                            <img src="{{ asset($settings->signature_image) }}" alt="Signature" height="60" class="rounded border p-1">
                        </div>
                        @endif
                        <input type="file" name="signature_image" class="form-control" accept="image/*">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Message / Bio' : 'Message / Bio' }}
                        </label>
                        <textarea name="proviseur_bio" class="form-control" rows="5">{{ old('proviseur_bio', $settings->proviseur_bio ?? '') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        {{-- ════════════ TAB: À PROPOS ════════════ --}}
        <div x-show="tab === 'about'" class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i data-lucide="info" style="width:18px" class="me-2"></i>
                    {{ app()->getLocale() === 'fr' ? 'Section À Propos' : 'About Section' }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row gy-4">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Titre Section À Propos' : 'About Section Title' }}
                        </label>
                        <input type="text" name="about_title" class="form-control"
                               value="{{ old('about_title', $settings->about_title ?? '') }}"
                               placeholder="À Propos de Nous">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Image Section À Propos' : 'About Section Image' }}
                        </label>
                        <input type="file" name="about_image" class="form-control" accept="image/*">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Description Générale' : 'General Description' }}
                        </label>
                        <textarea name="about_description" class="form-control" rows="6">{{ old('about_description', $settings->about_description ?? '') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        {{-- ════════════ TAB: TÉMOIGNAGES ════════════ --}}
        <div x-show="tab === 'testimonials'" class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">
                    <i data-lucide="message-square" style="width:18px" class="me-2"></i>
                    {{ app()->getLocale() === 'fr' ? 'Gestion des Témoignages' : 'Testimonials Management' }}
                </h5>
                <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm">
                    <i data-lucide="plus" style="width:14px"></i>
                    {{ app()->getLocale() === 'fr' ? 'Ajouter' : 'Add' }}
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ app()->getLocale() === 'fr' ? 'Nom' : 'Name' }}</th>
                                <th>{{ app()->getLocale() === 'fr' ? 'Rôle' : 'Role' }}</th>
                                <th>{{ app()->getLocale() === 'fr' ? 'Extrait' : 'Excerpt' }}</th>
                                <th>{{ app()->getLocale() === 'fr' ? 'Statut' : 'Status' }}</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($testimonials ?? [] as $testimonial)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($testimonial->photo)
                                            <img src="{{ asset('storage/'.$testimonial->photo) }}" width="32" height="32" class="rounded-circle">
                                        @else
                                            <div style="width:32px;height:32px;border-radius:50%;background:#0d9488;color:white;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700">
                                                {{ strtoupper(substr($testimonial->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        {{ $testimonial->name }}
                                    </div>
                                </td>
                                <td>{{ $testimonial->role }}</td>
                                <td>{{ Str::limit($testimonial->content, 60) }}</td>
                                <td>
                                    <span class="badge {{ $testimonial->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $testimonial->is_active ? ($isFr ? 'Actif' : 'Active') : ($isFr ? 'Inactif' : 'Inactive') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="btn btn-sm btn-light">
                                        <i data-lucide="edit-2" style="width:14px"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i data-lucide="trash-2" style="width:14px"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    {{ app()->getLocale() === 'fr' ? 'Aucun témoignage. Cliquez sur "Ajouter" pour créer le premier.' : 'No testimonials. Click "Add" to create the first one.' }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════════════ TAB: CONTACT ════════════ --}}
        <div x-show="tab === 'contact'" class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i data-lucide="phone" style="width:18px" class="me-2"></i>
                    {{ app()->getLocale() === 'fr' ? 'Informations de Contact' : 'Contact Information' }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row gy-4">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ app()->getLocale() === 'fr' ? 'Téléphone' : 'Phone' }}</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $settings->phone ?? '') }}"
                               placeholder="+237 6XX XXX XXX">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $settings->email ?? '') }}"
                               placeholder="contact@millenaire.cm">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">{{ app()->getLocale() === 'fr' ? 'Adresse' : 'Address' }}</label>
                        <input type="text" name="address" class="form-control"
                               value="{{ old('address', $settings->address ?? '') }}"
                               placeholder="{{ app()->getLocale() === 'fr' ? 'Douala, Cameroun' : 'Douala, Cameroon' }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Facebook</label>
                        <input type="url" name="social_facebook" class="form-control"
                               value="{{ old('social_facebook', $settings->social_facebook ?? '') }}"
                               placeholder="https://facebook.com/...">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Twitter / X</label>
                        <input type="url" name="social_twitter" class="form-control"
                               value="{{ old('social_twitter', $settings->social_twitter ?? '') }}"
                               placeholder="https://twitter.com/...">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Google Maps Embed URL</label>
                        <input type="url" name="google_maps_url" class="form-control"
                               value="{{ old('google_maps_url', $settings->google_maps_url ?? '') }}"
                               placeholder="https://maps.google.com/embed?...">
                    </div>

                </div>
            </div>
        </div>

        {{-- ════════════ TAB: ACADÉMIQUE ════════════ --}}
        <div x-show="tab === 'academic'" class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i data-lucide="graduation-cap" style="width:18px" class="me-2"></i>
                    {{ app()->getLocale() === 'fr' ? 'Configuration Académique' : 'Academic Configuration' }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row gy-4">

                    {{-- Barème d'appréciations --}}
                    <div class="col-12">
                        <h6 class="fw-semibold mb-3">{{ app()->getLocale() === 'fr' ? 'Barème des Appréciations (Francophone /20)' : 'Grade Scale (Francophone /20)' }}</h6>
                        <div class="row gy-2">
                            @foreach([
                                ['min' => 0,  'max' => 9,  'label' => 'Insuffisant',  'color' => '#ef4444'],
                                ['min' => 10, 'max' => 12, 'label' => 'Assez Bien',   'color' => '#f59e0b'],
                                ['min' => 13, 'max' => 15, 'label' => 'Bien',          'color' => '#3b82f6'],
                                ['min' => 16, 'max' => 18, 'label' => 'Très Bien',     'color' => '#10b981'],
                                ['min' => 19, 'max' => 20, 'label' => 'Excellent',     'color' => '#8b5cf6'],
                            ] as $i => $grade)
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:{{ $grade['color'] }}15">
                                    <span class="badge" style="background:{{ $grade['color'] }}">{{ $grade['min'] }}–{{ $grade['max'] }}</span>
                                    <input type="text" name="grade_labels[{{ $i }}]" class="form-control form-control-sm"
                                           value="{{ old('grade_labels.' . $i, $settings->{'grade_label_' . $i} ?? $grade['label']) }}">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Système Anglophone --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Système de Notation Anglophone' : 'Anglophone Grading System' }}
                        </label>
                        <select name="anglophone_grading" class="form-select">
                            <option value="letter" {{ ($settings->anglophone_grading ?? 'letter') === 'letter' ? 'selected' : '' }}>A–F (Letter Grades)</option>
                            <option value="percentage" {{ ($settings->anglophone_grading ?? '') === 'percentage' ? 'selected' : '' }}>Percentage %</option>
                        </select>
                    </div>

                    {{-- Séquences / Trimestres --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            {{ app()->getLocale() === 'fr' ? 'Nombre de Séquences par Trimestre' : 'Number of Sequences per Term' }}
                        </label>
                        <select name="sequences_per_term" class="form-select">
                            <option value="2" {{ ($settings->sequences_per_term ?? 2) == 2 ? 'selected' : '' }}>2 séquences</option>
                            <option value="3" {{ ($settings->sequences_per_term ?? 2) == 3 ? 'selected' : '' }}>3 séquences</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        {{-- ════════════ TAB: NOTIFICATIONS ════════════ --}}
        <div x-show="tab === 'notifications'" class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i data-lucide="bell" style="width:18px" class="me-2"></i>
                    {{ app()->getLocale() === 'fr' ? 'Paramètres Notifications' : 'Notification Settings' }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row gy-4">

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="notify_absence_parent" id="notifAbsence"
                                   {{ $settings->notify_absence_parent ?? true ? 'checked' : '' }}>
                            <label class="form-check-label" for="notifAbsence">
                                {{ app()->getLocale() === 'fr' ? 'Notifier parent lors d\'une absence' : 'Notify parent on absence' }}
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="notify_new_bulletin" id="notifBulletin"
                                   {{ $settings->notify_new_bulletin ?? true ? 'checked' : '' }}>
                            <label class="form-check-label" for="notifBulletin">
                                {{ app()->getLocale() === 'fr' ? 'Notifier lors de la publication d\'un bulletin' : 'Notify on bulletin publication' }}
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="notify_payment_success" id="notifPayment"
                                   {{ $settings->notify_payment_success ?? true ? 'checked' : '' }}>
                            <label class="form-check-label" for="notifPayment">
                                {{ app()->getLocale() === 'fr' ? 'Notifier lors d\'un paiement confirmé' : 'Notify on confirmed payment' }}
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="email_notifications" id="notifEmail"
                                   {{ $settings->email_notifications ?? false ? 'checked' : '' }}>
                            <label class="form-check-label" for="notifEmail">
                                {{ app()->getLocale() === 'fr' ? 'Envoyer emails de notification' : 'Send notification emails' }}
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>{{-- end x-data tabs --}}

    {{-- ─── Save Button ─────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-end gap-3 mt-4">
        <a href="{{ url()->previous() }}" class="btn btn-light px-4">
            {{ app()->getLocale() === 'fr' ? 'Annuler' : 'Cancel' }}
        </a>
        <button type="submit" class="btn btn-primary px-5">
            <i data-lucide="save" style="width:16px" class="me-2"></i>
            {{ app()->getLocale() === 'fr' ? 'Enregistrer les Modifications' : 'Save Changes' }}
        </button>
    </div>

</form>

@endsection

@push('scripts')
<script>
// Color picker sync
document.querySelectorAll('input[type="color"]').forEach(picker => {
    const textInput = picker.nextElementSibling;
    if (textInput) {
        picker.addEventListener('input', () => textInput.value = picker.value);
        textInput.addEventListener('input', () => picker.value = textInput.value);
    }
});

// Remove carousel image
function removeCarousel(index) {
    const item = document.querySelector(`[data-index="${index}"]`);
    if (item) item.remove();
}

// Broadcast settings update via Reverb (if connected)
document.getElementById('settings-form').addEventListener('submit', function () {
    // After form save, the server will broadcast SettingsUpdated event
    console.log('Settings form submitted');
});
</script>
@endpush

