@extends('layouts.app')

@section('title', 'Bulletin NG — Configuration')

@section('content')
<div class="bng-page">

    <div class="bng-page-header">
        <div class="bng-page-header-inner">
            <div class="bng-page-icon">⚙️</div>
            <div>
                <h1 class="bng-page-title">
                    {{ $langue === 'EN' ? 'Step 2 — Configuration' : 'Étape 2 — Configuration' }}
                </h1>
                <p class="bng-page-subtitle">
                    {{ $langue === 'EN' ? 'Report card settings' : 'Paramètres du bulletin scolaire' }}
                </p>
            </div>
        </div>
        <a href="{{ route('teacher.bulletin_ng.step1') }}" class="bng-btn bng-btn-secondary">← Retour</a>
    </div>

    {{-- Wizard Steps indicator --}}
    @php $currentStep = 2; @endphp
    @if(isset($config))
        @include('teacher.bulletin_ng.partials.wizard_header')
    @endif

    <div class="bng-card">
        <div class="bng-card-header">
            <div class="bng-card-header-icon">⚙️</div>
            <div>
                <div class="bng-card-title">
                    {{ $langue === 'EN' ? 'Report Card Configuration' : 'Configuration du Bulletin' }}
                </div>
                <div class="bng-card-subtitle">
                    {{ $langue === 'FR' ? '🇫🇷 Section Francophone' : '🇬🇧 Anglophone Section' }}
                </div>
            </div>
        </div>

        <div class="bng-card-body">

            {{-- En-tête par défaut --}}
            <div class="bng-header-preview">
                <div class="bng-header-preview-title">
                    {{ $langue === 'EN' ? '📋 Default Bulletin Header (mandatory)' : '📋 En-tête par défaut (obligatoire)' }}
                </div>
                <div class="bng-header-preview-content">
                    <div class="bng-header-col">
                        <strong>RÉPUBLIQUE DU CAMEROUN</strong><br>
                        Paix - Travail - Patrie<br>***********<br>
                        Ministère de l'Éducation de Base
                    </div>
                    <div class="bng-header-logo-placeholder">🏫<br><small>Votre Logo</small></div>
                    <div class="bng-header-col">
                        <strong>REPUBLIC OF CAMEROON</strong><br>
                        Peace - Work - Fatherland<br>***********<br>
                        Ministry of Basic Education
                    </div>
                </div>
            </div>

            <form action="{{ route('teacher.bulletin_ng.store-config') }}"
                  method="POST" enctype="multipart/form-data"
                  id="configForm">
                @csrf
                <input type="hidden" name="langue" value="{{ $langue }}">
                @if(isset($config) && $config)
                    <input type="hidden" name="config_id" value="{{ $config->id }}">
                @endif

                <div class="bng-form-grid">

                    {{-- Logo --}}
                    <div class="bng-form-field bng-full-width">
                        <label class="bng-label">
                            {{ $langue === 'EN' ? 'School Logo' : 'Logo de l\'Établissement' }}
                        </label>
                        <div class="bng-logo-upload" id="logoUploadZone">
                            @if(isset($config) && $config->logo_path)
                                <img src="{{ Storage::url($config->logo_path) }}" alt="Logo" class="bng-logo-preview" id="logoPreview">
                            @else
                                <div class="bng-logo-placeholder" id="logoPlaceholder">
                                    <span>🏫</span>
                                    <span>{{ $langue === 'EN' ? 'Click to upload logo' : 'Cliquer pour importer le logo' }}</span>
                                </div>
                            @endif
                            <input type="file" name="logo" id="logoInput" accept="image/*" class="bng-logo-input">
                        </div>
                    </div>

                    {{-- Nom établissement --}}
                    <div class="bng-form-field bng-full-width">
                        <label class="bng-label" for="school_name">
                            {{ $langue === 'EN' ? 'School Name *' : 'Nom de l\'Établissement *' }}
                        </label>
                        <input type="text" name="school_name" id="school_name"
                               class="bng-input @error('school_name') is-error @enderror"
                               value="{{ old('school_name', $config->school_name ?? 'ÉTABLISSEMENT LE MILLENAIRE') }}"
                               required>
                        @error('school_name') <span class="bng-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Délégations --}}
                    <div class="bng-form-field">
                        <label class="bng-label" for="delegation_fr">
                            {{ $langue === 'EN' ? 'Regional Delegation (FR)' : 'Délégation Régionale (FR)' }}
                        </label>
                        <input type="text" name="delegation_fr" id="delegation_fr"
                               class="bng-input"
                               value="{{ old('delegation_fr', $config->delegation_fr ?? '') }}"
                               placeholder="ex: Délégation Régionale du Littoral">
                    </div>
                    <div class="bng-form-field">
                        <label class="bng-label" for="delegation_en">
                            {{ $langue === 'EN' ? 'Regional Delegation (EN)' : 'Délégation Régionale (EN)' }}
                        </label>
                        <input type="text" name="delegation_en" id="delegation_en"
                               class="bng-input"
                               value="{{ old('delegation_en', $config->delegation_en ?? '') }}"
                               placeholder="ex: Littoral Regional Delegation">
                    </div>

                    {{-- Prof principal (auto-rempli) --}}
                    <div class="bng-form-field bng-full-width">
                        <label class="bng-label">
                            {{ $langue === 'EN' ? 'Class Teacher (Principal Teacher)' : 'Professeur Principal' }}
                        </label>
                        <input type="text" class="bng-input bng-input-readonly"
                               value="{{ auth()->user()->name }}" readonly>
                        <small class="bng-hint">
                            {{ $langue === 'EN' ? 'Automatically filled with your account name.' : 'Rempli automatiquement avec votre nom de compte.' }}
                        </small>
                    </div>

                    {{-- Classe --}}
                    <div class="bng-form-field">
                        <label class="bng-label" for="nom_classe">
                            {{ $langue === 'EN' ? 'Class *' : 'Classe *' }}
                        </label>
                        <input type="text" name="nom_classe" id="nom_classe"
                               class="bng-input @error('nom_classe') is-error @enderror"
                               value="{{ old('nom_classe', $config->nom_classe ?? '') }}"
                               placeholder="ex: 3ème A" required>
                        @error('nom_classe') <span class="bng-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Effectif --}}
                    <div class="bng-form-field">
                        <label class="bng-label" for="effectif">
                            {{ $langue === 'EN' ? 'Class Size (Students) *' : 'Effectif Élèves *' }}
                        </label>
                        <input type="number" name="effectif" id="effectif" min="1" max="200"
                               class="bng-input @error('effectif') is-error @enderror"
                               value="{{ old('effectif', $config->effectif ?? '') }}"
                               required>
                        @error('effectif') <span class="bng-error">{{ $message }}</span> @enderror
                    </div>

                    {{-- Trimestre --}}
                    <div class="bng-form-field">
                        <label class="bng-label" for="trimestre">
                            {{ $langue === 'EN' ? 'Term *' : 'Trimestre *' }}
                        </label>
                        <select name="trimestre" id="trimestre" class="bng-select" required>
                            <option value="1" {{ old('trimestre', $config->trimestre ?? 1) == 1 ? 'selected' : '' }}>
                                {{ $langue === 'EN' ? '1st Term' : '1er Trimestre' }}
                            </option>
                            <option value="2" {{ old('trimestre', $config->trimestre ?? '') == 2 ? 'selected' : '' }}>
                                {{ $langue === 'EN' ? '2nd Term' : '2ème Trimestre' }}
                            </option>
                            <option value="3" {{ old('trimestre', $config->trimestre ?? '') == 3 ? 'selected' : '' }}>
                                {{ $langue === 'EN' ? '3rd Term' : '3ème Trimestre' }}
                            </option>
                        </select>
                    </div>

                    {{-- Séquence --}}
                    <div class="bng-form-field">
                        <label class="bng-label" for="sequence">
                            {{ $langue === 'EN' ? 'Sequence *' : 'Séquence *' }}
                        </label>
                        <select name="sequence" id="sequence" class="bng-select" required>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}" {{ old('sequence', $config->sequence ?? 1) == $i ? 'selected' : '' }}>
                                    Séquence {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- Année académique --}}
                    <div class="bng-form-field">
                        <label class="bng-label" for="annee_academique">
                            {{ $langue === 'EN' ? 'Academic Year *' : 'Année Académique *' }}
                        </label>
                        <input type="text" name="annee_academique" id="annee_academique"
                               class="bng-input @error('annee_academique') is-error @enderror"
                               value="{{ old('annee_academique', $config->annee_academique ?? '2025-2026') }}"
                               placeholder="2025-2026" required>
                        @error('annee_academique') <span class="bng-error">{{ $message }}</span> @enderror
                    </div>

                </div>{{-- /bng-form-grid --}}

                <div class="bng-form-actions">
                    <a href="{{ route('teacher.bulletin_ng.step1') }}" class="bng-btn bng-btn-secondary">
                        ← {{ $langue === 'EN' ? 'Back' : 'Retour' }}
                    </a>
                    <button type="submit" class="bng-btn bng-btn-primary">
                        {{ $langue === 'EN' ? 'Save & Continue →' : 'Enregistrer & Continuer →' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bulletin_ng.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input   = document.getElementById('logoInput');
    const zone    = document.getElementById('logoUploadZone');
    const preview = document.getElementById('logoPreview');
    const holder  = document.getElementById('logoPlaceholder');
    const form    = document.getElementById('configForm');

    // Logo upload preview
    zone.addEventListener('click', () => input.click());

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'bng-logo-preview';
                img.id = 'logoPreview';
                zone.insertBefore(img, holder);
                if (holder) holder.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    });

    // Form submission via AJAX
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                // Redirect to step3 with the config_id
                window.location.href = `/teacher/bulletin-ng/${data.config_id}/step3`;
            } else {
                alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Erreur lors de l\'enregistrement: ' + error);
        }
    });
});
</script>
@endpush
