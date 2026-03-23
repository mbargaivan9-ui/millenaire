@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Gestion des Classes' : 'Class Management')

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<style>
  .classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
    margin-top: 20px;
  }
  
  .class-card {
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    border: 1px solid #f0f0f0;
  }
  
  .class-card:hover {
    box-shadow: 0 12px 28px rgba(0,0,0,0.12);
    transform: translateY(-4px);
    border-color: var(--primary);
  }
  
  .class-card-header {
    padding: 24px;
    position: relative;
    overflow: hidden;
  }
  
  .class-card-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), #7c3aed);
  }
  
  .class-header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
  }
  
  .class-name {
    font-size: 18px;
    font-weight: 700;
    color: #000;
    margin: 0 0 8px 0;
    letter-spacing: -0.5px;
  }
  
  .class-section-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    margin-top: 8px;
  }
  
  .section-fr {
    background: linear-gradient(135deg, #e8f4f8 0%, #d4e8f0 100%);
    color: #0066cc;
  }
  
  .section-en {
    background: linear-gradient(135deg, #f0e8f8 0%, #e8d4f0 100%);
    color: #6600cc;
  }
  
  .capacity-badge {
    background: var(--primary-bg);
    color: var(--primary);
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
  }
  
  .class-body {
    padding: 20px 24px;
    border-top: 1px solid #f0f0f0;
  }
  
  .class-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
  }
  
  .class-stat:last-child {
    margin-bottom: 0;
  }
  
  .stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
  }
  
  .stat-students {
    background: #e8f4ff;
    color: #0066cc;
  }
  
  .stat-teacher {
    background: #f0e8f8;
    color: #6600cc;
  }
  
  .stat-subject {
    background: #e8f8f0;
    color: #00996600;
  }
  
  .stat-content {
    flex: 1;
  }
  
  .stat-label {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 2px;
  }
  
  .stat-value {
    font-size: 14px;
    font-weight: 600;
    color: #000;
    word-break: break-word;
  }
  
  .stat-value.text-muted {
    color: #999;
    font-style: italic;
  }
  
  .class-footer {
    padding: 16px 24px;
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
  }
  
  .btn-icon {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
  }
  
  .btn-view {
    background: #e8f4ff;
    color: #0066cc;
  }
  
  .btn-view:hover {
    background: #0066cc;
    color: #fff;
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
  
  .btn-delete:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }
  
  .classes-empty {
    grid-column: 1 / -1;
  }
  
  .empty-state {
    text-align: center;
    padding: 80px 40px;
    color: #999;
  }
  
  .empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
  }
  
  .empty-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
  }
  
  .empty-text {
    font-size: 14px;
    margin-bottom: 24px;
  }
</style>

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

{{-- Classes Grid --}}
<div style="margin-bottom: 20px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <div>
      <h3 style="margin:0;font-size:18px;font-weight:700">{{ $isFr ? 'Classes' : 'Classes' }}</h3>
      <p style="margin:8px 0 0 0;font-size:13px;color:var(--text-muted)">
        {{ $classes?->total() ?? count($classes) ?? 0 }} {{ $isFr ? 'classe(s) trouvée(s)' : 'class(es) found' }}
      </p>
    </div>
  </div>

  @if($classes && $classes->count() > 0)
    <div class="classes-grid">
      @foreach($classes as $class)
      <div class="class-card">
        {{-- Header --}}
        <div class="class-card-header">
          <div class="class-header-content">
            <div style="flex: 1">
              <h3 class="class-name">{{ $class->name }}</h3>
              <span class="class-section-badge {{ $class->section === 'anglophone' ? 'section-en' : 'section-fr' }}">
                {{ $class->section === 'anglophone' ? '🇬🇧 ' : '🇫🇷 ' }}{{ $class->section === 'anglophone' ? 'Anglophone' : 'Francophone' }}
              </span>
            </div>
            @if($class->capacity)
            <div class="capacity-badge">
              {{ $class->students_count ?? 0 }}/{{ $class->capacity }}
            </div>
            @endif
          </div>
        </div>

        {{-- Body --}}
        <div class="class-body">
          {{-- Students --}}
          <div class="class-stat">
            <div class="stat-icon stat-students">👥</div>
            <div class="stat-content">
              <div class="stat-label">{{ $isFr ? 'Élèves' : 'Students' }}</div>
              <div class="stat-value">{{ $class->students_count ?? 0 }}</div>
            </div>
          </div>

          {{-- Head Teacher --}}
          <div class="class-stat">
            <div class="stat-icon stat-teacher">👨‍🏫</div>
            <div class="stat-content">
              <div class="stat-label">{{ $isFr ? 'Prof. Principal' : 'Head Teacher' }}</div>
              <div class="stat-value @if(!$class->profPrincipal) text-muted @endif">
                {{ $class->profPrincipal?->name ?? ($isFr ? 'Non assigné' : 'Unassigned') }}
              </div>
            </div>
          </div>

          {{-- Subject --}}
          <div class="class-stat">
            <div class="stat-icon stat-subject">📚</div>
            <div class="stat-content">
              <div class="stat-label">{{ $isFr ? 'Matière' : 'Subject' }}</div>
              <div class="stat-value @if(!$class->headTeacher?->classSubjectTeachers?->where('class_id', $class->id)->first()?->subject) text-muted @endif">
                @php
                  $principalSubject = $class->headTeacher?->classSubjectTeachers
                    ->where('class_id', $class->id)
                    ->first()?->subject?->name;
                @endphp
                {{ $principalSubject ?? ($isFr ? 'Non assignée' : 'Unassigned') }}
              </div>
            </div>
          </div>
        </div>

        {{-- Footer --}}
        <div class="class-footer">
          <a href="{{ route('admin.classes.show', $class->id) }}" class="btn-icon btn-view" title="{{ $isFr ? 'Voir détails' : 'View details' }}">
            <i data-lucide="eye" style="width:16px;height:16px"></i>
          </a>
          <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn-icon btn-edit" title="{{ $isFr ? 'Modifier' : 'Edit' }}">
            <i data-lucide="edit-2" style="width:16px;height:16px"></i>
          </a>
          <form action="{{ route('admin.classes.destroy', $class->id) }}" method="POST" style="display:inline"
                onsubmit="return confirm('{{ $isFr ? 'Êtes-vous sûr ? Les élèves ne peuvent pas être supprimés.' : 'Are you sure? Students cannot be deleted.' }}')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-icon btn-delete" title="{{ $isFr ? 'Supprimer' : 'Delete' }}" 
                    @if(($class->students_count ?? 0) > 0) disabled @endif>
              <i data-lucide="trash-2" style="width:16px;height:16px"></i>
            </button>
          </form>
        </div>
      </div>
      @endforeach
    </div>

    {{-- Pagination --}}
    @if($classes?->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:30px;padding-top:20px;border-top:1px solid var(--border)">
      <small style="color:var(--text-muted);font-size:12px">
        {{ $isFr ? 'Affichage' : 'Showing' }} {{ $classes->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $classes->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ $classes->total() }} {{ $isFr ? 'résultats' : 'results' }}
      </small>
      <div style="display:flex;gap:4px">
        {!! $classes->links('pagination::simple-tailwind') !!}
      </div>
    </div>
    @endif
  @else
    <div class="classes-grid classes-empty">
      <div class="empty-state">
        <div class="empty-icon">📚</div>
        <div class="empty-title">{{ $isFr ? 'Aucune classe trouvée' : 'No classes found' }}</div>
        <div class="empty-text">{{ $isFr ? 'Créez votre première classe pour commencer' : 'Create your first class to get started' }}</div>
        <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
          <i data-lucide="plus" style="width:14px;height:14px"></i>
          {{ $isFr ? 'Créer une classe' : 'Create class' }}
        </a>
      </div>
    </div>
  @endif
</div>

@endsection

