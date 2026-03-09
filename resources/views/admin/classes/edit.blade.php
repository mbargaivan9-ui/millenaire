@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Modifier une Classe' : 'Edit Class')

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.classes.index') }}" style="color:var(--primary);text-decoration:none">{{ $isFr ? 'Classes' : 'Classes' }}</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ $isFr ? 'Modifier' : 'Edit' }}</span>
    </div>
    <h1 class="page-title">{{ $isFr ? 'Modifier la Classe' : 'Edit Class' }}</h1>
    <p class="page-subtitle">{{ $class->name }}</p>
  </div>
</div>

{{-- Form Card --}}
<div class="card">
  <div class="card-header">
    <i data-lucide="grid-3x3" style="width:16px;height:16px"></i>
    <span>{{ $isFr ? 'Détails de la Classe' : 'Class Details' }}</span>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.classes.update', $class->id) }}">
      @csrf
      @method('PUT')
      
      <div style="max-width:500px">
        {{-- Name --}}
        <div class="mb-3">
          <label class="form-label">{{ $isFr ? 'Nom de la classe' : 'Class name' }} <span style="color:var(--danger)">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                 placeholder="{{ $isFr ? 'Ex: 4ème A, Terminale C...' : 'E.g: Form 4A, Upper 6th...' }}" 
                 value="{{ old('name', $class->name) }}" required>
          @error('name')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Section --}}
        <div class="mb-3">
          <label class="form-label">{{ $isFr ? 'Section' : 'Section' }} <span style="color:var(--danger)">*</span></label>
          <select name="section" class="form-control @error('section') is-invalid @enderror" required>
            <option value="">{{ $isFr ? 'Sélectionner une section' : 'Select a section' }}</option>
            <option value="francophone" {{ old('section', $class->section) === 'francophone' ? 'selected' : '' }}>🇫🇷 {{ $isFr ? 'Francophone' : 'Francophone' }}</option>
            <option value="anglophone" {{ old('section', $class->section) === 'anglophone' ? 'selected' : '' }}>🇬🇧 {{ $isFr ? 'Anglophone' : 'Anglophone' }}</option>
          </select>
          @error('section')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Professeur Principal --}}
        <div class="mb-3">
          <label class="form-label">{{ $isFr ? 'Professeur Principal' : 'Main Teacher' }}</label>
          <select name="prof_principal_id" class="form-control @error('prof_principal_id') is-invalid @enderror" id="prof_principal_id">
            <option value="">{{ $isFr ? '-- Aucun --' : '-- None --' }}</option>
            @foreach($teachers as $teacher)
              <option value="{{ $teacher->id }}" {{ old('prof_principal_id', $class->prof_principal_id) == $teacher->id ? 'selected' : '' }}>
                {{ $teacher->name }}
              </option>
            @endforeach
          </select>
          @error('prof_principal_id')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Matière du Professeur Principal --}}
        <div class="mb-3">
          <label class="form-label">{{ $isFr ? 'Matière' : 'Subject' }} <span id="subject-required-edit" style="color:var(--danger);display:none">*</span></label>
          <select name="teacher_subject_id" class="form-control @error('teacher_subject_id') is-invalid @enderror" id="teacher_subject_id">
            <option value="">{{ $isFr ? '-- Sélectionner une matière --' : '-- Select a subject --' }}</option>
            @foreach($subjects as $subject)
              <option value="{{ $subject->id }}" {{ old('teacher_subject_id', $class->headTeacher?->classSubjectTeachers->where('class_id', $class->id)->first()?->subject_id) == $subject->id ? 'selected' : '' }}>
                {{ $subject->name }}{{ $subject->name_en ? " ({$subject->name_en})" : '' }}
              </option>
            @endforeach
          </select>
          @error('teacher_subject_id')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Capacity --}}
        <div class="mb-3">
          <label class="form-label">{{ $isFr ? 'Capacité maximale' : 'Max capacity' }}</label>
          <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                 placeholder="{{ $isFr ? 'Ex: 30, 40...' : 'E.g: 30, 40...' }}" 
                 min="1" max="100" value="{{ old('capacity', $class->capacity) }}">
          @error('capacity')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;margin-top:30px">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="check" style="width:14px;height:14px"></i>
            {{ $isFr ? 'Enregistrer' : 'Save' }}
          </button>
          <a href="{{ route('admin.classes.index') }}" class="btn btn-outline">
            <i data-lucide="x" style="width:14px;height:14px"></i>
            {{ $isFr ? 'Annuler' : 'Cancel' }}
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const profSelect = document.getElementById('prof_principal_id');
  const subjectSelect = document.getElementById('teacher_subject_id');
  const subjectRequired = document.getElementById('subject-required-edit');
  
  function updateSubjectRequired() {
    if (profSelect.value) {
      subjectSelect.setAttribute('required', 'required');
      subjectRequired.style.display = 'inline';
    } else {
      subjectSelect.removeAttribute('required');
      subjectRequired.style.display = 'none';
    }
  }
  
  profSelect.addEventListener('change', updateSubjectRequired);
  updateSubjectRequired();
});
</script>

@endsection


