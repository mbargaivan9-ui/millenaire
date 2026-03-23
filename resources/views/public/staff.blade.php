{{--
    |--------------------------------------------------------------------------
    | staff.blade.php — Corps Administratif Public
    |--------------------------------------------------------------------------
    | Phase 2 — Section 3.8 Corps Administratif Page
    --}}

@extends('layouts.public')

@php
  $pageTitle = $pageTitle ?? __('public.staff_title_detailed');
@endphp

@section('title', $pageTitle)
@section('body_class', 'staff-page')

@section('content')

@endphp

<div class="page-title" style="background:linear-gradient(135deg,#0d9488,#0f766e);padding:3rem 0;text-align:center;color:#fff;">
    <div class="container">
        <h1 style="font-family:'Raleway',sans-serif;font-weight:800;font-size:2.2rem;margin-bottom:.5rem">
            {{ __('public.staff_title_detailed') }}
        </h1>
        <p style="opacity:.85">{{ __('public.staff_subtitle_detailed') }}</p>
    </div>
</div>

<section class="section" style="padding:80px 0;">
    <div class="container">

        <div class="row gy-4">
            @forelse($adminRoles ?? [] as $i => $role)
            @if($role->user)
            <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($i % 4) * 50 }}">
                <div class="instructor-card">
                    <div class="instructor-image">
                        @if($role->user->avatar_url)
                            <img src="{{ asset($role->user->avatar_url) }}" class="img-fluid"
                                 alt="{{ $role->user->display_name ?? $role->user->name }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center"
                                 style="height:220px;background:linear-gradient(135deg,#f0fdfa,#e6f9f7)">
                                <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#0f766e,#0d9488);display:flex;align-items:center;justify-content:center;">
                                    <span style="font-size:2.2rem;color:white;font-weight:700">
                                        {{ strtoupper(substr($role->user->name ?? 'A', 0, 1)) }}
                                    </span>
                                </div>
                            </div>
                        @endif
                        <div class="overlay-content">
                            <div class="course-count">
                                <i class="bi bi-person-badge me-1"></i>
                                {{ $role->role_name ?? 'Admin' }}
                            </div>
                        </div>
                    </div>
                    <div class="instructor-info">
                        <h5>{{ $role->user->display_name ?? $role->user->name }}</h5>
                        <p class="specialty">{{ $role->role_name ?? __('public.staff_role_admin') }}</p>
                        @if($role->user->email && ($role->show_email ?? false))
                        <p style="font-size:.8rem;color:#64748b;word-break:break-all">
                            <i class="bi bi-envelope me-1" style="color:#0d9488"></i>
                            {{ $role->user->email }}
                        </p>
                        @endif
                        @php
                        $roleColors = [
                            'censeur'    => '#3b82f6',
                            'intendant'  => '#f59e0b',
                            'secretaire' => '#10b981',
                            'surveillant'=> '#8b5cf6',
                        ];
                        $color = $roleColors[strtolower($role->role_name ?? '')] ?? '#0d9488';
                        @endphp
                        <div class="mt-2">
                            <span style="background:{{ $color }};color:white;padding:.3rem .8rem;border-radius:20px;font-size:.75rem;font-weight:600">
                                {{ $role->role_name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-person-badge" style="font-size:4rem;color:#0d9488;opacity:.3"></i>
                <p class="mt-3 text-muted">{{ __('public.staff_info_unavailable') }}</p>
            </div>
            @endforelse
        </div>

    </div>
</section>

@endsection
