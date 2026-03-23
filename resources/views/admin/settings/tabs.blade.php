{{--
    |--------------------------------------------------------------------------
    | admin/settings/tabs.blade.php — Interface Paramètres Admin
    |--------------------------------------------------------------------------
    | Vue alternative sans Livewire pour afficher les paramètres
    | Utilise des onglets avec JavaScript simple
    --}}

@extends('layouts.app')

@section('title', __('admin.settings'))

@section('content')
<style>
    .settings-nav-item {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text-secondary);
        border: none;
        background: none;
        text-align: left;
        width: 100%;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        gap: 0.66rem;
    }
    .settings-nav-item:hover {
        background: var(--primary-hover);
        color: var(--primary);
    }
    .settings-nav-item.active {
        background: var(--primary-bg);
        color: var(--primary);
        font-weight: 700;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    [data-lucide] {
        width: 16px;
        height: 16px;
        vertical-align: middle;
    }
</style>

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,var(--primary),var(--primary-light))">
            <i data-lucide="settings"></i>
        </div>
        <div>
            <h1 class="page-title">{{ app()->getLocale() === 'fr' ? 'Paramètres de l\'Établissement' : 'Establishment Settings' }}</h1>
            <p class="page-subtitle text-muted">{{ app()->getLocale() === 'fr' ? 'Configuration générale de la plateforme' : 'General platform configuration' }}</p>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i data-lucide="check-circle" style="width:18px" class="me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i data-lucide="alert-circle" style="width:18px" class="me-2"></i>
    {{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <button class="settings-nav-item active" onclick="switchTab(event, 'identity')">
                <i data-lucide="building-2"></i>
                <span>{{ app()->getLocale() === 'fr' ? 'Identité' : 'Identity' }}</span>
            </button>
            <button class="settings-nav-item" onclick="switchTab(event, 'logo')">
                <i data-lucide="image"></i>
                <span>{{ app()->getLocale() === 'fr' ? 'Logo & Favicon' : 'Logo & Favicon' }}</span>
            </button>
            <button class="settings-nav-item" onclick="switchTab(event, 'carousel')">
                <i data-lucide="pictures"></i>
                <span>{{ app()->getLocale() === 'fr' ? 'Carrousel' : 'Carousel' }}</span>
            </button>
            <button class="settings-nav-item" onclick="switchTab(event, 'about')">
                <i data-lucide="info"></i>
                <span>{{ app()->getLocale() === 'fr' ? 'À Propos' : 'About' }}</span>
            </button>
            <button class="settings-nav-item" onclick="switchTab(event, 'contact')">
                <i data-lucide="phone"></i>
                <span>{{ app()->getLocale() === 'fr' ? 'Contact' : 'Contact' }}</span>
            </button>
            <button class="settings-nav-item" onclick="switchTab(event, 'academic')">
                <i data-lucide="graduation-cap"></i>
                <span>{{ app()->getLocale() === 'fr' ? 'Académique' : 'Academic' }}</span>
            </button>
            <button class="settings-nav-item" onclick="switchTab(event, 'notifications')">
                <i data-lucide="bell"></i>
                <span>{{ app()->getLocale() === 'fr' ? 'Notifications' : 'Notifications' }}</span>
            </button>
        </div>
    </div>

    {{-- FORMULAIRE UNIQUE GLOBAL POUR TOUS LES ONGLETS --}}
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="globalSettingsForm">
        @csrf
        @method('PUT')
    <div class="col-md-9">
        {{-- IDENTITY TAB --}}
        <div id="identity" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ app()->getLocale() === 'fr' ? 'Identité de la Plateforme' : 'Platform Identity' }}</h5>
                </div>
                <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Nom de la Plateforme' : 'Platform Name' }}</label>
                                <input type="text" name="platform_name" class="form-control @error('platform_name') is-invalid @enderror" 
                                    value="{{ old('platform_name', $settings->platform_name ?? 'Millénaire Connect') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Slogan' : 'Tagline' }}</label>
                                <input type="text" name="slogan" class="form-control" 
                                    value="{{ old('slogan', $settings->slogan ?? '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Couleur Principale' : 'Primary Color' }}</label>
                                <div class="d-flex gap-2">
                                    <input type="color" name="primary_color" class="form-control form-control-color" style="width:60px"
                                        value="{{ old('primary_color', $settings->primary_color ?? '#0d9488') }}">
                                    <input type="text" class="form-control" readonly 
                                        value="{{ old('primary_color', $settings->primary_color ?? '#0d9488') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Couleur Secondaire' : 'Secondary Color' }}</label>
                                <div class="d-flex gap-2">
                                    <input type="color" name="secondary_color" class="form-control form-control-color" style="width:60px"
                                        value="{{ old('secondary_color', $settings->secondary_color ?? '#0f766e') }}">
                                    <input type="text" class="form-control" readonly 
                                        value="{{ old('secondary_color', $settings->secondary_color ?? '#0f766e') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ app()->getLocale() === 'fr' ? "Années d'Existence" : 'Years Established' }}</label>
                                <input type="number" name="years_existence" class="form-control" min="1" max="200"
                                    value="{{ old('years_existence', $settings->years_existence ?? 10) }}">
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save" style="width:16px" class="me-1"></i>{{ app()->getLocale() === 'fr' ? 'Enregistrer' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- LOGO & FAVICON TAB --}}
        <div id="logo" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i data-lucide="image" style="width:18px" class="me-2"></i>
                        {{ app()->getLocale() === 'fr' ? 'Logo et Favicon' : 'Logo & Favicon' }}
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Messages d'erreur --}}
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6><i data-lucide="alert-circle" style="width:16px" class="me-2"></i>{{ app()->getLocale() === 'fr' ? 'Erreurs' : 'Errors' }}</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="logoForm">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            {{-- Logo de l'établissement --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    {{ app()->getLocale() === 'fr' ? 'Logo de l\'Établissement' : 'Establishment Logo' }}
                                </label>
                                @if($settings->logo_path && file_exists(public_path($settings->logo_path)))
                                <div class="mb-3">
                                    <img src="{{ asset($settings->logo_path) }}?v={{ time() }}" alt="Logo actuel" height="80" 
                                         class="rounded border p-2" style="background:#f5f5f5;max-width:200px">
                                    <small class="d-block text-muted mt-2">{{ app()->getLocale() === 'fr' ? 'Logo actuel' : 'Current logo' }}</small>
                                </div>
                                @else
                                <div class="alert alert-info py-2 px-3 mb-3">
                                    <small>{{ app()->getLocale() === 'fr' ? 'Aucun logo configuré' : 'No logo configured' }}</small>
                                </div>
                                @endif
                                
                                <div class="input-group mb-3">
                                    <input type="file" name="logo" class="form-control" accept="image/*" id="logoInput" data-type="logo">
                                    <label class="input-group-text"><i data-lucide="upload" style="width:16px"></i></label>
                                </div>
                                <small class="text-muted d-block mb-3">
                                    {{ app()->getLocale() === 'fr' 
                                        ? 'JPG, PNG, GIF, WebP, SVG • Max 2MB' 
                                        : 'JPG, PNG, GIF, WebP, SVG • Max 2MB' }}
                                </small>
                                <div id="logoPreview" class="mb-3" style="min-height:40px"></div>
                            </div>

                            {{-- Favicon --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Favicon</label>
                                @if($settings->favicon_path && file_exists(public_path($settings->favicon_path)))
                                <div class="mb-3">
                                    <img src="{{ asset($settings->favicon_path) }}?v={{ time() }}" alt="Favicon actuel" height="32" 
                                         class="rounded border p-1" style="background:#f5f5f5">
                                    <small class="d-block text-muted mt-2">{{ app()->getLocale() === 'fr' ? 'Favicon actuel' : 'Current favicon' }}</small>
                                </div>
                                @else
                                <div class="alert alert-info py-2 px-3 mb-3">
                                    <small>{{ app()->getLocale() === 'fr' ? 'Aucun favicon configuré' : 'No favicon configured' }}</small>
                                </div>
                                @endif
                                
                                <div class="input-group mb-3">
                                    <input type="file" name="favicon" class="form-control" accept="image/x-icon,image/png,.ico" id="faviconInput" data-type="favicon">
                                    <label class="input-group-text"><i data-lucide="upload" style="width:16px"></i></label>
                                </div>
                                <small class="text-muted d-block mb-3">
                                    {{ app()->getLocale() === 'fr' 
                                        ? '.ico ou PNG 32x32px • Max 512KB' 
                                        : '.ico or PNG 32x32px • Max 512KB' }}
                                </small>
                                <div id="faviconPreview" class="mb-3" style="min-height:40px"></div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" id="logoSubmitBtn">
                                <i data-lucide="save" style="width:16px" class="me-1"></i>{{ app()->getLocale() === 'fr' ? 'Enregistrer' : 'Save' }}
                            </button>
                            <div id="logoSpinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display:none">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- CAROUSEL TAB --}}
        <div id="carousel" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i data-lucide="pictures" style="width:18px" class="me-2"></i>
                        {{ app()->getLocale() === 'fr' ? 'Images du Carrousel d\'Accueil' : 'Homepage Carousel Images' }}
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Messages --}}
                    @if($errors->any() && $errors->first('carousel_images'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first('carousel_images') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif
                    @if(session('carousel_success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('carousel_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="carouselForm">
                        @csrf
                        @method('PUT')

                        {{-- Instructions --}}
                        <div class="alert alert-info mb-4">
                            {{ app()->getLocale() === 'fr' 
                                ? '📸 Vous pouvez télécharger jusqu\'à 3 images Pour le carrousel. Tous les formats d\'image sont acceptés (JPG, PNG, GIF, WebP, SVG). Max 5MB par image.'
                                : '📸 You can upload up to 3 images for the carousel. All image formats are supported (JPG, PNG, GIF, WebP, SVG). Max 5MB per image.' }}
                        </div>

                        <div class="row gy-4">
                            {{-- Slide 1 --}}
                            <div class="col-lg-4 col-md-6">
                                <div class="card border shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">{{ app()->getLocale() === 'fr' ? 'Slide 1' : 'Slide 1' }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="carousel-slide-wrapper mb-3" style="position:relative;border-radius:8px;overflow:hidden;background:#f5f5f5;aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;border:2px dashed #ddd">
                                            @if(!empty($settings->carousel_images) && isset($settings->carousel_images[0]) && file_exists(public_path($settings->carousel_images[0])))
                                                <img src="{{ asset($settings->carousel_images[0]) }}?v={{ time() }}" alt="Slide 1" style="width:100%;height:100%;object-fit:cover;" id="carouselImg1">
                                                <button type="button" class="btn btn-sm btn-danger position-absolute top-2 end-2" onclick="removeCarouselSlide(1)" style="z-index:10">
                                                    <i data-lucide="trash2" style="width:14px"></i> {{ app()->getLocale() === 'fr' ? 'Supprimer' : 'Delete' }}
                                                </button>
                                            @else
                                                <div style="text-align:center;color:#999">
                                                    <i data-lucide="image" style="width:32px;height:32px;margin-bottom:8px;display:block;opacity:0.5"></i>
                                                    <small>{{ app()->getLocale() === 'fr' ? 'Aucune image' : 'No image' }}</small>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="input-group mb-2">
                                            <input type="file" name="carousel_replace[0]" class="form-control carousel-input" accept="image/*" data-slide="1" id="carouselFile1">
                                            <label class="input-group-text"><i data-lucide="upload" style="width:16px"></i></label>
                                        </div>
                                        @if(!empty($settings->carousel_images) && isset($settings->carousel_images[0]))
                                        <input type="hidden" name="carousel_keep[0]" value="{{ $settings->carousel_images[0] }}" id="carouselKeep1">
                                        @endif
                                        <div id="carouselPreview1" class="small text-muted"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Slide 2 --}}
                            <div class="col-lg-4 col-md-6">
                                <div class="card border shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">{{ app()->getLocale() === 'fr' ? 'Slide 2' : 'Slide 2' }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="carousel-slide-wrapper mb-3" style="position:relative;border-radius:8px;overflow:hidden;background:#f5f5f5;aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;border:2px dashed #ddd">
                                            @if(!empty($settings->carousel_images) && isset($settings->carousel_images[1]) && file_exists(public_path($settings->carousel_images[1])))
                                                <img src="{{ asset($settings->carousel_images[1]) }}?v={{ time() }}" alt="Slide 2" style="width:100%;height:100%;object-fit:cover;" id="carouselImg2">
                                                <button type="button" class="btn btn-sm btn-danger position-absolute top-2 end-2" onclick="removeCarouselSlide(2)" style="z-index:10">
                                                    <i data-lucide="trash2" style="width:14px"></i> {{ app()->getLocale() === 'fr' ? 'Supprimer' : 'Delete' }}
                                                </button>
                                            @else
                                                <div style="text-align:center;color:#999">
                                                    <i data-lucide="image" style="width:32px;height:32px;margin-bottom:8px;display:block;opacity:0.5"></i>
                                                    <small>{{ app()->getLocale() === 'fr' ? 'Aucune image' : 'No image' }}</small>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="input-group mb-2">
                                            <input type="file" name="carousel_replace[1]" class="form-control carousel-input" accept="image/*" data-slide="2" id="carouselFile2">
                                            <label class="input-group-text"><i data-lucide="upload" style="width:16px"></i></label>
                                        </div>
                                        @if(!empty($settings->carousel_images) && isset($settings->carousel_images[1]))
                                        <input type="hidden" name="carousel_keep[1]" value="{{ $settings->carousel_images[1] }}" id="carouselKeep2">
                                        @endif
                                        <div id="carouselPreview2" class="small text-muted"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Slide 3 --}}
                            <div class="col-lg-4 col-md-6">
                                <div class="card border shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">{{ app()->getLocale() === 'fr' ? 'Slide 3' : 'Slide 3' }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="carousel-slide-wrapper mb-3" style="position:relative;border-radius:8px;overflow:hidden;background:#f5f5f5;aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;border:2px dashed #ddd">
                                            @if(!empty($settings->carousel_images) && isset($settings->carousel_images[2]) && file_exists(public_path($settings->carousel_images[2])))
                                                <img src="{{ asset($settings->carousel_images[2]) }}?v={{ time() }}" alt="Slide 3" style="width:100%;height:100%;object-fit:cover;" id="carouselImg3">
                                                <button type="button" class="btn btn-sm btn-danger position-absolute top-2 end-2" onclick="removeCarouselSlide(3)" style="z-index:10">
                                                    <i data-lucide="trash2" style="width:14px"></i> {{ app()->getLocale() === 'fr' ? 'Supprimer' : 'Delete' }}
                                                </button>
                                            @else
                                                <div style="text-align:center;color:#999">
                                                    <i data-lucide="image" style="width:32px;height:32px;margin-bottom:8px;display:block;opacity:0.5"></i>
                                                    <small>{{ app()->getLocale() === 'fr' ? 'Aucune image' : 'No image' }}</small>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="input-group mb-2">
                                            <input type="file" name="carousel_replace[2]" class="form-control carousel-input" accept="image/*" data-slide="3" id="carouselFile3">
                                            <label class="input-group-text"><i data-lucide="upload" style="width:16px"></i></label>
                                        </div>
                                        @if(!empty($settings->carousel_images) && isset($settings->carousel_images[2]))
                                        <input type="hidden" name="carousel_keep[2]" value="{{ $settings->carousel_images[2] }}" id="carouselKeep3">
                                        @endif
                                        <div id="carouselPreview3" class="small text-muted"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <small class="text-muted d-block mt-3 mb-4">
                            {{ app()->getLocale() === 'fr' 
                                ? 'JPG, PNG, GIF, WebP, SVG • Format responsive en 16:9' 
                                : 'JPG, PNG, GIF, WebP, SVG • Responsive 16:9 format' }}
                        </small>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" id="carouselSubmitBtn">
                                <i data-lucide="save" style="width:16px" class="me-1"></i>{{ app()->getLocale() === 'fr' ? 'Enregistrer' : 'Save' }}
                            </button>
                            <div id="carouselSpinner" class="spinner-border spinner-border-sm ms-2" role="status" style="display:none">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ABOUT TAB --}}
        <div id="about" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i data-lucide="info" style="width:18px" class="me-2"></i>
                        {{ app()->getLocale() === 'fr' ? 'Section À Propos' : 'About Section' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="aboutForm">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            {{-- SECTION HERO/ABOUT --}}
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i data-lucide="info" style="width:16px" class="me-2"></i>
                                    {{ app()->getLocale() === 'fr' 
                                        ? 'Configuration de la section "À Propos" affichée sur la page d\'accueil'
                                        : 'Configuration of the "About" section displayed on the homepage' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    {{ app()->getLocale() === 'fr' ? 'Titre de la Section' : 'Section Title' }}
                                </label>
                                <input type="text" name="about_title" class="form-control"
                                    value="{{ old('about_title', $settings->about_title ?? '') }}"
                                    placeholder="{{ app()->getLocale() === 'fr' ? 'À Propos de Nous' : 'About Us' }}">
                                <small class="text-muted d-block mt-1">
                                    {{ app()->getLocale() === 'fr' ? 'Ex: À Propos de Millénaire Connect' : 'Ex: About Millénaire Connect' }}
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    {{ app()->getLocale() === 'fr' ? 'Image de Background' : 'Background Image' }}
                                </label>
                                @if($settings->about_image ?? null)
                                <div class="mb-2">
                                    <img src="{{ asset($settings->about_image) }}" alt="About image" height="80" 
                                         class="rounded border" style="object-fit:cover;max-width:200px">
                                    <small class="d-block text-muted mt-1">{{ app()->getLocale() === 'fr' ? 'Image actuelle' : 'Current image' }}</small>
                                </div>
                                @endif
                                <input type="file" name="about_image" class="form-control" accept="image/*" id="aboutImageInput">
                                <small class="text-muted d-block mt-2">
                                    {{ app()->getLocale() === 'fr' 
                                        ? 'JPG, PNG, WebP • Max 5MB • Recommandé: 1200x500px' 
                                        : 'JPG, PNG, WebP • Max 5MB • Recommended: 1200x500px' }}
                                </small>
                                <div id="aboutImagePreview" style="margin-top:10px"></div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    {{ app()->getLocale() === 'fr' ? 'Description de l\'Établissement' : 'Establishment Description' }}
                                </label>
                                <textarea name="about_description" class="form-control" rows="5" 
                                    placeholder="{{ app()->getLocale() === 'fr' 
                                        ? 'Texte descriptif de votre établissement...' 
                                        : 'Your establishment description...' }}">{{ old('about_description', $settings->about_description ?? '') }}</textarea>
                                <small class="text-muted d-block mt-2">{{ app()->getLocale() === 'fr' ? 'Jusqu\'à 3000 caractères' : 'Up to 3000 characters' }}</small>
                            </div>

                            {{-- SECTION PROVISEUR --}}
                            <div class="col-12">
                                <hr>
                                <h6 class="text-primary mt-4 mb-3">
                                    <i data-lucide="user" style="width:16px" class="me-2"></i>
                                    {{ app()->getLocale() === 'fr' ? 'Information du Proviseur' : 'Principal Information' }}
                                </h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    {{ app()->getLocale() === 'fr' ? 'Photo du Proviseur' : 'Principal Photo' }}
                                </label>
                                @if($settings->proviseur_photo ?? null)
                                <div class="mb-2">
                                    <img src="{{ asset($settings->proviseur_photo) }}" alt="Proviseur photo" height="120" 
                                         class="rounded border" style="object-fit:cover;width:120px;height:120px">
                                    <small class="d-block text-muted mt-2">{{ app()->getLocale() === 'fr' ? 'Photo actuelle' : 'Current photo' }}</small>
                                </div>
                                @else
                                <div class="alert alert-warning py-2 px-3 mb-3">
                                    <small>{{ app()->getLocale() === 'fr' ? 'Aucune photo configurée' : 'No photo configured' }}</small>
                                </div>
                                @endif
                                <input type="file" name="proviseur_photo" class="form-control" accept="image/*" id="proviseurPhotoInput">
                                <small class="text-muted d-block mt-2">
                                    {{ app()->getLocale() === 'fr' 
                                        ? 'JPG, PNG, WebP • Max 2MB • Recommandé: carrée 300x300px' 
                                        : 'JPG, PNG, WebP • Max 2MB • Recommended: 300x300px square' }}
                                </small>
                                <div id="proviseurPhotoPreview" style="margin-top:10px"></div>
                            </div>

                            <div class="col-md-8">
                                <div class="row gy-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            {{ app()->getLocale() === 'fr' ? 'Nom Complet du Proviseur' : 'Principal Full Name' }}
                                        </label>
                                        <input type="text" name="proviseur_name" class="form-control"
                                            value="{{ old('proviseur_name', $settings->proviseur_name ?? '') }}"
                                            placeholder="{{ app()->getLocale() === 'fr' ? 'Ex: Monsieur Jean Dupont' : 'Ex: Mr. John Smith' }}">
                                        <small class="text-muted d-block mt-1">{{ app()->getLocale() === 'fr' ? 'Nom affiché sur la section à propos' : 'Name displayed on about section' }}</small>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            {{ app()->getLocale() === 'fr' ? 'Titre/Position' : 'Title/Position' }}
                                        </label>
                                        <input type="text" name="proviseur_title" class="form-control"
                                            value="{{ old('proviseur_title', $settings->proviseur_title ?? '') }}"
                                            placeholder="{{ app()->getLocale() === 'fr' ? 'Ex: Directeur Pédagogique' : 'Ex: Principal/Headmaster' }}">
                                        <small class="text-muted d-block mt-1">{{ app()->getLocale() === 'fr' ? 'Position ou titre du proviseur' : 'Position or title of the principal' }}</small>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            {{ app()->getLocale() === 'fr' ? 'Message/Citation du Proviseur' : 'Message/Quote from Principal' }}
                                        </label>
                                        <textarea name="proviseur_bio" class="form-control" rows="4" 
                                            placeholder="{{ app()->getLocale() === 'fr' 
                                                ? 'Message personnel ou citation du proviseur...' 
                                                : 'Personal message or quote from the principal...' }}">{{ old('proviseur_bio', $settings->proviseur_bio ?? '') }}</textarea>
                                        <small class="text-muted d-block mt-2">{{ app()->getLocale() === 'fr' ? 'Jusqu\'à 2000 caractères' : 'Up to 2000 characters' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save" style="width:16px" class="me-1"></i>{{ app()->getLocale() === 'fr' ? 'Enregistrer' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- CONTACT TAB --}
        <div id="contact" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ app()->getLocale() === 'fr' ? 'Informations de Contact' : 'Contact Information' }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="contactForm">
                        @csrf
                        @method('PUT')
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Téléphone' : 'Phone' }}</label>
                                <input type="text" name="phone" class="form-control" 
                                    value="{{ old('phone', $settings->phone ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" 
                                    value="{{ old('email', $settings->email ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Adresse' : 'Address' }}</label>
                                <input type="text" name="address" class="form-control" 
                                    value="{{ old('address', $settings->address ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Facebook</label>
                                <input type="url" name="social_facebook" class="form-control" 
                                    value="{{ old('social_facebook', $settings->social_facebook ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Twitter / X</label>
                                <input type="url" name="social_twitter" class="form-control" 
                                    value="{{ old('social_twitter', $settings->social_twitter ?? '') }}">
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save" style="width:16px" class="me-1"></i>{{ app()->getLocale() === 'fr' ? 'Enregistrer' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ACADEMIC TAB --}}
        <div id="academic" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ app()->getLocale() === 'fr' ? 'Configuration Académique' : 'Academic Configuration' }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="academicForm">
                        @csrf
                        @method('PUT')
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Système de Notation' : 'Grading System' }}</label>
                                <select name="anglophone_grading" class="form-select">
                                    <option value="letter" {{ (old('anglophone_grading', $settings->anglophone_grading ?? 'letter') === 'letter') ? 'selected' : '' }}>A–F (Letter Grades)</option>
                                    <option value="percentage" {{ (old('anglophone_grading', $settings->anglophone_grading ?? 'letter') === 'percentage') ? 'selected' : '' }}>Percentage %</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Séquences par Trimestre' : 'Sequences per Term' }}</label>
                                <select name="sequences_per_term" class="form-select">
                                    <option value="2" {{ (old('sequences_per_term', $settings->sequences_per_term ?? 2) == 2) ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ (old('sequences_per_term', $settings->sequences_per_term ?? 2) == 3) ? 'selected' : '' }}>3</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save" style="width:16px" class="me-1"></i>{{ app()->getLocale() === 'fr' ? 'Enregistrer' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- NOTIFICATIONS TAB --}}
        <div id="notifications" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ app()->getLocale() === 'fr' ? 'Paramètres Notifications' : 'Notification Settings' }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="notificationsForm">
                        @csrf
                        @method('PUT')
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notify_absence_parent" id="notifyAbsence"
                                        {{ (old('notify_absence_parent', $settings->notify_absence_parent ?? true)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notifyAbsence">
                                        {{ app()->getLocale() === 'fr' ? 'Notifier absence' : 'Notify on absence' }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notify_new_bulletin" id="notifyBulletin"
                                        {{ (old('notify_new_bulletin', $settings->notify_new_bulletin ?? true)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notifyBulletin">
                                        {{ app()->getLocale() === 'fr' ? 'Notifier bulletin' : 'Notify bulletin' }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notify_payment_success" id="notifyPayment"
                                        {{ (old('notify_payment_success', $settings->notify_payment_success ?? true)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notifyPayment">
                                        {{ app()->getLocale() === 'fr' ? 'Notifier paiement' : 'Notify payment' }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="email_notifications" id="notifyEmail"
                                        {{ (old('email_notifications', $settings->email_notifications ?? false)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notifyEmail">
                                        {{ app()->getLocale() === 'fr' ? 'Emails notifications' : 'Email notifications' }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save" style="width:16px" class="me-1"></i>{{ app()->getLocale() === 'fr' ? 'Enregistrer' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(e, tabName) {
    e.preventDefault();
    
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Deactivate all buttons
    document.querySelectorAll('.settings-nav-item').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName).classList.add('active');
    
    // Activate button
    e.target.closest('.settings-nav-item').classList.add('active');
}

// Sync color inputs
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('change', function() {
        const textInput = this.closest('.d-flex').querySelector('input[type="text"]');
        if (textInput) textInput.value = this.value;
    });
});

// Logo preview
document.getElementById('logoInput')?.addEventListener('change', function(e) {
    const preview = document.getElementById('logoPreview');
    preview.innerHTML = '';
    if (this.files.length > 0) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.style = 'height:80px;border-radius:6px;border:1px solid #ddd;padding:4px;background:#f5f5f5';
            preview.appendChild(img);
        };
        reader.readAsDataURL(this.files[0]);
    }
});

// Favicon preview
document.getElementById('faviconInput')?.addEventListener('change', function(e) {
    const preview = document.getElementById('faviconPreview');
    preview.innerHTML = '';
    if (this.files.length > 0) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.style = 'height:32px;border-radius:4px;border:1px solid #ddd;padding:2px;background:#f5f5f5';
            preview.appendChild(img);
        };
        reader.readAsDataURL(this.files[0]);
    }
});

// Carousel images preview - Slide 1
document.getElementById('carouselFile1')?.addEventListener('change', function(e) {
    const reader = new FileReader();
    const preview = document.getElementById('carouselPreview1');
    const carouselWrapper = document.querySelector('#carouselFile1')?.closest('.card-body')?.querySelector('.carousel-slide-wrapper');
    
    if (this.files.length > 0) {
        const file = this.files[0];
        reader.onload = function(event) {
            // Afficher la prévisualisation dans le wrapper
            if (carouselWrapper) {
                carouselWrapper.innerHTML = `<img src="${event.target.result}" alt="Preview" style="width:100%;height:100%;object-fit:cover;">`;
            }
            // Afficher le nom du fichier
            if (preview) {
                preview.innerHTML = `<small style="color:#0d9488;display:block"><i style="color:#0d9488">✓</i> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)</small>`;
            }
        };
        reader.readAsDataURL(file);
    }
});

// Carousel images preview - Slide 2
document.getElementById('carouselFile2')?.addEventListener('change', function(e) {
    const reader = new FileReader();
    const preview = document.getElementById('carouselPreview2');
    const carouselWrapper = document.querySelector('#carouselFile2')?.closest('.card-body')?.querySelector('.carousel-slide-wrapper');
    
    if (this.files.length > 0) {
        const file = this.files[0];
        reader.onload = function(event) {
            // Afficher la prévisualisation dans le wrapper
            if (carouselWrapper) {
                carouselWrapper.innerHTML = `<img src="${event.target.result}" alt="Preview" style="width:100%;height:100%;object-fit:cover;">`;
            }
            // Afficher le nom du fichier
            if (preview) {
                preview.innerHTML = `<small style="color:#0d9488;display:block"><i style="color:#0d9488">✓</i> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)</small>`;
            }
        };
        reader.readAsDataURL(file);
    }
});

// Carousel images preview - Slide 3
document.getElementById('carouselFile3')?.addEventListener('change', function(e) {
    const reader = new FileReader();
    const preview = document.getElementById('carouselPreview3');
    const carouselWrapper = document.querySelector('#carouselFile3')?.closest('.card-body')?.querySelector('.carousel-slide-wrapper');
    
    if (this.files.length > 0) {
        const file = this.files[0];
        reader.onload = function(event) {
            // Afficher la prévisualisation dans le wrapper
            if (carouselWrapper) {
                carouselWrapper.innerHTML = `<img src="${event.target.result}" alt="Preview" style="width:100%;height:100%;object-fit:cover;">`;
            }
            // Afficher le nom du fichier
            if (preview) {
                preview.innerHTML = `<small style="color:#0d9488;display:block"><i style="color:#0d9488">✓</i> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)</small>`;
            }
        };
        reader.readAsDataURL(file);
    }
});

// Function to remove carousel slide
function removeCarouselSlide(slideNum) {
    if (confirm('{{ app()->getLocale() === "fr" ? "Êtes-vous sûr de vouloir supprimer cette image ?" : "Are you sure you want to delete this image?" }}')) {
        const fileInput = document.getElementById(`carouselFile${slideNum}`);
        const keepInput = document.getElementById(`carouselKeep${slideNum}`);
        const carouselWrapper = document.querySelector(`#carouselFile${slideNum}`)?.closest('.card-body')?.querySelector('.carousel-slide-wrapper');
        const preview = document.getElementById(`carouselPreview${slideNum}`);
        
        // Clear the file input
        if (fileInput) fileInput.value = '';
        
        // Remove the keep input to mark for deletion
        if (keepInput) keepInput.remove();
        
        // Reset preview element
        if (preview) preview.innerHTML = '';
        
        // Reset the image container to empty state
        if (carouselWrapper) {
            carouselWrapper.innerHTML = `<div style="text-align:center;color:#999"><i data-lucide="image" style="width:32px;height:32px;margin-bottom:8px;display:block;opacity:0.5"></i><small>{{ app()->getLocale() === 'fr' ? 'Aucune image' : 'No image' }}</small></div>`;
        }
    }
}

// About image preview
document.getElementById('aboutImageInput')?.addEventListener('change', function(e) {
    const preview = document.getElementById('aboutImagePreview');
    preview.innerHTML = '';
    if (this.files.length > 0) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.style = 'height:100px;border-radius:6px;border:1px solid #ddd;object-fit:cover';
            preview.appendChild(img);
        };
        reader.readAsDataURL(this.files[0]);
    }
});

// Proviseur photo preview
document.getElementById('proviseurPhotoInput')?.addEventListener('change', function(e) {
    const preview = document.getElementById('proviseurPhotoPreview');
    preview.innerHTML = '';
    if (this.files.length > 0) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.style = 'width:120px;height:120px;border-radius:8px;border:2px solid #0d9488;object-fit:cover;display:block';
            preview.appendChild(img);
            const fileName = document.createElement('small');
            fileName.textContent = `✓ ${e.target.files[0].name}`;
            fileName.style = 'color:#0d9488;display:block;margin-top:8px';
            preview.appendChild(fileName);
        };
        reader.readAsDataURL(this.files[0]);
    }
});

// Remove carousel image (old function - kept for compatibility)
function removeCarousel(e, imagePath) {
    e.preventDefault();
    if (confirm('{{ app()->getLocale() === "fr" ? "Êtes-vous sûr de vouloir supprimer cette image ?" : "Are you sure you want to delete this image?" }}')) {
        const form = document.getElementById('carouselForm');
        // Find and remove the hidden input with this path
        const hiddenInputs = form.querySelectorAll('input[name="carousel_keep[]"]');
        hiddenInputs.forEach(input => {
            if (input.value === imagePath) {
                input.remove();
            }
        });
        // Remove the preview element
        const previewItem = form.querySelector(`[data-index]`);
        if (previewItem && previewItem.querySelector('input[value="' + imagePath + '"]')) {
            previewItem.closest('.carousel-item-preview').remove();
        } else {
            location.reload();
        }
    }
}

/**
 * ═══════════════════════════════════════════════════════════════════
 * FUSION DE FORMULAIRES - Combine tous les formulaires en UN seul
 * ═══════════════════════════════════════════════════════════════════
 */
document.addEventListener('submit', function(e) {
    const form = e.target;
    
    // Si c'est un formulaire de paramètres
    if (form.id === 'logoForm' || form.id === 'carouselForm' || form.id === 'globalSettingsForm') {
        e.preventDefault();
        console.log('📋 Fusion des formulaires - Onglet:', form.id);
        
        // Créer un FormData qui combine TOUS les formulaires
        const mergedFormData = new FormData();
        
        // ─── Copier token CSRF ──────────────────────────────────────
        const csrfToken = document.querySelector('input[name="_token"]');
        if (csrfToken) {
            mergedFormData.append('_token', csrfToken.value);
            mergedFormData.append('_method', 'PUT');
        }
        
        // Fonction helper pour copier les données d'un formulaire
        const copyFormData = (sourceForm) => {
            if (!sourceForm) return;
            const formData = new FormData(sourceForm);
            for (let [key, value] of formData.entries()) {
                if (key !== '_token' && key !== '_method') {
                    mergedFormData.append(key, value);
                }
            }
        };
        
        // ─── Copier les données de TOUS les formulaires ──────────────
        const globalForm = document.getElementById('globalSettingsForm');
        const logoForm = document.getElementById('logoForm');
        const carouselForm = document.getElementById('carouselForm');
        
        if (globalForm) {
            console.log('  ✓ Copie globalSettingsForm');
            copyFormData(globalForm);
        }
        if (logoForm) {
            console.log('  ✓ Copie logoForm');
            copyFormData(logoForm);
        }
        if (carouselForm) {
            console.log('  ✓ Copie carouselForm');
            copyFormData(carouselForm);
        }
        
        // Copier aussi les autres formulaires (About, Proviseur, etc.)
        document.querySelectorAll('form[id*="Form"], form[id*="form"]').forEach(f => {
            if (f.id !== 'globalSettingsForm' && f.id !== 'logoForm' && f.id !== 'carouselForm') {
                console.log('  ✓ Copie', f.id);
                copyFormData(f);
            }
        });
        
        // Afficher les données fusionnées (debug)
        console.log('📦 Données fusionnées:');
        for (let [key, value] of mergedFormData.entries()) {
            const displayValue = value instanceof File ? `File: ${value.name}` : value;
            console.log(`  - ${key}:`, displayValue);
        }
        
        // ─── Afficher un indicateur de chargement ────────────────────
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>' + 
                                (app.locale === 'fr' ? 'Enregistrement...' : 'Saving...');
        }
        
        // ─── Soumettre le formulaire fusionné ────────────────────────
        console.log('📤 Envoi au serveur...');
        
        fetch('{{ route("admin.settings.update") }}', {
            method: 'POST',
            body: mergedFormData
            // Ne pas ajouter de header Content-Type - le navigateur va le définir automatiquement
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            console.log('✅ Succès ! Rechargement...');
            // Afficher message de succès et recharger
            const message = '{{ app()->getLocale() === "fr" ? "Paramètres enregistrés avec succès !" : "Settings saved successfully!" }}';
            alert(message);
            
            // Invalider le cache localement
            if ('caches' in window) {
                caches.keys().then(names => {
                    names.forEach(name => caches.delete(name));
                });
            }
            
            // Recharger avec un paramètre de cache-busting
            location.href = location.href.split('?')[0] + '?t=' + Date.now();
        })
        .catch(error => {
            console.error('❌ Erreur:', error);
            alert('{{ app()->getLocale() === "fr" ? "Erreur lors de l\'enregistrement: " : "Error saving settings: " }}' + error.message);
            
            // Restaurer le bouton
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '{{ app()->getLocale() === "fr" ? "Enregistrer" : "Save" }}';
            }
        });
    }
});
</script>
@endsection
