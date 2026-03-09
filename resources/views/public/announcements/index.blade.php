@extends('layouts.public')

@section('title', 'Actualités')

@section('content')
    <div class="container py-5">
        <div class="row">
            <div class="col-12 mb-4">
                <h1>Actualités</h1>
            </div>
        </div>
        <div class="row g-4">
            @forelse($announcements as $ann)
                <div class="col-md-6 col-lg-4">
                    <div class="news-card">
                        @php
                            $bg = isset($ann->featured_image) ? asset('storage/' . ltrim($ann->featured_image, '/')) : null;
                            $bgStyle = $bg
                                ? "background-image: url('{$bg}'); background-size: cover; background-position: center;"
                                : "background: linear-gradient(135deg, #667eea, #764ba2);";
                        @endphp
                        <div class="news-image" style="{{ $bgStyle }} height:180px;"></div>
                        <div class="news-content">
                            <span class="news-date"><i class="fas fa-calendar-alt me-2"></i>{{ $ann->published_date }}</span>
                            <h5 class="news-title">{{ $ann->title }}</h5>
                            <p class="news-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($ann->content), 150) }}</p>
                            <a href="{{ route('announcements.show', ['slug' => $ann->slug]) }}" class="news-link">Lire plus <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    </div>
                </div>
                
            @empty
                <div class="col-12">
                    <p>Aucune actualité disponible.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
