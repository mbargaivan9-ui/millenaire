{{-- admin/subjects/index.blade.php --}}
@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Gestion des Matières' : 'Subject Management')
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ $isFr ? 'Matières' : 'Subjects' }}</span>
    </div>
    <h1 class="page-title">{{ $isFr ? 'Gestion des Matières' : 'Subject Management' }}</h1>
    <p class="page-subtitle">{{ $isFr ? 'Gérer toutes les matières de l\'établissement' : 'Manage all subjects in the institution' }}</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
      <i data-lucide="plus" style="width:14px;height:14px"></i>
      {{ $isFr ? 'Nouvelle matière' : 'New subject' }}
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
                 placeholder="{{ $isFr ? 'Nom de matière...' : 'Subject name...' }}" value="{{ request('search') }}">
        </div>
        <div>
          <button type="submit" class="btn btn-primary w-100">
            <i data-lucide="search" style="width:13px;height:13px"></i>
            {{ $isFr ? 'Filtrer' : 'Filter' }}
          </button>
        </div>
        <div>
          <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline w-100">
            <i data-lucide="rotate-ccw" style="width:13px;height:13px"></i>
            {{ $isFr ? 'Réinitialiser' : 'Reset' }}
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Subjects Table --}}
<div class="card">
  <div class="card-header">
    <i data-lucide="book" style="width:16px;height:16px"></i>
    <span>{{ $isFr ? 'Matières' : 'Subjects' }}</span>
    <span style="margin-left:auto;font-size:12px;color:var(--text-muted)">
      {{ $subjects?->total() ?? count($subjects) ?? 0 }} {{ $isFr ? 'total' : 'total' }}
    </span>
  </div>
  <div class="card-body">
    <div style="overflow-x:auto">
      <table class="table">
        <thead>
          <tr>
            <th>{{ $isFr ? 'Matière' : 'Subject' }}</th>
            <th>{{ $isFr ? 'Nom anglais' : 'English name' }}</th>
            <th style="text-align:center">{{ $isFr ? 'Coefficient' : 'Coefficient' }}</th>
            <th>{{ $isFr ? 'Enseignants' : 'Teachers' }}</th>
            <th>{{ $isFr ? 'Actions' : 'Actions' }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($subjects as $subject)
          <tr>
            <td>
              <div style="font-weight:600;font-size:13px">{{ $subject->name }}</div>
              <div style="font-size:11px;color:var(--text-muted)">ID: {{ $subject->id }}</div>
            </td>
            <td>
              <div style="font-size:12px">{{ $subject->name_en ?? '—' }}</div>
            </td>
            <td style="text-align:center">
              <span style="background:var(--primary-bg);color:var(--primary);padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600">
                {{ $subject->coefficient }}
              </span>
            </td>
            <td>
              <div style="font-size:11px;color:var(--text-muted)">
                {{ $subject->teachers?->take(2)->map(fn($t) => $t->user->name)->implode(', ') ?? '—' }}
                @if($subject->teachers?->count() > 2)
                  <br><span style="color:var(--primary);cursor:pointer">+{{ $subject->teachers->count() - 2 }} {{ $isFr ? 'autre(s)' : 'more' }}</span>
                @endif
              </div>
            </td>
            <td>
              <div style="display:flex;gap:6px">
                <button class="btn btn-sm" onclick="window.location.href='{{ route('admin.subjects.edit', $subject->id) }}'" 
                        title="{{ $isFr ? 'Modifier' : 'Edit' }}"
                        style="background:var(--primary-bg);color:var(--primary)">
                  <i data-lucide="edit-2" style="width:13px;height:13px"></i>
                </button>
                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" style="display:inline"
                      onsubmit="return confirm('{{ $isFr ? 'Êtes-vous sûr ?' : 'Are you sure ?' }}')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm" title="{{ $isFr ? 'Supprimer' : 'Delete' }}"
                          style="background:var(--danger-bg);color:var(--danger)">
                    <i data-lucide="trash-2" style="width:13px;height:13px"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" style="text-align:center;padding:40px 20px">
              <i data-lucide="book" style="width:40px;height:40px;color:var(--text-muted);margin-bottom:16px;display:block"></i>
              <div style="color:var(--text-muted);font-size:13px">{{ $isFr ? 'Aucune matière trouvée' : 'No subjects found' }}</div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($subjects?->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
      <small style="color:var(--text-muted);font-size:12px">
        {{ $isFr ? 'Affichage' : 'Showing' }} {{ $subjects->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $subjects->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ $subjects->total() }} {{ $isFr ? 'résultats' : 'results' }}
      </small>
      <div style="display:flex;gap:4px">
        {!! $subjects->links('pagination::simple-tailwind') !!}
      </div>
    </div>
    @endif
  </div>
</div>

@endsection


