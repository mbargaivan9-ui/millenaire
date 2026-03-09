@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Modifier une Matière' : 'Edit Subject')

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.subjects.index') }}" style="color:var(--primary);text-decoration:none">{{ $isFr ? 'Matières' : 'Subjects' }}</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ $isFr ? 'Modifier' : 'Edit' }}</span>
    </div>
    <h1 class="page-title">{{ $isFr ? 'Modifier la Matière' : 'Edit Subject' }}</h1>
    <p class="page-subtitle">{{ $subject->name }}</p>
  </div>
</div>

{{-- Form Card --}}
<div class="card">
  <div class="card-header">
    <i data-lucide="book" style="width:16px;height:16px"></i>
    <span>{{ $isFr ? 'Détails de la Matière' : 'Subject Details' }}</span>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.subjects.update', $subject->id) }}">
      @csrf
      @method('PUT')
      
      <div style="max-width:500px">
        {{-- Name French --}}
        <div class="mb-3">
          <label class="form-label">{{ $isFr ? 'Nom (Français)' : 'Name (French)' }} <span style="color:var(--danger)">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                 placeholder="{{ $isFr ? 'Ex: Mathématiques' : 'E.g: Mathematics' }}" 
                 value="{{ old('name', $subject->name) }}" required>
          @error('name')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Name English --}}
        <div class="mb-3">
          <label class="form-label">{{ $isFr ? 'Nom (Anglais)' : 'Name (English)' }}</label>
          <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" 
                 placeholder="{{ $isFr ? 'Ex: Mathematics' : 'E.g: Mathematics' }}" 
                 value="{{ old('name_en', $subject->name_en) }}">
          @error('name_en')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Coefficient --}}
        <div class="mb-3">
          <label class="form-label">{{ $isFr ? 'Coefficient' : 'Coefficient' }} <span style="color:var(--danger)">*</span></label>
          <input type="number" name="coefficient" class="form-control @error('coefficient') is-invalid @enderror" 
                 placeholder="1" min="0.5" max="10" step="0.5" value="{{ old('coefficient', $subject->coefficient) }}" required>
          @error('coefficient')
            <div style="color:var(--danger);font-size:12px;margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:12px;margin-top:30px">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="check" style="width:14px;height:14px"></i>
            {{ $isFr ? 'Enregistrer' : 'Save' }}
          </button>
          <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline">
            <i data-lucide="x" style="width:14px;height:14px"></i>
            {{ $isFr ? 'Annuler' : 'Cancel' }}
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection
