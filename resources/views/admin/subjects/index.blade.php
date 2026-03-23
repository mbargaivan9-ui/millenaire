{{-- admin/subjects/index.blade.php --}}
@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Gestion des Matières' : 'Subject Management')
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<style>
  .subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
  }
  
  .subject-card {
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    padding: 20px;
    background: #fff;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
  }
  
  .subject-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    transform: translateY(-2px);
    border-color: var(--primary);
  }
  
  .subject-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 16px;
  }
  
  .subject-title {
    font-size: 16px;
    font-weight: 600;
    color: #000;
    margin: 0 0 4px 0;
  }
  
  .subject-section {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    margin-top: 8px;
  }
  
  .section-francophone {
    background: #e8f4f8;
    color: #0066cc;
  }
  
  .section-anglophone {
    background: #f0e8f8;
    color: #6600cc;
  }
  
  .subject-meta {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #eee;
    flex-wrap: wrap;
  }
  
  .meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  
  .meta-label {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
    font-weight: 600;
  }
  
  .meta-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--primary);
  }
  
  .subject-description {
    font-size: 12px;
    color: #666;
    margin-bottom: 16px;
    flex-grow: 1;
    line-height: 1.4;
    min-height: 40px;
  }
  
  .subject-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
  }
  
  .btn-action {
    padding: 8px 12px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  
  .btn-edit {
    background: #e8f0ff;
    color: var(--primary);
  }
  
  .btn-edit:hover {
    background: var(--primary);
    color: #fff;
  }
  
  .btn-delete {
    background: #ffe8e8;
    color: var(--danger);
  }
  
  .btn-delete:hover {
    background: var(--danger);
    color: #fff;
  }
</style>

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
          <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline w-100">
            <i data-lucide="rotate-ccw" style="width:13px;height:13px"></i>
            {{ $isFr ? 'Réinitialiser' : 'Reset' }}
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Subjects Grid --}}
<div style="margin-bottom: 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <div>
      <h3 style="margin:0;font-size:16px;font-weight:600">{{ $isFr ? 'Matières' : 'Subjects' }}</h3>
      <p style="margin:4px 0 0 0;font-size:12px;color:var(--text-muted)">
        {{ $subjects?->total() ?? count($subjects) ?? 0 }} {{ $isFr ? 'matière(s) trouvée(s)' : 'subject(s) found' }}
      </p>
    </div>
  </div>

  @forelse($subjects as $subject)
    <div class="subjects-grid">
      <div class="subject-card">
        <div class="subject-card-header">
          <div style="flex-grow:1">
            <p class="subject-title">{{ $subject->name }}</p>
            <span class="subject-section {{ $subject->section === 'anglophone' ? 'section-anglophone' : 'section-francophone' }}">
              {{ $subject->section === 'anglophone' ? '🇬🇧 Anglophone' : '🇫🇷 Francophone' }}
            </span>
          </div>
        </div>

        <div class="subject-meta">
          <div class="meta-item">
            <span class="meta-label">{{ $isFr ? 'Code' : 'Code' }}</span>
            <span class="meta-value">{{ $subject->code }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">{{ $isFr ? 'Coefficient' : 'Coefficient' }}</span>
            <span class="meta-value">{{ $subject->coefficient }}</span>
          </div>
          @if($subject->department)
          <div class="meta-item">
            <span class="meta-label">{{ $isFr ? 'Département' : 'Department' }}</span>
            <span class="meta-value">{{ $subject->department }}</span>
          </div>
          @endif
        </div>

        @if($subject->description)
          <p class="subject-description">{{ $subject->description }}</p>
        @else
          <p class="subject-description" style="color:#ccc;font-style:italic">{{ $isFr ? 'Pas de description' : 'No description' }}</p>
        @endif

        <div class="subject-actions">
          <a href="{{ route('admin.subjects.edit', $subject->id) }}" class="btn-action btn-edit">
            <i data-lucide="edit-2" style="width:13px;height:13px"></i>
            {{ $isFr ? 'Modifier' : 'Edit' }}
          </a>
          <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" style="group-inline" name="destroy-form"
                onsubmit="return confirm('{{ $isFr ? 'Êtes-vous sûr ?' : 'Are you sure ?' }}')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-action btn-delete">
              <i data-lucide="trash-2" style="width:13px;height:13px"></i>
              {{ $isFr ? 'Supprimer' : 'Delete' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  @empty
    <div class="card">
      <div class="card-body" style="text-align:center;padding:60px 20px">
        <i data-lucide="book" style="width:50px;height:50px;color:var(--text-muted);margin-bottom:16px;display:block"></i>
        <div style="color:var(--text-muted);font-size:14px">{{ $isFr ? 'Aucune matière trouvée' : 'No subjects found' }}</div>
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary" style="margin-top:20px">
          <i data-lucide="plus" style="width:14px;height:14px"></i>
          {{ $isFr ? 'Créer la première matière' : 'Create first subject' }}
        </a>
      </div>
    </div>
  @endforelse

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

  @endsection


