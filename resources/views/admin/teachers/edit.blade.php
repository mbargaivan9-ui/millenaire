{{-- admin/teachers/edit.blade.php --}}
@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Modifier l\'enseignant' : 'Edit Teacher')
@section('content')
@php $isFr = app()->getLocale() === 'fr'; $editing = true; @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.teachers.show', $teacher->id) }}" class="btn btn-light btn-sm"><i data-lucide="arrow-left" style="width:14px"></i></a>
        <div class="page-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb)"><i data-lucide="edit-2"></i></div>
        <h1 class="page-title">{{ $isFr ? 'Modifier' : 'Edit' }}: {{ $teacher->user->name }}</h1>
    </div>
</div>

<div class="row"><div class="col-lg-8">
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.teachers.update', $teacher->id) }}">
@csrf @method('PUT')

@if($errors->any())
<div class="alert alert-danger mb-4">@foreach($errors->all() as $err)<div style="font-size:.83rem">• {{ $err }}</div>@endforeach</div>
@endif

<div class="row gy-3">
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Nom complet' : 'Full name' }} <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $teacher->user->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $teacher->user->email) }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">{{ $isFr ? 'Matières enseignées' : 'Subjects taught' }} <span class="text-danger">*</span></label>
        <select name="subject_ids[]" class="form-select" multiple required style="height:100px">
            @foreach($subjects as $sub)
            <option value="{{ $sub->id }}" {{ in_array($sub->id, old('subject_ids', $teacher->subjects->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $sub->name }}</option>
            @endforeach
        </select>
        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem">{{ $isFr ? 'Ctrl+clic pour plusieurs' : 'Ctrl+click for multiple' }}</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Qualification' : 'Qualification' }}</label>
        <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $teacher->qualification) }}">
    </div>
    <div class="col-md-6">
        <div class="d-flex gap-3 mt-3 pt-2">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $teacher->is_active) ? 'checked' : '' }} style="accent-color:var(--primary)">
                {{ $isFr ? 'Enseignant actif' : 'Active' }}
            </label>
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem">
                <input type="checkbox" name="is_visible_on_site" value="1" {{ old('is_visible_on_site', $teacher->is_visible_on_site) ? 'checked' : '' }} style="accent-color:var(--primary)">
                {{ $isFr ? 'Visible site' : 'On website' }}
            </label>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Bio (Français)' : 'Bio (French)' }}</label>
        <textarea name="bio_fr" class="form-control" rows="3" style="resize:none">{{ old('bio_fr', $teacher->bio_fr) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Bio (English)</label>
        <textarea name="bio_en" class="form-control" rows="3" style="resize:none">{{ old('bio_en', $teacher->bio_en) }}</textarea>
    </div>
</div>
<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:14px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</button>
    <a href="{{ route('admin.teachers.show', $teacher->id) }}" class="btn btn-light">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
</div>
</form>
</div></div>
</div></div>
@endsection


