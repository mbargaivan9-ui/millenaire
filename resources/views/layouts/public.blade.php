<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Millénaire Connect') — Collège Millénaire Bilingue</title>
    <meta name="description" content="@yield('meta_description', 'Plateforme de digitalisation du Collège Millénaire Bilingue — Douala, Cameroun')">

    <!-- Favicons -->
    <link href="{{ asset('img/favicon.png') }}" rel="icon">
    <link href="{{ asset('img/apple-touch-icon.png') }}" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Raleway:wght@300;400;500;600;700;800&family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Vendor CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">

    <!-- Millenaire Public CSS -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">

    @stack('styles')
</head>
<body class="@yield('body_class', 'index-page')">

    {{-- ─── HEADER / NAV ─────────────────────────────────────── --}}
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">

            <a href="{{ route('home') }}" class="logo d-flex align-items-center me-auto">
                @php 
                    $settings = $globalSettings ?? App\Models\EstablishmentSetting::getInstance();
                    $logoUrl = \App\Helpers\SettingsHelper::logoUrl();
                @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $settings->platform_name ?? 'Millénaire Connect' }}" height="40" class="me-2">
                @endif
                <h1 class="sitename">{{ $settings->platform_name ?? 'Millénaire Connect' }}</h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                         Accueil
                    </a></li>
                    <li><a href="{{ route('public.about') }}" class="{{ request()->routeIs('public.about') ? 'active' : '' }}">
                         À Propos
                    </a></li>
                    <li><a href="{{ route('public.instructors') }}" class="{{ request()->routeIs('public.instructors') ? 'active' : '' }}">
                         Enseignants
                    </a></li>
                    <li><a href="{{ route('public.staff') }}" class="{{ request()->routeIs('public.staff') ? 'active' : '' }}">
                         Corps Administratif
                    </a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            {{-- User Menu or Login Button --}}
            @if(auth()->check())
                {{-- Authenticated User Menu --}}
                <div class="dropdown d-flex align-items-center" style="margin-left: 1rem;">
                    <button class="user-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if(auth()->user()?->profile_photo)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" 
                                class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover; border: 2px solid #0d9488;">
                        @else
                            <div class="user-avatar-placeholder">
                                {{ auth()->user()?->initials ?? 'U' }}
                            </div>
                        @endif
                        <span class="user-name">{{ auth()->user()?->name }}</span>
                    </button>
                    
                    <ul class="dropdown-menu dropdown-menu-end user-dropdown" style="min-width: 280px;">
                        <li class="dropdown-header">
                            <div class="user-header-info">
                                @if(auth()->user()?->profile_photo)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="rounded-circle" style="width: 42px; height: 42px; object-fit: cover;">
                                @else
                                    <div class="user-avatar-placeholder" style="width: 42px; height: 42px;">
                                        {{ auth()->user()?->initials ?? 'U' }}
                                    </div>
                                @endif
                                <div>
                                    <div class="user-header-name">{{ auth()->user()?->name }}</div>
                                    <div class="user-header-role">{{ auth()->user()?->role_label ?? auth()->user()?->role }}</div>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider" style="margin: 8px 0;"></li>
                        <li>
                            <a class="dropdown-item dashboard-link" href="{{ auth()->user()->getDashboardRoute() }}">
                                <i class="bi bi-speedometer2"></i>
                                <span>{{ app()->getLocale() === 'fr' ? 'Mon Tableau de Bord' : 'My Dashboard' }}</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.show') }}">
                                <i class="bi bi-person-circle"></i>
                                <span>{{ app()->getLocale() === 'fr' ? 'Mon Profil' : 'My Profile' }}</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider" style="margin: 8px 0;"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" style="display: block;">
                                @csrf
                                <button type="submit" class="dropdown-item logout-btn" style="width: 100%; text-align: left;">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>{{ app()->getLocale() === 'fr' ? 'Déconnexion' : 'Logout' }}</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                {{-- Login Button for Guests --}}
                <a class="btn-getstarted" href="{{ route('login') }}">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    {{ app()->getLocale() === 'fr' ? 'Espace Connexion' : 'Login' }}
                </a>
            @endif

        </div>
    </header>

    {{-- ─── MAIN CONTENT ─────────────────────────────────────── --}}
    <main class="main">
        @yield('content')
    </main>

    {{-- ─── FOOTER ───────────────────────────────────────────── --}}
    <footer id="footer" class="footer accent-background">
        <div class="container footer-top">
            <div class="row gy-4">

                <div class="col-lg-5 col-md-12 footer-about">
                    <a href="{{ route('home') }}" class="logo d-flex align-items-center">
                        <span class="sitename">{{ $settings->platform_name ?? 'Millénaire Connect' }}</span>
                    </a>
                    <p>{{ $settings->about_description ?? 'Excellence académique pour un avenir brillant. Plateforme de digitalisation du Collège Millénaire Bilingue, Douala, Cameroun.' }}</p>
                    <div class="social-links d-flex mt-4">
                        @if($settings->social_facebook ?? null)
                            <a href="{{ $settings->social_facebook }}" target="_blank"><i class="bi bi-facebook"></i></a>
                        @endif
                        @if($settings->social_twitter ?? null)
                            <a href="{{ $settings->social_twitter }}" target="_blank"><i class="bi bi-twitter-x"></i></a>
                        @endif
                        <a href="mailto:{{ $settings->email ?? 'contact@millenaire.cm' }}"><i class="bi bi-envelope-fill"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-6 footer-links">
                    <h4>{{ app()->getLocale() === 'fr' ? 'Navigation' : 'Navigation' }}</h4>
                    <ul>
                        <li><a href="{{ route('home') }}">{{ app()->getLocale() === 'fr' ? 'Accueil' : 'Home' }}</a></li>
                        <li><a href="{{ route('public.about') }}">{{ app()->getLocale() === 'fr' ? 'À Propos' : 'About' }}</a></li>
                        <li><a href="{{ route('public.instructors') }}">{{ app()->getLocale() === 'fr' ? 'Enseignants' : 'Instructors' }}</a></li>
                        <li><a href="{{ route('public.staff') }}">{{ app()->getLocale() === 'fr' ? 'Corps Admin.' : 'Staff' }}</a></li>
                        <li><a href="{{ route('login') }}">{{ app()->getLocale() === 'fr' ? 'Connexion' : 'Login' }}</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-6 footer-links">
                    <h4>{{ app()->getLocale() === 'fr' ? 'Fonctionnalités' : 'Features' }}</h4>
                    <ul>
                        <li><a href="#">{{ app()->getLocale() === 'fr' ? 'Bulletins Scolaires' : 'Report Cards' }}</a></li>
                        <li><a href="#">{{ app()->getLocale() === 'fr' ? 'Emploi du Temps' : 'Schedule' }}</a></li>
                        <li><a href="#">{{ app()->getLocale() === 'fr' ? 'Suivi des Absences' : 'Attendance' }}</a></li>
                        <li><a href="#">{{ app()->getLocale() === 'fr' ? 'Paiements Mobile Money' : 'Mobile Payments' }}</a></li>
                        <li><a href="#">{{ app()->getLocale() === 'fr' ? 'E-Learning' : 'E-Learning' }}</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-12 footer-contact text-center text-md-start">
                    <h4>{{ app()->getLocale() === 'fr' ? 'Nous Contacter' : 'Contact Us' }}</h4>
                    @if($settings->address ?? null)
                        <p>{{ $settings->address }}</p>
                    @else
                        <p>Douala, Cameroun</p>
                    @endif
                    @if($settings->phone ?? null)
                        <p class="mt-3"><strong>Tél:</strong> <span>{{ $settings->phone }}</span></p>
                    @endif
                    @if($settings->email ?? null)
                        <p><strong>Email:</strong> <span>{{ $settings->email }}</span></p>
                    @endif
                </div>

            </div>
        </div>

        <div class="container copyright text-center mt-4">
            <p>© {{ date('Y') }} <strong class="px-1 sitename">{{ $settings->platform_name ?? 'Millénaire Connect' }}</strong> — Collège Millénaire Bilingue, Douala</p>
        </div>
    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/purecounterjs@1.5.0/dist/purecounter_vanilla.js"></script>

    <!-- Landing JS -->
    <script src="{{ asset('js/landing.js') }}"></script>

    <!-- Real-time Settings Updater (Phase 10) -->
    @if(app()->environment('production') || request()->getHost() === 'localhost:8000')
    {{-- Inclure Laravel Echo pour les broadcasts --}}
    <script src="{{ mix('js/app.js') }}" defer></script>
    <script src="{{ asset('js/realtime-settings-updater.js') }}"></script>
    @endif

    @stack('scripts')
</body>
</html>
