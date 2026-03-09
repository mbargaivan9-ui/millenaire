{{--
    | teacher/materials/index.blade.php — Mes Ressources Pédagogiques
    --}}

@extends('layouts.app')
@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Mes Ressources' : 'My Resources');
@endphp
@section('title', $pageTitle)

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
                <i data-lucide="folder-open"></i>
            </div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Mes Ressources Pédagogiques' : 'My Teaching Resources' }}</h1>
                <p class="page-subtitle text-muted">{{ $materials->total() }} {{ $isFr ? 'ressources publiées' : 'published resources' }}</p>
            </div>
        </div>
        <a href="{{ route('teacher.materials.create') }}" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Ajouter une ressource' : 'Add resource' }}
        </a>
    </div>
</div>

@if($materials->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i data-lucide="folder-open" style="width:48px;opacity:.25;display:block;margin:0 auto 1rem"></i>
        <h5 class="text-muted">{{ $isFr ? 'Aucune ressource publiée.' : 'No resources yet.' }}</h5>
        <p class="text-muted" style="font-size:.85rem">{{ $isFr ? 'Commencez par ajouter un PDF, une vidéo ou un lien.' : 'Start by adding a PDF, video, or link.' }}</p>
        <a href="{{ route('teacher.materials.create') }}" class="btn btn-primary mt-2">
            <i data-lucide="plus" style="width:14px" class="me-1"></i>{{ $isFr ? 'Première ressource' : 'First resource' }}
        </a>
    </div>
</div>
@else

<div class="row gy-3">
    @foreach($materials as $mat)
    @php
        $iconMap  = ['pdf' => '📄', 'video' => '🎬', 'powerpoint' => '📊', 'link' => '🔗'];
        $colorMap = ['pdf' => '#ef4444', 'video' => '#9333ea', 'powerpoint' => '#f97316', 'link' => '#3b82f6'];
        $icon  = $iconMap[$mat->type]  ?? '📎';
        $color = $colorMap[$mat->type] ?? '#64748b';
    @endphp
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div style="width:44px;height:44px;border-radius:12px;background:{{ $color }}15;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.3rem">{{ $icon }}</div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold text-truncate" style="font-size:.88rem">{{ $mat->title }}</div>
                        <div style="font-size:.73rem;color:var(--text-muted)">{{ $mat->subject?->name }}</div>
                    </div>
                    @if($mat->is_published)
                        <span class="badge bg-success" style="font-size:.65rem">{{ $isFr ? 'Publié' : 'Published' }}</span>
                    @else
                        <span class="badge bg-warning" style="font-size:.65rem">{{ $isFr ? 'Brouillon' : 'Draft' }}</span>
                    @endif
                </div>
                @if($mat->description)
                <p style="font-size:.79rem;color:var(--text-muted);margin:.75rem 0 0">{{ Str::limit($mat->description, 80) }}</p>
                @endif
                <div class="d-flex align-items-center gap-2 mt-3" style="font-size:.73rem;color:var(--text-muted)">
                    <i data-lucide="calendar" style="width:12px"></i>
                    {{ $mat->created_at?->format('d/m/Y') }}
                    <span class="ms-auto">{{ $mat->classes->pluck('name')->implode(', ') }}</span>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('teacher.materials.edit', $mat->id) }}" class="btn btn-sm btn-light" style="flex:1;justify-content:center">
                    <i data-lucide="edit-2" style="width:13px" class="me-1"></i>{{ $isFr ? 'Modifier' : 'Edit' }}
                </a>
                <form method="POST" action="{{ route('teacher.materials.destroy', $mat->id) }}"
                      onsubmit="return confirm('{{ $isFr ? 'Supprimer cette ressource ?' : 'Delete this resource?' }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i data-lucide="trash-2" style="width:13px"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-3">{{ $materials->links() }}</div>
@endif

@endsection
