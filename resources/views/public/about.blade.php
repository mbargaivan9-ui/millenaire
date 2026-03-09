{{--
    |--------------------------------------------------------------------------
    | about.blade.php — Page À Propos Publique
    |--------------------------------------------------------------------------
    | Phase 2 — Section 3.8 About Page
    | Design Learner template + couleurs Millénaire Connect
    --}}

@extends('layouts.public')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'À Propos' : 'About Us');
@endphp

@section('title', $pageTitle)
@section('body_class', 'about-page')

@section('content')

@php
    $settings = $settings ?? App\Models\EstablishmentSetting::getInstance();
    $isFr     = app()->getLocale() === 'fr';
@endphp

{{-- Page Title --}}
<div class="page-title" style="background:linear-gradient(135deg,#0d9488,#0f766e);padding:3rem 0;text-align:center;color:#fff;">
    <div class="container">
        <h1 style="font-family:'Raleway',sans-serif;font-weight:800;font-size:2.2rem;margin-bottom:.5rem">
            {{ $isFr ? 'À Propos de Nous' : 'About Us' }}
        </h1>
        <p style="opacity:.85">{{ $settings->platform_name ?? 'Millénaire Connect' }} — Collège Millénaire Bilingue, Douala</p>
    </div>
</div>

{{-- ─── About Section ─────────────────────────────────────────────────────── --}}
<section id="about" class="about section" style="padding:80px 0;">
    <div class="container">
        <div class="row align-items-center gy-4">

            <div class="col-lg-6" data-aos="fade-up">
                <h2 style="font-family:'Raleway',sans-serif;font-size:2rem;font-weight:800;color:#223a58;margin-bottom:1rem">
                    {{ $settings->about_title ?? ($isFr ? 'Excellence Académique depuis ' . (date('Y') - ($settings->years_existence ?? 10)) : 'Academic Excellence since ' . (date('Y') - ($settings->years_existence ?? 10))) }}
                </h2>
                <p style="color:#475569;line-height:1.8;margin-bottom:1.5rem">
                    {{ $settings->about_description ?? ($isFr
                        ? 'Le Collège Millénaire Bilingue est un établissement d\'enseignement secondaire de référence à Douala, Cameroun, offrant une formation d\'excellence en français et en anglais. Millénaire Connect est notre plateforme numérique qui digitalise l\'intégralité de la vie scolaire pour mieux servir nos élèves, enseignants et familles.'
                        : 'Collège Millénaire Bilingue is a leading secondary school in Douala, Cameroon, offering excellent education in both French and English. Millénaire Connect is our digital platform that fully digitalizes school life to better serve our students, teachers and families.') }}
                </p>

                {{-- Stats --}}
                <div class="row gy-3">
                    @foreach([
                        ['number' => $stats['students'] ?? 500, 'label_fr' => 'Élèves Inscrits', 'label_en' => 'Students Enrolled', 'icon' => 'bi-mortarboard', 'color' => '#0d9488'],
                        ['number' => $stats['teachers'] ?? 40, 'label_fr' => 'Enseignants', 'label_en' => 'Teachers', 'icon' => 'bi-person-workspace', 'color' => '#3b82f6'],
                        ['number' => $settings->years_existence ?? 10, 'label_fr' => "Ans d'Existence", 'label_en' => 'Years of Excellence', 'icon' => 'bi-award', 'color' => '#f59e0b'],
                        ['number' => $stats['classes'] ?? 15, 'label_fr' => 'Classes', 'label_en' => 'Classes', 'icon' => 'bi-building', 'color' => '#10b981'],
                    ] as $stat)
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#f0fdfa;">
                            <div style="width:48px;height:48px;border-radius:12px;background:white;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.08)">
                                <i class="{{ $stat['icon'] }}" style="font-size:1.3rem;color:{{ $stat['color'] }}"></i>
                            </div>
                            <div>
                                <div style="font-size:1.5rem;font-weight:800;color:{{ $stat['color'] }};line-height:1">
                                    {{ $stat['number'] }}+
                                </div>
                                <div style="font-size:.8rem;color:#64748b;font-weight:600">
                                    {{ $isFr ? $stat['label_fr'] : $stat['label_en'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                @if($settings->about_image ?? null)
                    <img src="{{ asset($settings->about_image) }}" alt="{{ $isFr ? 'Notre École' : 'Our School' }}" class="img-fluid rounded-4 shadow-lg">
                @else
                    <div class="rounded-4 overflow-hidden" style="background:linear-gradient(135deg,#f0fdfa,#ccfbf1);height:350px;display:flex;align-items:center;justify-content:center;">
                        <div class="text-center p-4">
                            <i class="bi bi-building-fill" style="font-size:5rem;color:#0d9488;opacity:.4"></i>
                            <p class="mt-3" style="color:#0d9488;font-weight:600">{{ $settings->platform_name ?? 'Millénaire Connect' }}</p>
                            <p style="color:#64748b;font-size:.9rem">Collège Millénaire Bilingue</p>
                            <p style="color:#64748b;font-size:.9rem">Douala, Cameroun 🇨🇲</p>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</section>

{{-- ─── Section Proviseur ────────────────────────────────────────────────── --}}
@if($settings->proviseur_name ?? null)
<section id="proviseur" class="section light-background" style="padding:80px 0;background:#f0fdfa;">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>{{ $isFr ? 'Mot du Directeur' : 'Message from the Director' }}</h2>
            <p>{{ $isFr ? 'Un message personnel de la direction de l\'établissement' : 'A personal message from school leadership' }}</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                <div class="proviseur-card p-4 bg-white rounded-4 shadow-sm">
                    <div class="row align-items-center gy-4">
                        <div class="col-md-4 text-center">
                            @if($settings->proviseur_photo ?? null)
                                <img src="{{ asset($settings->proviseur_photo) }}"
                                     alt="{{ $settings->proviseur_name }}"
                                     class="rounded-circle shadow"
                                     style="width:160px;height:160px;object-fit:cover;border:4px solid #0d9488">
                            @else
                                <div style="width:160px;height:160px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#14b8a6);display:flex;align-items:center;justify-content:center;margin:0 auto;border:4px solid #0d9488;">
                                    <span style="font-size:3rem;color:white;font-weight:800">
                                        {{ strtoupper(substr($settings->proviseur_name, 0, 1)) }}
                                    </span>
                                </div>
                            @endif

                            <h4 class="mt-3 mb-1" style="color:#223a58;font-weight:700">{{ $settings->proviseur_name }}</h4>
                            <p style="color:#0d9488;font-size:.85rem;font-weight:600">
                                {{ $settings->proviseur_title ?? ($isFr ? 'Directeur de l\'Établissement' : 'School Director') }}
                            </p>
                        </div>

                        <div class="col-md-8">
                            <div style="position:relative;padding-left:1.5rem;border-left:4px solid #0d9488;">
                                <i class="bi bi-quote" style="font-size:3rem;color:#0d9488;opacity:.2;position:absolute;top:-10px;left:10px"></i>
                                <p style="font-size:1rem;color:#475569;line-height:1.8;font-style:italic">
                                    {{ $settings->proviseur_bio ?? ($isFr
                                        ? 'Notre mission est de former des élèves compétents, responsables et ouverts sur le monde. Millénaire Connect nous permet de maintenir un lien permanent entre l\'école et les familles, pour une meilleure réussite de chaque élève.'
                                        : 'Our mission is to train competent, responsible students who are open to the world. Millénaire Connect allows us to maintain a permanent link between school and families, for the success of each student.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ─── Bilingual System Section ─────────────────────────────────────────── --}}
<section class="section" style="padding:80px 0;">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>{{ $isFr ? 'Système Éducatif Bilingue' : 'Bilingual Education System' }}</h2>
            <p>{{ $isFr ? 'Nous préparons les élèves selon les deux systèmes éducatifs du Cameroun' : 'We prepare students according to both Cameroonian educational systems' }}</p>
        </div>
        <div class="row gy-4">
            <div class="col-md-6" data-aos="fade-up">
                <div class="p-4 rounded-4 h-100" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span style="font-size:2.5rem">🇫🇷</span>
                        <h3 style="margin:0;color:white;font-weight:800">{{ $isFr ? 'Sous-Système Francophone' : 'Francophone Subsystem' }}</h3>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['6ème','5ème','4ème','3ème','Seconde','Première','Terminale'] as $level)
                            <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">{{ $level }}</span>
                        @endforeach
                    </div>
                    <p class="mt-3 mb-0" style="opacity:.9;font-size:.9rem">
                        {{ $isFr ? 'Notation sur 20 — Diplôme BEPC et BAC camerounais' : 'Grades out of 20 — BEPC and Cameroonian BAC Diploma' }}
                    </p>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="p-4 rounded-4 h-100" style="background:linear-gradient(135deg,#0d9488,#0f766e);color:white;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span style="font-size:2.5rem">🇬🇧</span>
                        <h3 style="margin:0;color:white;font-weight:800">Anglophone Subsystem</h3>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(['Form 1','Form 2','Form 3','Form 4','Form 5','Lower 6th','Upper 6th'] as $level)
                            <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">{{ $level }}</span>
                        @endforeach
                    </div>
                    <p class="mt-3 mb-0" style="opacity:.9;font-size:.9rem">
                        Letter grades A–F — GCE O-Level & A-Level
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
