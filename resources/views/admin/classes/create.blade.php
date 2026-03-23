@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Créer une Classe' : 'Create Class')

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<style>
  .class-form-container {
    max-width: 750px;
    margin: 0 auto;
  }
  
  .section-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 24px;
  }
  
  .section-option {
    position: relative;
  }
  
  .section-option input[type="radio"] {
    display: none;
  }
  
  .section-option label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    border: 2px solid #ddd;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fff;
    min-height: 120px;
    font-weight: 500;
  }
  
  .section-option input[type="radio"]:checked + label {
    border-color: var(--primary);
    background: rgba(var(--primary-rgb), 0.05);
  }
  
  .section-flag {
    font-size: 32px;
    margin-bottom: 8px;
  }
  
  .form-group-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
  }
  
  .form-group-grid-full {
    grid-column: 1 / -1;
  }
  
  .card-premium {
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  }
  
  .info-box {
    background: #e8f4ff;
    border-left: 4px solid var(--primary);
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #0066cc;
  }
</style>

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.classes.index') }}" style="color:var(--primary);text-decoration:none">{{ $isFr ? 'Classes' : 'Classes' }}</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ $isFr ? 'Créer' : 'Create' }}</span>
    </div>
    <h1 class="page-title">{{ $isFr ? 'Nouvelle Classe' : 'New Class' }}</h1>
    <p class="page-subtitle">{{ $isFr ? 'Créer une nouvelle classe dans l\'établissement' : 'Create a new class in the institution' }}</p>
  </div>
</div>

<div class="class-form-container">
  {{-- Form Card --}}
  <div class="card card-premium">
    <div class="card-header">
      <i data-lucide="grid-3x3" style="width:18px;height:18px"></i>
      <span style="font-weight:600">{{ $isFr ? 'Détails de la Classe' : 'Class Details' }}</span>
    </div>
    <div class="card-body" style="padding: 32px">
      
      <div class="info-box">
        <i data-lucide="info" style="width:14px;height:14px;margin-right:8px;vertical-align:middle"></i>
        {{ $isFr ? 'Remplissez les informations de base de votre classe. Vous pourrez ajouter des enseignants et des matières ultérieurement.' : 'Fill in the basic information for your class. You can add teachers and subjects later.' }}
      </div>

      <form method="POST" action="{{ route('admin.classes.store') }}">
        @csrf
        
        {{-- Section Selection --}}
        <div style="margin-bottom: 32px">
          <div style="font-size:13px;font-weight:600;text-transform:uppercase;color:var(--text-muted);margin-bottom:12px">
            {{ $isFr ? 'Sélectionnez la Section de la Classe' : 'Select Class Section' }} <span style="color:var(--danger)">*</span>
          </div>
          <div class="section-selector">
            <div class="section-option">
              <input type="radio" name="section" value="francophone" id="section_fr" 
                     {{ old('section') === 'francophone' || (!old('section')) ? 'checked' : '' }} required>
              <label for="section_fr">
                <div class="section-flag">🇫🇷</div>
                <div>{{ $isFr ? 'Francophone' : 'Francophone' }}</div>
              </label>
            </div>
            <div class="section-option">
              <input type="radio" name="section" value="anglophone" id="section_en" 
                     {{ old('section') === 'anglophone' ? 'checked' : '' }}>
              <label for="section_en">
                <div class="section-flag">🇬🇧</div>
                <div>{{ $isFr ? 'Anglophone' : 'Anglophone' }}</div>
              </label>
            </div>
          </div>
          @error('section')
            <div style="color:var(--danger);font-size:12px;margin-top:8px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Nom de la classe & Capacité --}}
        <div class="form-group-grid">
          <div>
            <label class="form-label">{{ $isFr ? 'Nom de la Classe' : 'Class Name' }} <span style="color:var(--danger)">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                   placeholder="{{ $isFr ? 'Ex: 4ème A, Terminale C' : 'E.g: Form 4A, Upper 6th' }}" 
                   value="{{ old('name') }}" required>
            @error('name')
              <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label class="form-label">{{ $isFr ? 'Capacité Maximale' : 'Max Capacity' }}</label>
            <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                   placeholder="{{ $isFr ? 'Ex: 30, 40' : 'E.g: 30, 40' }}" 
                   min="1" max="100" value="{{ old('capacity') }}">
            <div style="font-size:12px;color:var(--text-muted);margin-top:6px">
              {{ $isFr ? 'Nombre maximum d\'élèves (optionnel)' : 'Maximum number of students (optional)' }}
            </div>
            @error('capacity')
              <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- Professeur Principal & Matière --}}
        <div class="form-group-grid">
          <div>
            <label class="form-label">{{ $isFr ? 'Professeur Principal' : 'Main Teacher' }}</label>
            <select name="prof_principal_id" class="form-control @error('prof_principal_id') is-invalid @enderror">
              <option value="">{{ $isFr ? '-- Aucun (optionnel) --' : '-- None (optional) --' }}</option>
              @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" {{ old('prof_principal_id') == $teacher->id ? 'selected' : '' }}>
                  {{ $teacher->name }}
                </option>
              @endforeach
            </select>
            @error('prof_principal_id')
              <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label class="form-label">{{ $isFr ? 'Matière du Professeur' : 'Teacher\'s Subject' }} <span id="subject-required" style="color:var(--danger);display:none">*</span></label>
            <select name="teacher_subject_id" class="form-control @error('teacher_subject_id') is-invalid @enderror">
              <option value="">{{ $isFr ? '-- Sélectionner une matière --' : '-- Select a subject --' }}</option>
              @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" {{ old('teacher_subject_id') == $subject->id ? 'selected' : '' }}>
                  {{ $subject->name }} ({{ $subject->section === 'anglophone' ? '🇬🇧' : '🇫🇷' }})
                </option>
              @endforeach
            </select>
            <div style="font-size:12px;color:var(--text-muted);margin-top:6px" id="subject-help">
              {{ $isFr ? 'Sélectionnez d\'abord un professeur' : 'Select a teacher first' }}
            </div>
            @error('teacher_subject_id')
              <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;margin-top:40px;padding-top:20px;border-top:1px solid #eee">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="check" style="width:14px;height:14px"></i>
            {{ $isFr ? 'Créer la Classe' : 'Create Class' }}
          </button>
          <a href="{{ route('admin.classes.index') }}" class="btn btn-outline">
            <i data-lucide="x" style="width:14px;height:14px"></i>
            {{ $isFr ? 'Annuler' : 'Cancel' }}
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const profSelect = document.querySelector('[name="prof_principal_id"]');
  const subjectSelect = document.querySelector('[name="teacher_subject_id"]');
  const subjectRequired = document.getElementById('subject-required');
  const subjectHelp = document.getElementById('subject-help');
  
  function updateSubjectRequired() {
    if (profSelect.value) {
      subjectSelect.setAttribute('required', 'required');
      subjectRequired.style.display = 'inline';
      subjectHelp.style.display = 'none';
    } else {
      subjectSelect.removeAttribute('required');
      subjectRequired.style.display = 'none';
      subjectHelp.style.display = 'block';
      subjectSelect.value = '';
    }
  }
  
  profSelect.addEventListener('change', updateSubjectRequired);
  updateSubjectRequired();
});
</script>

@endsection


