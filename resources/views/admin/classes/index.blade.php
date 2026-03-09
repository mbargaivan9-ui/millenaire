@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Gestion des Classes' : 'Class Management')

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ $isFr ? 'Classes' : 'Classes' }}</span>
    </div>
    <h1 class="page-title">{{ $isFr ? 'Gestion des Classes' : 'Class Management' }}</h1>
    <p class="page-subtitle">{{ $isFr ? 'Gérer toutes les classes de l\'établissement' : 'Manage all classes in the institution' }}</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
      <i data-lucide="plus" style="width:14px;height:14px"></i>
      {{ $isFr ? 'Nouvelle classe' : 'New class' }}
    </a>
  </div>
</div>

{{-- Filters Card --}}
<div class="card mb-20">
  <div class="card-header">
    <i data-lucide="filter" style="width:16px;height:16px"></i>
    <span>{{ $isFr ? 'Filtres' : 'Filters' }}</span>
  </div>
  <div class="card-body">
    <form method="GET" class="search-filters">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;align-items:flex-end">
        <div>
          <label class="form-label">{{ $isFr ? 'Recherche' : 'Search' }}</label>
          <input type="text" class="form-control" name="search" 
                 placeholder="{{ $isFr ? 'Nom de classe...' : 'Class name...' }}" value="{{ request('search') }}">
        </div>
        <div>
          <label class="form-label">{{ $isFr ? 'Section' : 'Section' }}</label>
          <select class="form-control" name="section">
            <option value="">{{ $isFr ? 'Toutes' : 'All' }}</option>
            <option value="francophone" {{ request('section') === 'francophone' ? 'selected' : '' }}>🇫🇷 {{ $isFr ? 'Francophone' : 'Francophone' }}</option>
            <option value="anglophone" {{ request('section') === 'anglophone' ? 'selected' : '' }}>🇬🇧 {{ $isFr ? 'Anglophone' : 'Anglophone' }}</option>
          </select>
        </div>
        <div>
          <button type="submit" class="btn btn-primary w-100">
            <i data-lucide="search" style="width:13px;height:13px"></i>
            {{ $isFr ? 'Filtrer' : 'Filter' }}
          </button>
        </div>
        <div>
          <a href="{{ route('admin.classes.index') }}" class="btn btn-outline w-100">
            <i data-lucide="rotate-ccw" style="width:13px;height:13px"></i>
            {{ $isFr ? 'Réinitialiser' : 'Reset' }}
          </a>
        </div>
      </div>
    </form>
  </div>
</div>


{{-- Classes Table --}}
<div class="card">
  <div class="card-header">
    <i data-lucide="grid-3x3" style="width:16px;height:16px"></i>
    <span>{{ $isFr ? 'Classes' : 'Classes' }}</span>
    <span style="margin-left:auto;font-size:12px;color:var(--text-muted)">
      {{ $classes?->total() ?? count($classes) ?? 0 }} {{ $isFr ? 'total' : 'total' }}
    </span>
  </div>
  <div class="card-body">
    <div style="overflow-x:auto">
      <table class="table">
        <thead>
          <tr>
            <th>{{ $isFr ? 'Nom de classe' : 'Class name' }}</th>
            <th>{{ $isFr ? 'Section' : 'Section' }}</th>
            <th style="text-align:center">{{ $isFr ? 'Élèves' : 'Students' }}</th>
            <th>{{ $isFr ? 'Prof. principal' : 'Head teacher' }}</th>
            <th>{{ $isFr ? 'Matière' : 'Subject' }}</th>
            <th>{{ $isFr ? 'Actions' : 'Actions' }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($classes as $class)
          <tr>
            <td>
              <div style="font-weight:600;font-size:13px">{{ $class->name }}</div>
              <div style="font-size:11px;color:var(--text-muted)">ID: {{ $class->id }}</div>
            </td>
            <td>
              @php
                $sectionLabel = $class->section === 'anglophone' ? '🇬🇧 Anglophone' : '🇫🇷 Francophone';
              @endphp
              <span style="font-size:12px">{{ $sectionLabel }}</span>
            </td>
            <td style="text-align:center">
              <span style="background:var(--primary-bg);color:var(--primary);padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600">
                {{ $class->students_count ?? 0 }}
                @if($class->capacity)
                /{{ $class->capacity }}
                @endif
              </span>
            </td>
            <td>
              <div style="font-size:12px;font-weight:600">
                {{ $class->profPrincipal?->name ?? '—' }}
              </div>
            </td>
            <td>
              <div style="font-size:12px">
                @php
                  $principalSubject = $class->headTeacher?->classSubjectTeachers
                    ->where('class_id', $class->id)
                    ->first()?->subject?->name;
                @endphp
                @if($principalSubject)
                  <span style="font-weight:600;color:var(--primary)">
                    📚 {{ $principalSubject }}
                  </span>
                @else
                  <span style="color:var(--text-muted)">—</span>
                @endif
              </div>
            </td>
            <td>
              <div style="display:flex;gap:6px">
                <a href="{{ route('admin.classes.show', $class->id) }}" class="btn btn-sm" title="{{ $isFr ? 'Voir détails' : 'View details' }}"
                   style="background:var(--info-bg);color:var(--info)">
                  <i data-lucide="eye" style="width:13px;height:13px"></i>
                </a>
                <button class="btn btn-sm" onclick="window.location.href='{{ route('admin.classes.edit', $class->id) }}'" 
                        title="{{ $isFr ? 'Modifier' : 'Edit' }}"
                        style="background:var(--primary-bg);color:var(--primary)">
                  <i data-lucide="edit-2" style="width:13px;height:13px"></i>
                </button>
                <form action="{{ route('admin.classes.destroy', $class->id) }}" method="POST" style="display:inline"
                      onsubmit="return confirm('{{ $isFr ? 'Êtes-vous sûr ?' : 'Are you sure ?' }}')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm" title="{{ $isFr ? 'Supprimer' : 'Delete' }}"
                          style="background:var(--danger-bg);color:var(--danger)"
                          {{ ($class->students_count ?? 0) > 0 ? 'disabled' : '' }}>
                    <i data-lucide="trash-2" style="width:13px;height:13px"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" style="text-align:center;padding:40px 20px">
              <i data-lucide="grid-3x3" style="width:40px;height:40px;color:var(--text-muted);margin-bottom:16px;display:block"></i>
              <div style="color:var(--text-muted);font-size:13px">{{ $isFr ? 'Aucune classe trouvée' : 'No classes found' }}</div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($classes?->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
      <small style="color:var(--text-muted);font-size:12px">
        {{ $isFr ? 'Affichage' : 'Showing' }} {{ $classes->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $classes->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ $classes->total() }} {{ $isFr ? 'résultats' : 'results' }}
      </small>
      <div style="display:flex;gap:4px">
        {!! $classes->links('pagination::simple-tailwind') !!}
      </div>
    </div>
    @endif
  </div>
</div>

@endsection


