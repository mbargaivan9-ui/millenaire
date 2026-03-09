{{-- student/course-detail.blade.php --}}
@extends('layouts.app')
@section('title', $material->title)
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('student.courses') }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px"></i>
        </a>
        <div>
            <h1 class="page-title">{{ $material->title }}</h1>
            <p class="page-subtitle text-muted">
                {{ $material->subject?->name }}
                @if($material->teacher)
                · {{ $material->teacher?->user?->name }}
                @endif
            </p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                {{-- Video embed --}}
                @if($material->type === 'video' && $material->video_url)
                @php
                    $url = $material->video_url;
                    $embedUrl = '';
                    if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
                        preg_match('/(?:v=|youtu\.be\/)([A-Za-z0-9_-]{11})/', $url, $m);
                        if (isset($m[1])) $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
                    } elseif (str_contains($url, 'vimeo.com')) {
                        preg_match('/vimeo\.com\/(\d+)/', $url, $m);
                        if (isset($m[1])) $embedUrl = 'https://player.vimeo.com/video/' . $m[1];
                    }
                @endphp
                @if($embedUrl)
                <div style="position:relative;padding-top:56.25%;border-radius:12px;overflow:hidden;background:#000;margin-bottom:1.5rem">
                    <iframe src="{{ $embedUrl }}"
                            style="position:absolute;inset:0;width:100%;height:100%;border:none"
                            allowfullscreen allow="autoplay; encrypted-media"></iframe>
                </div>
                @else
                <a href="{{ $url }}" target="_blank" class="btn btn-primary mb-3">
                    🎬 {{ $isFr ? 'Ouvrir la vidéo' : 'Open video' }}
                </a>
                @endif
                @endif

                {{-- PDF viewer --}}
                @if(in_array($material->type, ['pdf', 'powerpoint']) && $material->file_path)
                <div class="d-flex gap-2 mb-4">
                    <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="btn btn-primary">
                        <i data-lucide="external-link" style="width:14px" class="me-1"></i>
                        {{ $isFr ? 'Ouvrir le document' : 'Open document' }}
                    </a>
                    <a href="{{ Storage::url($material->file_path) }}" download class="btn btn-light">
                        <i data-lucide="download" style="width:14px" class="me-1"></i>
                        {{ $isFr ? 'Télécharger' : 'Download' }}
                    </a>
                </div>
                @php $ext = pathinfo($material->file_path, PATHINFO_EXTENSION); @endphp
                @if(strtolower($ext) === 'pdf')
                <div style="border-radius:12px;overflow:hidden;border:1px solid var(--border)">
                    <iframe src="{{ Storage::url($material->file_path) }}"
                            style="width:100%;height:600px;border:none"></iframe>
                </div>
                @endif
                @endif

                {{-- External link --}}
                @if($material->type === 'link' && $material->external_url)
                <div class="text-center py-4">
                    <div style="font-size:3rem;margin-bottom:1rem">🔗</div>
                    <a href="{{ $material->external_url }}" target="_blank" class="btn btn-primary btn-lg">
                        {{ $isFr ? 'Accéder à la ressource' : 'Open resource' }}
                        <i data-lucide="external-link" style="width:14px" class="ms-2"></i>
                    </a>
                    <div class="mt-2" style="font-size:.75rem;color:var(--text-muted)">
                        {{ $material->external_url }}
                    </div>
                </div>
                @endif

                {{-- Description --}}
                @if($material->description)
                <div class="mt-4 p-3" style="background:var(--surface-2);border-radius:12px">
                    <h6 class="fw-bold mb-2">{{ $isFr ? 'Description' : 'Description' }}</h6>
                    <p style="font-size:.88rem;color:var(--text-secondary);margin:0">{{ $material->description }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ $isFr ? 'Détails' : 'Details' }}</h6>
            </div>
            <div class="card-body" style="font-size:.83rem">
                @foreach([
                    ($isFr ? 'Matière' : 'Subject')     => $material->subject?->name ?? '—',
                    ($isFr ? 'Enseignant' : 'Teacher')  => $material->teacher?->user?->name ?? '—',
                    ($isFr ? 'Type' : 'Type')            => ucfirst($material->type),
                    ($isFr ? 'Publié le' : 'Published')  => $material->created_at?->format('d/m/Y') ?? '—',
                    ($isFr ? 'Taille' : 'Size')          => $material->file_size ? number_format($material->file_size / 1024, 0) . ' KB' : '—',
                ] as $lbl => $val)
                <div class="d-flex justify-content-between mb-2">
                    <span style="color:var(--text-muted)">{{ $lbl }}</span>
                    <strong>{{ $val }}</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
