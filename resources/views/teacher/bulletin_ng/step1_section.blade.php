@extends('layouts.app')

@section('title', 'Système de Bulletins — Choisir la Section')

@section('content')
<div class="bng-page">

    {{-- Page Header --}}
    <div class="bng-page-header">
        <div class="bng-page-header-inner">
            <div class="bng-page-icon">📋</div>
            <div>
                <h1 class="bng-page-title">Système de Bulletins</h1>
                <p class="bng-page-subtitle">Le Millenaire — Nouvelle Génération</p>
            </div>
        </div>
        <a href="{{ route('teacher.bulletin_ng.index') }}" class="bng-btn bng-btn-secondary">
            ← Mes Sessions
        </a>
    </div>

    {{-- Carte principale --}}
    <div class="bng-card">
        <div class="bng-card-header">
            <div class="bng-card-header-icon">🌐</div>
            <div>
                <div class="bng-card-title">Étape 1 — Choix de la Section</div>
                <div class="bng-card-subtitle">Sélectionnez la section linguistique pour définir la langue du bulletin</div>
            </div>
        </div>

        <div class="bng-card-body">
            <div class="bng-section-intro">
                <p>
                    Le Millenaire est un établissement bilingue. La section choisie déterminera
                    la langue de l'interface et du bulletin scolaire généré.
                </p>
            </div>

            <div class="bng-section-choices">
                {{-- Francophone --}}
                <a href="{{ route('teacher.bulletin_ng.step2') }}?langue=FR" class="bng-section-card bng-section-fr">
                    <div class="bng-section-flag">🇫🇷</div>
                    <div class="bng-section-name">Section Francophone</div>
                    <div class="bng-section-desc">Bulletin rédigé en Français</div>
                    <div class="bng-section-features">
                        <span>✓ En-tête bilingue obligatoire</span>
                        <span>✓ Colonnes en Français</span>
                        <span>✓ Appréciations FR</span>
                    </div>
                    <div class="bng-section-cta">Choisir →</div>
                </a>

                {{-- Anglophone --}}
                <a href="{{ route('teacher.bulletin_ng.step2') }}?langue=EN" class="bng-section-card bng-section-en">
                    <div class="bng-section-flag">🇬🇧</div>
                    <div class="bng-section-name">Anglophone Section</div>
                    <div class="bng-section-desc">Report Card written in English</div>
                    <div class="bng-section-features">
                        <span>✓ Mandatory bilingual header</span>
                        <span>✓ Columns in English</span>
                        <span>✓ EN Appreciation labels</span>
                    </div>
                    <div class="bng-section-cta">Choose →</div>
                </a>
            </div>

            {{-- Note header --}}
            <div class="bng-info-box">
                <strong>📌 Note :</strong>
                Quel que soit le choix, l'en-tête du bulletin maintiendra toujours le texte
                <em>« République du Cameroun — Paix Travail Patrie / Republic of Cameroon — Peace Work Fatherland »</em>
                dans les deux langues, conformément aux normes officielles.
            </div>
        </div>
    </div>

</div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bulletin_ng.css') }}">
@endpush
