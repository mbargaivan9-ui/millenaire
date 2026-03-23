{{--
    |--------------------------------------------------------------------------
    | home.blade.php — Page d'Accueil Publique Millénaire Connect
    |--------------------------------------------------------------------------
    | Phase 2 — Interface Publique Principale
    | Adapté du template Learner.zip avec les couleurs de la plateforme (#0d9488)
    | Données dynamiques depuis EstablishmentSetting, Announcement, Teacher
    --}}

@extends('layouts.public')

@section('title', __('home') . ' — ' . (isset($settings) ? ($settings->platform_name ?? 'Millénaire Connect') : 'Millénaire Connect'))

@section('content')

@php
    $settings = $settings ?? App\Models\EstablishmentSetting::getInstance();
    $locale   = app()->getLocale();
@endphp

{{-- ════════════════════════════════════════════════════════════
     SECTION HERO
     ════════════════════════════════════════════════════════════ --}}
<section id="courses-hero" class="courses-hero section light-background">

    <div class="hero-content">
        <div class="container">
            <div class="row align-items-center">

                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="hero-text">
                        @if($settings->logo_path)
                        <div style="margin-bottom: 1.5rem;">
                            <img src="{{ asset($settings->logo_path) }}" alt="{{ $settings->platform_name ?? 'Logo' }}" style="max-height: 80px; object-fit: contain;">
                        </div>
                        @endif
                        <h1>
                            {{ $settings->hero_title ?? __('Welcome to millénaire  connect') }}
                        </h1>
                        <p>
                            {{ $settings->hero_subtitle ?? __('For a better world') }}
                        </p>

                        
                        {{-- CTA Buttons --}}
                        <div class="hero-buttons">
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                {{ __('Login') }}
                            </a>
                            <a href="#about" class="btn btn-outline">
                                {{ __('Learn more') }}
                            </a>
                        </div>

                        {{-- Features badges --}}
                        <div class="hero-features">
                            <div class="feature">
                                <i class="bi bi-shield-check"></i>
                                <span>{{ __('Security') ?? (app()->getLocale() === 'fr' ? 'Sécurisé & Fiable' : 'Secure & Reliable') }}</span>
                            </div>
                            <div class="feature">
                                <i class="bi bi-phone"></i>
                                <span>{{ __('Mobile payments') }}</span>
                            </div>
                            <div class="feature">
                                <i class="bi bi-translate"></i>
                                <span>{{ __('Language') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="hero-background">
        <div class="bg-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

</section><!-- /Hero Section -->

{{-- ════════════════════════════════════════════════════════════
     SECTION CAROUSEL (remplace Hero Carousel)
     ════════════════════════════════════════════════════════════ --}}
<section id="hero-carousel" class="hero-carousel section" style="padding: 0; overflow: hidden;">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" style="height: 500px;">
        
        @if(!empty($carouselImages) && count($carouselImages) > 0)
            <!-- Carousel Indicators -->
            <div class="carousel-indicators">
                @foreach($carouselImages as $index => $image)
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}" 
                        class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" 
                        aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>

            <!-- Carousel Items -->
            <div class="carousel-inner" style="height: 100%;">
                @foreach($carouselImages as $index => $image)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" style="height: 100%; position: relative;">
                        <img src="{{ asset($image) }}" class="d-block w-100" alt="Slide {{ $index + 1 }}" 
                            style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                @endforeach
            </div>

            <!-- Carousel Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" 
                style="background: rgba(13, 148, 136, 0.6); width: 60px; border-radius: 5px; left: 20px;">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">{{ __('public.carousel_previous') }}</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" 
                style="background: rgba(13, 148, 136, 0.6); width: 60px; border-radius: 5px; right: 20px;">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">{{ __('public.carousel_next') }}</span>
            </button>

        @else
            <!-- Carousel par défaut (SVG) si aucune image configurée -->
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>

            <div class="carousel-inner" style="height: 100%;">
                <!-- Slide 1 -->
                <div class="carousel-item active" style="height: 100%; position: relative;">
                    <img src="{{ asset('images/Capture d’écran 2026-02-16 160044.png') }}" class="d-block w-100" alt="Excellence Académique" 
                        style="width: 100%; height: 100%; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block" style="background: rgba(13, 148, 136, 0.85); padding: 2rem; border-radius: 10px; bottom: 30px;">
                        <h5 style="font-size: 2rem; font-weight: 700; margin-bottom: .5rem;">{{ __('public.carousel_academic_excellence') }}</h5>
                        <p style="font-size: 1.1rem; margin-bottom: 0;">{{ __('public.carousel_academic_excellence_desc') }}</p>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="carousel-item" style="height: 100%; position: relative;">
                    <img src="{{ asset('images/images (1).png') }}" class="d-block w-100" alt="Communication Sécurisée" 
                        style="width: 100%; height: 100%; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block" style="background: rgba(13, 148, 136, 0.85); padding: 2rem; border-radius: 10px; bottom: 30px;">
                        <h5 style="font-size: 2rem; font-weight: 700; margin-bottom: .5rem;">{{ __('public.carousel_secure_communication') }}</h5>
                        <p style="font-size: 1.1rem; margin-bottom: 0;">{{ __('public.carousel_secure_communication_desc') }}</p>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="carousel-item" style="height: 100%; position: relative;">
                    <img src="{{ asset('images/carousel-3.svg') }}" class="d-block w-100" alt="Gestion Complète" 
                        style="width: 100%; height: 100%; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block" style="background: rgba(13, 148, 136, 0.85); padding: 2rem; border-radius: 10px; bottom: 30px;">
                        <h5 style="font-size: 2rem; font-weight: 700; margin-bottom: .5rem;">{{ __('public.carousel_complete_management') }}</h5>
                        <p style="font-size: 1.1rem; margin-bottom: 0;">{{ __('public.carousel_complete_management_desc') }}</p>
                    </div>
                </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" 
                style="background: rgba(13, 148, 136, 0.6); width: 60px; border-radius: 5px; left: 20px;">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">{{ __('public.carousel_previous') }}</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" 
                style="background: rgba(13, 148, 136, 0.6); width: 60px; border-radius: 5px; right: 20px;">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">{{ __('public.carousel_next') }}</span>
            </button>
        @endif
    </div>

    <style>
        .hero-carousel {
            margin: 0;
            padding: 0;
        }

        #heroCarousel .carousel-indicators {
            bottom: 20px;
            z-index: 10;
        }

        #heroCarousel .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            border: none;
            transition: all 0.3s ease;
        }

        #heroCarousel .carousel-indicators button.active {
            background-color: #0d9488;
            width: 30px;
            border-radius: 6px;
        }

        #heroCarousel .carousel-indicators button:hover {
            background-color: rgba(255, 255, 255, 0.8);
        }

        #heroCarousel .carousel-control-prev,
        #heroCarousel .carousel-control-next {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #heroCarousel:hover .carousel-control-prev,
        #heroCarousel:hover .carousel-control-next {
            opacity: 1;
        }

        #heroCarousel .carousel-control-prev-icon,
        #heroCarousel .carousel-control-next-icon {
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        @media (max-width: 768px) {
            #heroCarousel {
                height: 300px;
            }

            #heroCarousel .carousel-caption {
                display: none !important;
            }

            #heroCarousel .carousel-control-prev,
            #heroCarousel .carousel-control-next {
                opacity: 1;
            }
        }

        @media (max-width: 576px) {
            #heroCarousel {
                height: 250px;
            }
        }
    </style>
</section>
{{-- ════════════════════════════════════════════════════════════
     SECTION ANNONCES (remplace Featured Courses)
     ════════════════════════════════════════════════════════════ --}}
<section id="featured-courses" class="featured-courses section">

    <div class="container section-title" data-aos="fade-up">
        <h2>{{ __('public.announcements_title') }}</h2>
        <p>{{ __('public.announcements_subtitle') }}</p>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">

        {{-- Real-time announcements container --}}
        <div id="announcements-container">
            <div class="row gy-4">

                @forelse($announcements ?? [] as $announcement)
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 + 200 }}">
                    <div class="course-card announcement-card">
                        <div class="course-image">
                            @if($announcement->cover_image)
                                <img src="{{ asset('storage/' . $announcement->cover_image) }}" alt="{{ $announcement->title }}" class="img-fluid">
                            @else
                                <div class="announcement-placeholder-img d-flex align-items-center justify-content-center"
                                     style="height:200px; background: linear-gradient(135deg,#f0fdfa,#ccfbf1)">
                                    <i class="bi bi-megaphone" style="font-size:3rem;color:#0d9488;"></i>
                                </div>
                            @endif
                            <div class="badge {{ $announcement->category === 'urgent' ? 'featured' : ($announcement->category === 'event' ? 'new' : 'certificate') }}">
                                {{ ucfirst($announcement->category ?? 'Info') }}
                            </div>
                        </div>
                        <div class="course-content">
                            <div class="course-meta">
                                <span class="level">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ $announcement->published_at?->format('d/m/Y') ?? $announcement->created_at->format('d/m/Y') }}
                                </span>
                                <span class="duration">{{ __('announcements') }}</span>
                            </div>
                            <h3><a href="{{ route('announcements.show', $announcement->slug ?? $announcement->id) }}">{{ $announcement->title }}</a></h3>
                            <p>{{ Str::limit(strip_tags($announcement->content), 150) }}</p>
                            <a href="{{ route('announcements.show', $announcement->slug ?? $announcement->id) }}" class="btn-course">
                                {{ __('read more') }}
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                {{-- Fallback si pas d'annonces --}}
                <div class="col-12 text-center py-5" data-aos="fade-up">
                    <div class="empty-announcements">
                        <i class="bi bi-megaphone" style="font-size:3rem;color:#0d9488;opacity:.4;"></i>
                        <p class="mt-3 text-muted">{{ __('No announcements') }}</p>
                    </div>
                </div>
                @endforelse

            </div>
        </div>

        <div class="more-courses text-center" data-aos="fade-up" data-aos-delay="500">
            <a href="{{ route('announcements.index') }}" class="btn-more">
                {{ __('View all announcements') }}
            </a>
        </div>

    </div>

</section><!-- /Announcements Section -->


<section id="course-categories" class="course-categories section">

    <div class="container section-title" data-aos="fade-up">
        <h2>{{ __('Features') }}</h2>
        <p>{{ __('Features subtitle') }}</p>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row">

            @php
            $features = [
                [
                    'icon'  => 'bi-bar-chart-line-fill',
                    'color' => '#0d9488',
                    'title_fr' => __('report_cards'),
                    'title_en' => __('preport_cards'),
                    'desc_fr'  => __('report cards desc'),
                    'desc_en'  => __('report cards count'),
                    'count_en' => __('report cardscount'),
                ],
                [
                    'icon'  => 'bi-calendar3',
                    'color' => '#3b82f6',
                    'title_fr' => __('public.feature_schedule_title'),
                    'title_en' => __('public.feature_schedule_title'),
                    'desc_fr'  => __('public.feature_schedule_title_desc'),
                    'desc_en'  => __('public.feature_schedule_title_desc'),
                    'count_fr' => __('public.feature_schedule_count'),
                    'count_en' => __('public.feature_schedule_count'),
                ],
                [
                    'icon'  => 'bi-list-task',
                    'color' => '#8b5cf6',
                    'title_fr' => 'Gestion des Classes',
                    'title_en' => 'Classes Management',
                    'desc_fr'  => 'Organisation et suivi des classes avec gestion des emplois du temps.',
                    'desc_en'  => 'Interactive schedules with real-time conflict detection.',
                    'count_fr' => 'Classes gérées',
                    'count_en' => 'Classes managed',
                ],
                [
                    'icon'  => 'bi-person-check-fill',
                    'color' => '#10b981',
                    'title_fr' => 'Absences & Présences',
                    'title_en' => 'Attendance',
                    'desc_fr'  => 'Suivi quotidien des présences avec notification automatique aux parents.',
                    'desc_en'  => 'Daily attendance tracking with automatic parent notifications.',
                    'count_fr' => 'Absences traitées',
                    'count_en' => 'Absences handled',
                ],
                [
                    'icon'  => 'bi-phone-fill',
                    'color' => '#f59e0b',
                    'title_fr' => 'Paiement Mobile Money',
                    'title_en' => 'Mobile Money Payment',
                    'desc_fr'  => 'Orange Money & MTN MoMo intégrés pour le paiement des frais scolaires.',
                    'desc_en'  => 'Orange Money & MTN MoMo integrated for school fee payments.',
                    'count_fr' => 'Paiements traités',
                    'count_en' => 'Payments processed',
                ],
                [
                    'icon'  => 'bi-book-fill',
                    'color' => '#8b5cf6',
                    'title_fr' => 'E-Learning',
                    'title_en' => 'E-Learning',
                    'desc_fr'  => 'Cours en ligne, PDF, vidéos et quiz interactifs accessibles partout.',
                    'desc_en'  => 'Online courses, PDFs, videos and interactive quizzes accessible anywhere.',
                    'count_fr' => 'Ressources disponibles',
                    'count_en' => 'Resources available',
                ],
                [
                    'icon'  => 'bi-chat-dots-fill',
                    'color' => '#ef4444',
                    'title_fr' => 'Communication Sécurisée',
                    'title_en' => 'Secure Communication',
                    'desc_fr'  => 'Chat interne, appels audio/vidéo et notifications push en temps réel.',
                    'desc_en'  => 'Internal chat, audio/video calls and real-time push notifications.',
                    'count_fr' => 'Messages envoyés',
                    'count_en' => 'Messages sent',
                ],
            ];
            @endphp

            @foreach($features as $i => $feature)
            <div class="col-lg-2 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="{{ ($i % 6) * 50 + 100 }}">
                <div class="category-item text-center">
                    <div class="icon-wrapper mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;background: color-mix(in srgb, {{ $feature['color'] }} 15%, transparent)">
                        <i class="{{ $feature['icon'] }}" style="font-size:1.6rem;color:{{ $feature['color'] }}"></i>
                    </div>
                    <h5 class="mb-1" style="font-size:.9rem;font-weight:600;color:#223a58">
                        {{ $feature['title_en'] }}
                    </h5>
                    <p style="font-size:.78rem;color:#64748b;line-height:1.4">
                        {{ $feature['desc_en'] }}
                    </p>
                </div>
            </div>
            @endforeach

        </div>
    </div>

</section><!-- /Features Section -->


{{-- ════════════════════════════════════════════════════════════
     SECTION ENSEIGNANTS (Featured Instructors)
     ════════════════════════════════════════════════════════════ --}}
<section id="featured-instructors" class="featured-instructors section">

    <div class="container section-title" data-aos="fade-up">
        <h2>{{ __('public.teachers_title') }}</h2>
        <p>{{ __('public.teachers_subtitle') }}</p>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row gy-4">

            @forelse($teachers ?? [] as $i => $teacher)
            <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($i % 4) * 50 + 200 }}">
                <div class="instructor-card">
                    <div class="instructor-image">
                        @if($teacher->user->avatar_url)
                            <img src="{{ asset($teacher->user->avatar_url) }}" class="img-fluid" alt="{{ $teacher->user->display_name ?? $teacher->user->name }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-light" style="height:220px">
                                <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#14b8a6);display:flex;align-items:center;justify-content:center;">
                                    <span style="font-size:2rem;color:white;font-weight:700">
                                        {{ strtoupper(substr($teacher->user->name ?? 'T', 0, 1)) }}
                                    </span>
                                </div>
                            </div>
                        @endif
                        <div class="overlay-content">
                            <div class="course-count">
                                <i class="bi bi-book"></i>
                                <span>{{ $teacher->subjects_count ?? $teacher->subjects()->count() }} {{ __('public.teacher_subjects_label') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="instructor-info">
                        <h5>{{ $teacher->user->display_name ?? $teacher->user->name }}</h5>
                        <p class="specialty">
                            {{ $teacher->subjects->pluck('name')->take(2)->implode(', ') ?: __('public.teacher_label') }}
                        </p>
                        @if($teacher->is_prof_principal)
                        <p class="description">
                            <span class="badge" style="background:#0d9488;font-size:.7rem">
                                {{ __('public.teacher_head') }}
                            </span>
                        </p>
                        @endif
                        <div class="action-buttons">
                            <a href="{{ route('public.teacher.profile', $teacher->id) }}" class="btn-view">
                                {{ __('public.teacher_view_profile') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-4">
                <p class="text-muted">{{ __('public.teachers_empty') }}</p>
            </div>
            @endforelse

        </div>

        <div class="more-courses text-center mt-5" data-aos="fade-up">
            <a href="{{ route('public.instructors') }}" class="btn-more">
                {{ __('public.teachers_view_all') }}
            </a>
        </div>
    </div>

</section><!-- /Instructors Section -->


{{-- ════════════════════════════════════════════════════════════
     SECTION TÉMOIGNAGES
     ════════════════════════════════════════════════════════════ --}}
<section id="testimonials" class="testimonials section">

    <div class="container section-title" data-aos="fade-up">
        <h2>{{ __('public.testimonials_title') }}</h2>
        <p>{{ __('public.testimonials_subtitle') }}</p>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row">
            <div class="col-12">
                <div class="testimonials-container">
                    <div class="swiper testimonials-slider init-swiper" data-aos="fade-up" data-aos-delay="400">
                        <script type="application/json" class="swiper-config">
                          {
                            "loop": true,
                            "speed": 600,
                            "autoplay": { "delay": 5000 },
                            "slidesPerView": 1,
                            "spaceBetween": 30,
                            "pagination": { "el": ".swiper-pagination", "type": "bullets", "clickable": true },
                            "breakpoints": { "768": { "slidesPerView": 2 }, "992": { "slidesPerView": 3 } }
                          }
                        </script>

                        <div class="swiper-wrapper">

                            @forelse($testimonials ?? [] as $testimonial)
                            <div class="swiper-slide">
                                <div class="testimonial-item">
                                    <div class="stars">
                                        @for($s = 1; $s <= 5; $s++)
                                            <i class="bi bi-star-fill"></i>
                                        @endfor
                                    </div>
                                    <p>{{ $testimonial->content }}</p>
                                    <div class="testimonial-profile">
                                        @if($testimonial->photo)
                                            <img src="{{ asset('storage/' . $testimonial->photo) }}" alt="{{ $testimonial->name }}" class="img-fluid rounded-circle">
                                        @else
                                            <div style="width:50px;height:50px;border-radius:50%;background:#0d9488;display:flex;align-items:center;justify-content:center;color:white;font-weight:700">
                                                {{ strtoupper(substr($testimonial->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h3>{{ $testimonial->name }}</h3>
                                            <h4>{{ $testimonial->role }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            {{-- Témoignages par défaut --}}
                            @php
                            $defaultTestimonials = [
                                ['name' => 'Marie Nguema', 'role' => __('public.testimonial_parent_role'), 'content' => __('public.testimonial_parent_content')],
                                ['name' => 'Prof. Etoga Jean', 'role' => __('public.testimonial_teacher_role'), 'content' => __('public.testimonial_teacher_content')],
                                ['name' => 'Amina Bello', 'role' => __('public.testimonial_student_role'), 'content' => __('public.testimonial_student_content')],
                            ];
                            @endphp
                            @foreach($defaultTestimonials as $defTest)
                            <div class="swiper-slide">
                                <div class="testimonial-item">
                                    <div class="stars">
                                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                    <p>{{ $defTest['content'] }}</p>
                                    <div class="testimonial-profile">
                                        <div style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#14b8a6);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:1.1rem;flex-shrink:0;">
                                            {{ strtoupper(substr($defTest['name'], 0, 1)) }}
                                        </div>
                                        <div>
                                            <h3>{{ $defTest['name'] }}</h3>
                                            <h4>{{ $defTest['role'] }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @endforelse
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section><!-- /Testimonials Section -->


{{-- ════════════════════════════════════════════════════════════
     SECTION CTA — Connexion Portail
     ════════════════════════════════════════════════════════════ --}}
<section id="cta" class="cta section light-background">

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row align-items-center">

            <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                <div class="cta-content">
                    <h2>{{ __('public.personal_space_title') }}</h2>
                    <p>{{ __('public.personal_space_subtitle') }}</p>

                    <div class="features-list">
                        @php
                        $roleitems = [
                            ['icon' => 'bi-person-badge', 'text' => 'Admin — ' . __('public.role_admin')],
                            ['icon' => 'bi-person-workspace', 'text' => 'Enseignants — ' . __('public.role_teacher')],
                            ['icon' => 'bi-people', 'text' => 'Parents — ' . __('public.role_parent')],
                            ['icon' => 'bi-mortarboard', 'text' => 'Élèves — ' . __('public.role_student')],
                        ];
                        @endphp
                        @foreach($roleitems as $item)
                        <div class="feature-item" data-aos="fade-up" data-aos-delay="300">
                            <i class="bi bi-check-circle-fill"></i>
                            <span><i class="{{ $item['icon'] }} me-1"></i> {{ $item['text'] }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="cta-actions" data-aos="fade-up" data-aos-delay="500">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            {{ __('public.sign_in') }}
                        </a>
                        <a href="{{ route('public.about') }}" class="btn btn-outline">
                            {{ __('public.learn_more_detail') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                <div class="cta-image">
                    <div style="background:linear-gradient(135deg,#f0fdfa,#e6f9f7);border-radius:16px;padding:2rem;text-align:center">
                        {{-- Stats dynamiques --}}
                        <div class="stats-row d-flex justify-content-around flex-wrap gap-3 mt-3">
                            <div class="stat-item text-center">
                                <h3 class="mb-0" style="color:#0d9488">
                                    <span data-purecounter-start="0" data-purecounter-end="{{ $stats['students'] ?? 500 }}"
                                          data-purecounter-duration="2" class="purecounter"></span>+
                                </h3>
                                <p class="mb-0 small">{{ __('public.stat_students') }}</p>
                            </div>
                            <div class="stat-item text-center">
                                <h3 class="mb-0" style="color:#0d9488">
                                    <span data-purecounter-start="0" data-purecounter-end="{{ $stats['teachers'] ?? 40 }}"
                                          data-purecounter-duration="2" class="purecounter"></span>+
                                </h3>
                                <p class="mb-0 small">{{ __('public.stat_teachers') }}</p>
                            </div>
                            <div class="stat-item text-center">
                                <h3 class="mb-0" style="color:#0d9488">
                                    <span data-purecounter-start="0" data-purecounter-end="{{ $stats['classes'] ?? 15 }}"
                                          data-purecounter-duration="2" class="purecounter"></span>
                                </h3>
                                <p class="mb-0 small">{{ __('public.stat_classes') }}</p>
                            </div>
                        </div>

                        <div class="mt-4 p-3 rounded" style="background:white;box-shadow:0 4px 20px rgba(13,148,136,.1)">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-phone" style="color:white;font-size:1.2rem"></i>
                                </div>
                                <div class="text-start">
                                    <strong style="color:#0f172a">Orange Money & MTN MoMo</strong>
                                    <p class="mb-0 small text-muted">{{ __('public.payment_description') }}</p>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <span style="background:#FF6600;color:white;padding:.3rem .8rem;border-radius:20px;font-size:.75rem;font-weight:600">Orange Money</span>
                                <span style="background:#FFCC00;color:#333;padding:.3rem .8rem;border-radius:20px;font-size:.75rem;font-weight:600">MTN MoMo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</section><!-- /CTA Section -->

@endsection

@push('scripts')
<script>
// ─── Real-time announcements via Laravel Echo / Reverb ────────────────────────
@auth
// Only if user is logged in and Echo is configured
if (typeof window.Echo !== 'undefined') {
    window.Echo.channel('announcements')
        .listen('AnnouncementPublished', (data) => {
            // Prepend new announcement card to the container
            const container = document.querySelector('#announcements-container .row');
            if (container && data.announcement) {
                const a = data.announcement;
                const card = `
                    <div class="col-lg-4 col-md-6 announcement-new" style="animation:fadeIn .4s ease">
                        <div class="course-card announcement-card">
                            <div class="course-image">
                                <div class="announcement-placeholder-img d-flex align-items-center justify-content-center"
                                     style="height:200px;background:linear-gradient(135deg,#f0fdfa,#ccfbf1)">
                                    <i class="bi bi-megaphone" style="font-size:3rem;color:#0d9488"></i>
                                </div>
                                <div class="badge new">Nouveau</div>
                            </div>
                            <div class="course-content">
                                <div class="course-meta">
                                    <span class="level"><i class="bi bi-calendar3 me-1"></i>${a.date}</span>
                                    <span class="duration">Annonce</span>
                                </div>
                                <h3><a href="/announcements/${a.slug || a.id}">${a.title}</a></h3>
                                <p>${a.excerpt}</p>
                                <a href="/announcements/${a.slug || a.id}" class="btn-course">Lire la suite</a>
                            </div>
                        </div>
                    </div>`;
                container.insertAdjacentHTML('afterbegin', card);
            }
        });
}
@endauth

@css
@keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
@endcss
</script>
@endpush
