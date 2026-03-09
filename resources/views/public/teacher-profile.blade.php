{{-- public/teacher-profile.blade.php --}}
@extends('layouts.public')
@section('title', ($teacher->user->display_name ?? $teacher->user->name) . ' — ' . config('app.name'))
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<section style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));padding:4rem 0 2rem;color:#fff">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-auto">
                @if($teacher->avatar_url)
                <img src="{{ $teacher->avatar_url }}" alt="{{ $teacher->user->name }}"
                     style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,.3)">
                @else
                <div style="width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.2);font-size:2.5rem;font-weight:900;color:#fff;display:flex;align-items:center;justify-content:center;border:4px solid rgba(255,255,255,.3)">
                    {{ strtoupper(substr($teacher->user->name, 0, 1)) }}
                </div>
                @endif
            </div>
            <div>
                <h1 style="font-size:1.8rem;font-weight:800;margin:0 0 .4rem">
                    {{ $teacher->user->display_name ?? $teacher->user->name }}
                </h1>
                @if($teacher->qualification)
                <div style="opacity:.85;margin-bottom:.4rem">{{ $teacher->qualification }}</div>
                @endif
                @if($teacher->is_prof_principal && $teacher->headClass)
                <span style="background:rgba(255,255,255,.2);border-radius:20px;padding:.25rem .8rem;font-size:.82rem;font-weight:700">
                    ⭐ {{ $isFr ? 'Prof. Principal' : 'Head Teacher' }} — {{ $teacher->headClass->name }}
                </span>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                {{-- Bio --}}
                @php $bio = $isFr ? $teacher->bio_fr : ($teacher->bio_en ?? $teacher->bio_fr); @endphp
                @if($bio)
                <div class="mb-5">
                    <h3 style="font-size:1.2rem;font-weight:700;margin-bottom:1rem">
                        {{ $isFr ? 'À propos' : 'About' }}
                    </h3>
                    <p style="line-height:1.8;color:var(--text-secondary)">{{ $bio }}</p>
                </div>
                @endif

                {{-- Subjects --}}
                <div class="mb-5">
                    <h3 style="font-size:1.2rem;font-weight:700;margin-bottom:1rem">
                        {{ $isFr ? 'Matières enseignées' : 'Subjects taught' }}
                    </h3>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($teacher->subjects as $sub)
                        <span class="badge" style="background:var(--primary-bg);color:var(--primary);font-size:.85rem;padding:.45rem 1rem;border-radius:20px;font-weight:600">
                            {{ $sub->name }}
                        </span>
                        @endforeach
                    </div>
                </div>

                {{-- Classes --}}
                @if($teacher->classes->isNotEmpty())
                <div>
                    <h3 style="font-size:1.2rem;font-weight:700;margin-bottom:1rem">
                        {{ $isFr ? 'Classes' : 'Classes' }}
                    </h3>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($teacher->classes as $class)
                        <span class="badge bg-secondary" style="font-size:.82rem;padding:.4rem .9rem">
                            {{ $class->name }}
                            <span style="opacity:.7;font-size:.72rem">
                                ({{ $class->section === 'anglophone' ? '🇬🇧' : '🇫🇷' }})
                            </span>
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card" style="border-radius:var(--radius-lg)">
                    <div class="card-body text-center py-4">
                        <div style="font-size:2rem;margin-bottom:.5rem">📅</div>
                        <h5 class="fw-bold mb-2">{{ $isFr ? 'Prendre rendez-vous' : 'Book a meeting' }}</h5>
                        <p style="font-size:.83rem;color:var(--text-muted)">
                            {{ $isFr
                                ? 'Connectez-vous pour réserver un créneau de consultation.'
                                : 'Log in to book a consultation slot.' }}
                        </p>
                        <a href="{{ route('login') }}" class="btn btn-primary w-100">
                            {{ $isFr ? 'Se connecter' : 'Login' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
