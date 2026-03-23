@extends('layouts.public')

@section('title', app()->getLocale() === 'fr' ? 'Accueil - Annonces' : 'Home - Announcements')

@section('content')
@php
    $isFr = app()->getLocale() === 'fr';
    $announcements = \App\Models\Announcement::published()->orderByDesc('is_featured')->orderByDesc('published_at')->paginate(12);
    $featuredAnnouncements = \App\Models\Announcement::published()->featured()->orderByDesc('published_at')->limit(3)->get();
@endphp

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 80px 20px;">
    <div class="container">
        <div style="text-align: center; color: white;">
            <h1 style="font-size: 3rem; font-weight: 700; margin-bottom: 20px;">
                📢 {{ $isFr ? 'Annonces Importantes' : 'Important Announcements' }}
            </h1>
            <p style="font-size: 1.2rem; opacity: 0.95; margin-bottom: 0;">
                {{ $isFr ? 'Restez informé des dernières actualités de notre institution' : 'Stay updated with the latest news from our institution' }}
            </p>
        </div>
    </div>
</div>

<div class="container" style="padding: 60px 20px;">
    {{-- Annonces En Vedette --}}
    @if($featuredAnnouncements->count() > 0)
    <div style="margin-bottom: 80px;">
        <h2 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 30px; text-align: center;">
            ⭐ {{ $isFr ? 'À la Une' : 'Featured' }}
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
            @foreach($featuredAnnouncements as $featured)
            @php
                $categoryEmojis = [
                    'general' => '🏫',
                    'event' => '📅',
                    'exam' => '📝',
                    'holiday' => '🏖️',
                    'urgent' => '🚨',
                    'payment' => '💳',
                ];
                $emoji = $categoryEmojis[$featured->category] ?? '📢';
            @endphp
            <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: all 0.3s ease; border: 2px solid #667eea;">
                {{-- Cover Image --}}
                @if($featured->cover_image)
                    <div style="height: 250px; overflow: hidden; position: relative;">
                        <img src="{{ asset('storage/' . $featured->cover_image) }}" 
                             alt="{{ $featured->title }}"
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                    </div>
                @else
                    <div style="height: 250px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 80px;">{{ $emoji }}</span>
                    </div>
                @endif

                <div style="padding: 25px;">
                    {{-- Category Badge --}}
                    <div style="display: inline-block; background: #f3f4f6; color: #374151; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; margin-bottom: 15px;">
                        {{ $emoji }} 
                        @switch($featured->category)
                            @case('general') {{ $isFr ? 'Général' : 'General' }} @break
                            @case('event') {{ $isFr ? 'Événement' : 'Event' }} @break
                            @case('exam') {{ $isFr ? 'Examen' : 'Exam' }} @break
                            @case('holiday') {{ $isFr ? 'Congé' : 'Holiday' }} @break
                            @case('urgent') {{ $isFr ? 'Urgent' : 'Urgent' }} @break
                            @case('payment') {{ $isFr ? 'Paiement' : 'Payment' }} @break
                            @default {{ $featured->category }}
                        @endswitch
                    </div>

                    {{-- Title --}}
                    <h3 style="font-size: 1.3rem; font-weight: 700; color: #1f2937; margin-bottom: 12px; line-height: 1.4;">
                        {{ $featured->title }}
                    </h3>

                    {{-- Date --}}
                    <div style="color: #9ca3af; font-size: 0.85rem; margin-bottom: 15px;">
                        📅 {{ $featured->published_at->format($isFr ? 'd/m/Y à H:i' : 'm/d/Y \a\t H:i') }}
                    </div>

                    {{-- Content Preview --}}
                    <p style="color: #6b7280; line-height: 1.6; margin-bottom: 15px; font-size: 0.95rem;">
                        {{ Str::limit(strip_tags($featured->content), 120) }}
                    </p>

                    {{-- Actions --}}
                    <div style="display: flex; gap: 10px; align-items: center; margin-top: 20px;">
                        <a href="{{ route('announcements.show', $featured->slug) }}" 
                           style="flex: 1; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 20px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 600; transition: all 0.3s ease; font-size: 0.9rem;">
                            {{ $isFr ? 'Lire Plus' : 'Read More' }} →
                        </a>

                        @if($featured->attached_file)
                        <a href="{{ asset('storage/' . $featured->attached_file) }}" 
                           download="{{ $featured->attachment_name }}"
                           style="background: #f3f4f6; color: #374151; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; font-size: 0.9rem; display: flex; align-items: center; gap: 6px;">
                            📥 {{ $isFr ? 'Fichier' : 'File' }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tous les Annonces --}}
    <div>
        <h2 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 30px; text-align: center;">
            📰 {{ $isFr ? 'Toutes les Annonces' : 'All Announcements' }}
        </h2>

        @if($announcements->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px;">
                @foreach($announcements as $announcement)
                @php
                    $categoryEmojis = [
                        'general' => '🏫',
                        'event' => '📅',
                        'exam' => '📝',
                        'holiday' => '🏖️',
                        'urgent' => '🚨',
                        'payment' => '💳',
                    ];
                    $emoji = $categoryEmojis[$announcement->category] ?? '📢';
                @endphp
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: all 0.3s ease; border-left: 5px solid #667eea;">
                    {{-- Cover Image --}}
                    @if($announcement->cover_image)
                        <div style="height: 180px; overflow: hidden; position: relative;">
                            <img src="{{ asset('storage/' . $announcement->cover_image) }}" 
                                 alt="{{ $announcement->title }}"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    @else
                        <div style="height: 180px; background: linear-gradient(135deg, #ddd6e8 0%, #e8dce8 100%); display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 50px;">{{ $emoji }}</span>
                        </div>
                    @endif

                    <div style="padding: 20px;">
                        {{-- Category --}}
                        <div style="display: inline-block; background: #f0f3ff; color: #667eea; padding: 4px 10px; border-radius: 15px; font-size: 0.7rem; font-weight: 700; margin-bottom: 10px;">
                            {{ $emoji }}
                        </div>

                        {{-- Title --}}
                        <h4 style="font-size: 1rem; font-weight: 700; color: #1f2937; margin-bottom: 8px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $announcement->title }}
                        </h4>

                        {{-- Date --}}
                        <div style="color: #9ca3af; font-size: 0.8rem; margin-bottom: 10px;">
                            {{ $announcement->published_at->diffForHumans() }}
                        </div>

                        {{-- Content Preview --}}
                        <p style="color: #6b7280; line-height: 1.5; margin-bottom: 12px; font-size: 0.9rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ Str::limit(strip_tags($announcement->content), 100) }}
                        </p>

                        {{-- Actions --}}
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <a href="{{ route('announcements.show', $announcement->slug) }}" 
                               style="flex: 1; background: #667eea; color: white; padding: 10px 15px; border-radius: 6px; text-align: center; text-decoration: none; font-weight: 600; transition: all 0.3s ease; font-size: 0.85rem;">
                                {{ $isFr ? 'Voir' : 'View' }}
                            </a>

                            @if($announcement->attached_file)
                            <a href="{{ asset('storage/' . $announcement->attached_file) }}" 
                               download="{{ $announcement->attachment_name }}"
                               style="background: #f3f4f6; color: #667eea; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; font-size: 0.85rem; display: flex; align-items: center; gap: 4px;">
                                📥
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($announcements->hasPages())
            <div style="display: flex; justify-content: center; margin-top: 50px; gap: 10px; flex-wrap: wrap;">
                @foreach($announcements->links()->elements[0] ?? [] as $page => $url)
                    @if(is_string($url))
                        <span style="padding: 10px 15px; background: #e5e7eb; border-radius: 6px; color: #9ca3af;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" 
                           style="padding: 10px 15px; background: {{ $page == $announcements->currentPage() ? '#667eea' : 'white' }}; color: {{ $page == $announcements->currentPage() ? 'white' : '#667eea' }}; border: 1px solid #667eea; border-radius: 6px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            </div>
            @endif
        @else
            <div style="text-align: center; padding: 60px 20px; background: #f9fafb; border-radius: 12px;">
                <div style="font-size: 80px; margin-bottom: 20px;">📭</div>
                <h3 style="font-size: 1.3rem; color: #374151; margin-bottom: 10px;">
                    {{ $isFr ? 'Aucune annonce' : 'No announcements' }}
                </h3>
                <p style="color: #9ca3af;">
                    {{ $isFr ? 'Revenez bientôt pour les dernières actualités.' : 'Check back soon for the latest updates.' }}
                </p>
            </div>
        @endif
    </div>
</div>

@endsection
