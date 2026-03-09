{{-- admin/teachers/create.blade.php --}}
@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Ajouter un enseignant' : 'Add Teacher')
@section('content')
@php $isFr = app()->getLocale() === 'fr'; $editing = isset($teacher); @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.teachers.index') }}" class="btn btn-light btn-sm"><i data-lucide="arrow-left" style="width:14px"></i></a>
        <div class="page-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb)"><i data-lucide="{{ $editing ? 'edit-2' : 'user-plus' }}"></i></div>
        <h1 class="page-title">{{ $editing ? ($isFr ? 'Modifier l\'enseignant' : 'Edit Teacher') : ($isFr ? 'Ajouter un enseignant' : 'Add Teacher') }}</h1>
    </div>
</div>

<div class="row"><div class="col-lg-8">
<div class="card"><div class="card-body">

<form method="POST" action="{{ $editing ? route('admin.teachers.update', $teacher->id) : route('admin.teachers.store') }}">
@csrf
@if($editing) @method('PUT') @endif

@if($errors->any())
<div class="alert alert-danger mb-4">
    @foreach($errors->all() as $err)<div style="font-size:.83rem">• {{ $err }}</div>@endforeach
</div>
@endif

<div class="row gy-3">
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Nom complet' : 'Full name' }} <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $teacher->user->name ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $teacher->user->email ?? '') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">{{ $isFr ? 'Matières enseignées' : 'Subjects taught' }} <span class="text-danger">*</span></label>
        <select name="subject_ids[]" class="form-select" multiple required style="height:100px">
            @foreach($subjects as $sub)
            <option value="{{ $sub->id }}"
                {{ in_array($sub->id, old('subject_ids', $teacher?->subjects->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                {{ $sub->name }}
            </option>
            @endforeach
        </select>
        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem">{{ $isFr ? 'Ctrl+clic pour plusieurs' : 'Ctrl+click for multiple' }}</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Qualification' : 'Qualification' }}</label>
        <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $teacher->qualification ?? '') }}" placeholder="{{ $isFr ? 'Ex: Master en Mathématiques' : 'E.g: Masters in Mathematics' }}">
    </div>
    <div class="col-md-6">
        @if($editing)
        <div class="d-flex gap-3 mt-3 pt-2">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $teacher->is_active ?? true) ? 'checked' : '' }} style="accent-color:var(--primary)">
                {{ $isFr ? 'Enseignant actif' : 'Active teacher' }}
            </label>
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem">
                <input type="checkbox" name="is_visible_on_site" value="1" {{ old('is_visible_on_site', $teacher->is_visible_on_site ?? false) ? 'checked' : '' }} style="accent-color:var(--primary)">
                {{ $isFr ? 'Visible sur le site' : 'Visible on website' }}
            </label>
        </div>
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Bio (Français)' : 'Bio (French)' }}</label>
        <textarea name="bio_fr" class="form-control" rows="3" style="resize:none" placeholder="{{ $isFr ? 'Présentation courte...' : 'Short bio...' }}">{{ old('bio_fr', $teacher->bio_fr ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Bio (English)</label>
        <textarea name="bio_en" class="form-control" rows="3" style="resize:none" placeholder="Short bio...">{{ old('bio_en', $teacher->bio_en ?? '') }}</textarea>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">
        <i data-lucide="{{ $editing ? 'save' : 'user-plus' }}" style="width:14px" class="me-1"></i>
        {{ $editing ? ($isFr ? 'Enregistrer' : 'Save') : ($isFr ? 'Créer l\'enseignant' : 'Create teacher') }}
    </button>
    <a href="{{ route('admin.teachers.index') }}" class="btn btn-light">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
</div>
</form>

</div></div>
</div></div>
@endsection


