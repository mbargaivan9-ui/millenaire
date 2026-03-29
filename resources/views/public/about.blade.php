{{--
    |--------------------------------------------------------------------------
    | about.blade.php — Page À Propos Publique (100% FRANÇAIS EN DUR)
    |--------------------------------------------------------------------------
    | Collège Millénaire Bilingue — Douala, Cameroun
    --}}

@extends('layouts.public')

@section('title', 'À Propos — Millénaire Connect')
@section('body_class', 'about-page')

@section('content')

@php
    $settings = $settings ?? App\Models\EstablishmentSetting::getInstance();
@endphp

{{-- Page Title --}}
<div class="page-title" style="background:linear-gradient(135deg,#0d9488,#0f766e);padding:3rem 0;text-align:center;color:#fff;">
    <div class="container">
        <h1 style="font-family:'Raleway',sans-serif;font-weight:800;font-size:2.2rem;margin-bottom:.5rem">
            À Propos de Nous
        </h1>
        <p style="opacity:.85">Millénaire Connect — En savoir plus sur notre établissement</p>
    </div>
</div>

{{-- ─── About Section ─────────────────────────────────────────────────────── --}}
<section id="about" class="about section" style="padding:80px 0;">
    <div class="container">
        <div class="row align-items-center gy-4">

            <div class="col-lg-6" data-aos="fade-up">
                <h2 style="font-family:'Raleway',sans-serif;font-size:2rem;font-weight:800;color:#223a58;margin-bottom:1rem">
                    Notre Mission
                </h2>
                <p style="color:#475569;line-height:1.8;margin-bottom:1.5rem">
                    Le Collège Millénaire Bilingue offre une éducation de qualité supérieure, combinant l'excellence académique avec le développement personnel et l'innovation technologique. Nous sommes dédiés à préparer les élèves à réussir dans un monde global, en mettant l'accent sur le bilinguisme, les valeurs humaines et l'utilisation responsable de la technologie.
                </p>

                {{-- Stats --}}
                <div class="row gy-3">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#f0fdfa;">
                            <div style="width:48px;height:48px;border-radius:12px;background:white;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.08)">
                                <i class="bi bi-mortarboard" style="font-size:1.3rem;color:#0d9488"></i>
                            </div>
                            <div>
                                <div style="font-size:1.5rem;font-weight:800;color:#0d9488;line-height:1">
                                    600+
                                </div>
                                <div style="font-size:.8rem;color:#64748b;font-weight:600">
                                    Élèves
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#f0fdfa;">
                            <div style="width:48px;height:48px;border-radius:12px;background:white;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.08)">
                                <i class="bi bi-person-workspace" style="font-size:1.3rem;color:#3b82f6"></i>
                            </div>
                            <div>
                                <div style="font-size:1.5rem;font-weight:800;color:#3b82f6;line-height:1">
                                    45+
                                </div>
                                <div style="font-size:.8rem;color:#64748b;font-weight:600">
                                    Enseignants
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#f0fdfa;">
                            <div style="width:48px;height:48px;border-radius:12px;background:white;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.08)">
                                <i class="bi bi-award" style="font-size:1.3rem;color:#f59e0b"></i>
                            </div>
                            <div>
                                <div style="font-size:1.5rem;font-weight:800;color:#f59e0b;line-height:1">
                                    20+
                                </div>
                                <div style="font-size:.8rem;color:#64748b;font-weight:600">
                                    Ans d'excellence
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#f0fdfa;">
                            <div style="width:48px;height:48px;border-radius:12px;background:white;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.08)">
                                <i class="bi bi-building" style="font-size:1.3rem;color:#10b981"></i>
                            </div>
                            <div>
                                <div style="font-size:1.5rem;font-weight:800;color:#10b981;line-height:1">
                                    18
                                </div>
                                <div style="font-size:.8rem;color:#64748b;font-weight:600">
                                    Classes
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                @if($settings->about_image ?? null)
                    <img src="{{ asset($settings->about_image) }}" alt="Collège Millénaire" class="img-fluid rounded-4 shadow-lg">
                @else
                    <div class="rounded-4 overflow-hidden" style="background:linear-gradient(135deg,#f0fdfa,#ccfbf1);height:350px;display:flex;align-items:center;justify-content:center;">
                        <div class="text-center p-4">
                            <i class="bi bi-building-fill" style="font-size:5rem;color:#0d9488;opacity:.4"></i>
                            <p class="mt-3" style="color:#0d9488;font-weight:600">Collège Millénaire Bilingue</p>
                            <p style="color:#64748b;font-size:.9rem">Excellence Académique & Innovation</p>
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
        <div style="text-align:center;margin-bottom:3rem;">
            <h2 style="font-size:1.8rem;font-weight:900;color:#0f172a;margin-bottom:.5rem">Message du Proviseur</h2>
            <p style="color:#64748b;font-size:1rem;">Une parole du leadership de notre établissement</p>
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
                                {{ $settings->proviseur_title ?? 'Proviseur' }}
                            </p>
                        </div>

                        <div class="col-md-8">
                            <div style="position:relative;padding-left:1.5rem;border-left:4px solid #0d9488;">
                                <i class="bi bi-quote" style="font-size:3rem;color:#0d9488;opacity:.2;position:absolute;top:-10px;left:10px"></i>
                                <p style="font-size:1rem;color:#475569;line-height:1.8;font-style:italic">
                                    {{ $settings->proviseur_bio ?? "Bienvenue au Collège Millénaire Bilingue. Ensemble, nous cultivons l'excellence, la rigueur et l'humanité. Chaque élève est une future excellence qui mérite le meilleur." }}
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
        <div style="text-align:center;margin-bottom:3rem;">
            <h2 style="font-size:1.8rem;font-weight:900;color:#0f172a;margin-bottom:.5rem">Notre Système Bilingue</h2>
            <p style="color:#64748b;font-size:1rem;">Options pédagogiques en français et en anglais</p>
        </div>
        <div class="row gy-4">
            <div class="col-md-6" data-aos="fade-up">
                <div class="p-4 rounded-4 h-100" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span style="font-size:2.5rem">🇫🇷</span>
                        <h3 style="margin:0;color:white;font-weight:800">Section Française</h3>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">6ème</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">5ème</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">4ème</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">3ème</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">Seconde</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">Première</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">Terminale</span>
                    </div>
                    <p class="mt-3 mb-0" style="opacity:.9;font-size:.9rem">
                        Cursus conforme aux standards du système éducatif français avec excellence académique et valeurs humanistes.
                    </p>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="p-4 rounded-4 h-100" style="background:linear-gradient(135deg,#0d9488,#0f766e);color:white;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span style="font-size:2.5rem">🇬🇧</span>
                        <h3 style="margin:0;color:white;font-weight:800">Section Anglaise</h3>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">Form 1</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">Form 2</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">Form 3</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">Form 4</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">Form 5</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">Lower 6th</span>
                        <span style="background:rgba(255,255,255,.2);padding:.3rem .8rem;border-radius:20px;font-size:.82rem;font-weight:600">Upper 6th</span>
                    </div>
                    <p class="mt-3 mb-0" style="opacity:.9;font-size:.9rem">
                        Système britannique avec pédagogie active, critique et créative pour une formation complète et équilibrée.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
