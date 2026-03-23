@php
    $isFr = app()->getLocale() === 'fr';
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

@extends('layouts.public')

@section('title', $announcement->title ?? 'Annonce')

@section('content')
<div style="min-height: 100vh; background: #f9fafb;">
    {{-- Banner --}}
    @if($announcement->cover_image && $announcement->cover_image_url)
        <div style="height: 400px; position: relative; overflow: hidden;">
            <img src="{{ $announcement->cover_image_url }}" 
                 alt="{{ $announcement->title }}"
                 style="width: 100%; height: 100%; object-fit: cover; filter: brightness(0.7);">
            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.5) 100%);"></div>
        </div>
    @elseif(!empty($announcement->featured_image))
        <div style="height: 400px; position: relative; overflow: hidden;">
            <img src="{{ asset('storage/' . ltrim($announcement->featured_image, '/')) }}" 
                 alt="{{ $announcement->title }}"
                 style="width: 100%; height: 100%; object-fit: cover; filter: brightness(0.7);">
            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.5) 100%);"></div>
        </div>
    @else
        <div style="height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; position: relative;">
            <span style="font-size: 150px; opacity: 0.3;">{{ $emoji }}</span>
        </div>
    @endif

    {{-- Content --}}
    <div class="container" style="padding: 0 20px;">
        {{-- Header Section with Title --}}
        <div style="background: white; margin-top: -50px; position: relative; z-index: 10; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); padding: 40px; margin-bottom: 40px;">
            <div style="max-width: 800px; margin: 0 auto;">
                {{-- Category Badge --}}
                @if($announcement->category)
                <div style="display: inline-block; background: #f3f4f6; color: #374151; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; margin-bottom: 20px;">
                    {{ $emoji }}
                    @switch($announcement->category)
                        @case('general') {{ $isFr ? 'Général' : 'General' }} @break
                        @case('event') {{ $isFr ? 'Événement' : 'Event' }} @break
                        @case('exam') {{ $isFr ? 'Examen' : 'Exam' }} @break
                        @case('holiday') {{ $isFr ? 'Congé' : 'Holiday' }} @break
                        @case('urgent') {{ $isFr ? 'Urgent' : 'Urgent' }} @break
                        @case('payment') {{ $isFr ? 'Paiement' : 'Payment' }} @break
                        @default {{ $announcement->category }}
                    @endswitch
                </div>
                @endif

                {{-- Title --}}
                <h1 style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 20px; line-height: 1.3;">
                    {{ $announcement->title }}
                </h1>

                {{-- Meta Information --}}
                <div style="display: flex; flex-wrap: wrap; gap: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 0.95rem;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-calendar-alt" style="color: #667eea;"></i>
                        <strong>{{ $announcement->published_at ?? $announcement->published_date ?? 'N/A' }}</strong>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-user" style="color: #667eea;"></i>
                        {{ $announcement->author_name ?? 'Admin' }}
                    </div>

                    @if(isset($announcement->view_count))
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-eye" style="color: #667eea;"></i>
                        {{ $announcement->view_count }} {{ $isFr ? 'vue(s)' : 'view(s)' }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Content Area --}}
        <div style="display: grid; grid-template-columns: 1fr 300px; gap: 40px; margin-bottom: 60px;" class="announcement-grid">
            {{-- Main Content --}}
            <div>
                <div style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
                    {{-- Content --}}
                    <div style="font-size: 1.05rem; line-height: 1.8; color: #4b5563;">
                        {!! $announcement->content !!}
                    </div>

                    {{-- Attached File Section --}}
                    @if($announcement->attached_file || !empty($announcement->attachment_path))
                    <div style="margin-top: 40px; padding-top: 40px; border-top: 2px solid #e5e7eb;">
                        <h3 style="font-size: 1.2rem; font-weight: 700; color: #1f2937; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-paperclip" style="color: #667eea; font-size: 1.1rem;"></i>
                            {{ $isFr ? 'Fichier Joint' : 'Attached File' }}
                        </h3>

                        @php
                            $fileUrl = $announcement->attached_file 
                                ? asset('storage/' . $announcement->attached_file)
                                : asset('storage/' . ltrim($announcement->attachment_path ?? '', '/'));
                            $fileName = $announcement->attachment_name ?? pathinfo($announcement->attachment_path ?? '', PATHINFO_BASENAME);
                        @endphp

                        <a href="{{ $fileUrl }}" 
                           download="{{ $fileName }}"
                           style="display: inline-flex; align-items: center; gap: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 30px; border-radius: 10px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                            <div>
                                <div style="font-size: 1.8rem;">
                                    @php
                                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                        echo match($ext) {
                                            'pdf' => '📄',
                                            'doc', 'docx' => '📝',
                                            'xls', 'xlsx' => '📊',
                                            'ppt', 'pptx' => '🎬',
                                            'zip' => '📦',
                                            default => '📎',
                                        };
                                    @endphp
                                </div>
                            </div>
                            <div style="text-align: left;">
                                <div style="font-size: 1rem; font-weight: 700;">{{ $fileName }}</div>
                                @if($announcement->attachment_size)
                                <div style="font-size: 0.85rem; opacity: 0.9;">
                                    {{ number_format($announcement->attachment_size / 1024, 1) }} KB
                                </div>
                                @endif
                            </div>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar --}}
            <div>
                {{-- Share Box --}}
                <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 30px;">
                    <h4 style="font-size: 0.9rem; font-weight: 700; color: #1f2937; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ $isFr ? 'Partager' : 'Share' }}
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}" 
                           target="_blank"
                           style="padding: 10px; background: #1877f2; color: white; border-radius: 6px; text-align: center; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                            <i class="fab fa-facebook-f me-2"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ url()->current() }}&text={{ urlencode($announcement->title) }}" 
                           target="_blank"
                           style="padding: 10px; background: #1da1f2; color: white; border-radius: 6px; text-align: center; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                            <i class="fab fa-twitter me-2"></i> Twitter
                        </a>
                        <a href="mailto:?subject={{ urlencode($announcement->title) }}&body={{ urlencode(url()->current()) }}" 
                           style="padding: 10px; background: #667eea; color: white; border-radius: 6px; text-align: center; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: all 0.3s ease;">
                            <i class="fas fa-envelope me-2"></i> Email
                        </a>
                    </div>
                </div>

                {{-- Related Announcements --}}
                <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
                    <h4 style="font-size: 0.9rem; font-weight: 700; color: #1f2937; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ $isFr ? 'Autres Annonces' : 'Other Announcements' }}
                    </h4>
                    @php
                        $relatedAnnouncements = \App\Models\Announcement::published()
                            ->where('id', '!=', $announcement->id)
                            ->orderByDesc('published_at')
                            ->limit(3)
                            ->get();
                    @endphp

                    @if($relatedAnnouncements->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach($relatedAnnouncements as $related)
                                <a href="{{ route('announcements.show', $related->slug) }}" 
                                   style="padding: 12px; background: #f9fafb; border-radius: 6px; text-decoration: none; transition: all 0.3s ease; border-left: 3px solid #667eea;">
                                    <div style="font-size: 0.85rem; font-weight: 600; color: #1f2937; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $related->title }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: #9ca3af;">
                                        {{ $related->published_at->diffForHumans() }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p style="color: #9ca3af; font-size: 0.9rem; text-align: center;">
                            {{ $isFr ? 'Aucune autre annonce' : 'No other announcements' }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Back Button --}}
        <div style="text-align: center; margin-bottom: 40px;">
            <a href="{{ route('announcements.index') }}" 
               style="display: inline-block; background: white; color: #667eea; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; border: 2px solid #667eea;">
                ← {{ $isFr ? 'Retour aux annonces' : 'Back to announcements' }}
            </a>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .announcement-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection
