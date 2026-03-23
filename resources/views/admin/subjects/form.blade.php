@extends('layouts.app')
@php $isFr = app()->getLocale() === 'fr'; @endphp
@section('title', isset($subject) ? ($isFr ? 'Modifier une Matière' : 'Edit Subject') : ($isFr ? 'Créer une Matière' : 'Create Subject'))

@section('content')

<style>
  .subject-form-container {
    max-width: 700px;
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
  
  .form-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  
  .form-section h3 {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 8px;
  }
  
  .card-premium {
    border: 1px solid #e8e8e8;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  }
</style>

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.subjects.index') }}" style="color:var(--primary);text-decoration:none">{{ $isFr ? 'Matières' : 'Subjects' }}</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ isset($subject) ? ($isFr ? 'Modifier' : 'Edit') : ($isFr ? 'Créer' : 'Create') }}</span>
    </div>
    <h1 class="page-title">{{ isset($subject) ? ($isFr ? 'Modifier la Matière' : 'Edit Subject') : ($isFr ? 'Nouvelle Matière' : 'New Subject') }}</h1>
    <p class="page-subtitle">{{ isset($subject) ? ($isFr ? 'Mettre à jour les détails de la matière' : 'Update subject details') : ($isFr ? 'Créer une nouvelle matière dans l\'établissement' : 'Create a new subject in the institution') }}</p>
  </div>
</div>

<div class="subject-form-container">
  {{-- Form Card --}}
  <div class="card card-premium">
    <div class="card-header">
      <i data-lucide="book" style="width:18px;height:18px"></i>
      <span style="font-weight:600">{{ isset($subject) ? ($isFr ? 'Modifier les Détails' : 'Edit Details') : ($isFr ? 'Détails de la Matière' : 'Subject Details') }}</span>
    </div>
    <div class="card-body" style="padding: 32px">
      <form method="POST" action="{{ isset($subject) ? route('admin.subjects.update', $subject->id) : route('admin.subjects.store') }}">
        @csrf
        @if(isset($subject))
          @method('PUT')
        @endif
        
        {{-- Section Selection --}}
        <div style="margin-bottom: 32px">
          <div style="font-size:13px;font-weight:600;text-transform:uppercase;color:var(--text-muted);margin-bottom:12px">
            {{ $isFr ? 'Sélectionnez la Section' : 'Select Section' }} <span style="color:var(--danger)">*</span>
          </div>
          <div class="section-selector">
            <div class="section-option">
              <input type="radio" name="section" value="francophone" id="section_fr" 
                     @if(isset($subject) && $subject->section === 'francophone' || old('section') === 'francophone' || (!isset($subject) && !old('section'))) checked @endif required>
              <label for="section_fr">
                <div class="section-flag">🇫🇷</div>
                <div>{{ $isFr ? 'Francophone' : 'Francophone' }}</div>
              </label>
            </div>
            <div class="section-option">
              <input type="radio" name="section" value="anglophone" id="section_en" 
                     @if(isset($subject) && $subject->section === 'anglophone' || old('section') === 'anglophone') checked @endif>
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

        {{-- Subject Name & Code --}}
        <div class="form-group-grid">
          <div>
            <label class="form-label">{{ $isFr ? 'Nom de la Matière' : 'Subject Name' }} <span style="color:var(--danger)">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                   placeholder="{{ $isFr ? 'Ex: Mathématiques' : 'E.g: Mathematics' }}" 
                   value="{{ isset($subject) ? $subject->name : old('name') }}" required>
            @error('name')
              <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label class="form-label">{{ $isFr ? 'Code' : 'Code' }} <span style="color:var(--danger)">*</span></label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                   placeholder="MATH" 
                   value="{{ isset($subject) ? $subject->code : old('code') }}" required maxlength="10">
            @error('code')
              <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- Coefficient --}}
        <div style="margin-bottom: 20px">
          <label class="form-label">{{ $isFr ? 'Coefficient' : 'Coefficient' }} <span style="color:var(--danger)">*</span></label>
          <input type="number" name="coefficient" class="form-control @error('coefficient') is-invalid @enderror" 
                 placeholder="1" min="0.5" max="10" step="0.5" 
                 value="{{ isset($subject) ? $subject->coefficient : old('coefficient', 1) }}" required>
          <div style="font-size:12px;color:var(--text-muted);margin-top:6px">
            {{ $isFr ? 'Le coefficient doit être entre 0.5 et 10' : 'Coefficient must be between 0.5 and 10' }}
          </div>
          @error('coefficient')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Description --}}
        <div style="margin-bottom: 20px">
          <label class="form-label">{{ $isFr ? 'Description' : 'Description' }}</label>
          <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                    rows="4" placeholder="{{ $isFr ? 'Description de la matière...' : 'Subject description...' }}">{{ isset($subject) ? $subject->description : old('description') }}</textarea>
          @error('description')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Department --}}
        <div style="margin-bottom: 20px">
          <label class="form-label">{{ $isFr ? 'Département' : 'Department' }}</label>
          <select name="department" class="form-control @error('department') is-invalid @enderror">
            <option value="">{{ $isFr ? 'Sélectionner...' : 'Select...' }}</option>
            <option value="Général" @if((isset($subject) && $subject->department === 'Général') || old('department') === 'Général') selected @endif>{{ $isFr ? 'Général' : 'General' }}</option>
            <option value="Scientifique" @if((isset($subject) && $subject->department === 'Scientifique') || old('department') === 'Scientifique') selected @endif>{{ $isFr ? 'Scientifique' : 'Scientific' }}</option>
            <option value="Littéraire" @if((isset($subject) && $subject->department === 'Littéraire') || old('department') === 'Littéraire') selected @endif>{{ $isFr ? 'Littéraire' : 'Literary' }}</option>
            <option value="Technique" @if((isset($subject) && $subject->department === 'Technique') || old('department') === 'Technique') selected @endif>{{ $isFr ? 'Technique' : 'Technical' }}</option>
          </select>
          @error('department')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Active Status --}}
        <div style="margin-bottom: 30px">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500">
            <input type="checkbox" name="is_active" value="1" 
                   @if(isset($subject) && $subject->is_active || !isset($subject)) checked @endif>
            <span>{{ $isFr ? 'Matière active' : 'Active subject' }}</span>
          </label>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;margin-top:40px;padding-top:20px;border-top:1px solid #eee">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="check" style="width:14px;height:14px"></i>
            {{ isset($subject) ? ($isFr ? 'Mettre à jour' : 'Update') : ($isFr ? 'Créer' : 'Create') }}
          </button>
          <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline">
            <i data-lucide="x" style="width:14px;height:14px"></i>
            {{ $isFr ? 'Annuler' : 'Cancel' }}
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection


