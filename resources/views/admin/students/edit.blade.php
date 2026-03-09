{{-- admin/students/edit.blade.php --}}
@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Modifier l\'élève' : 'Edit Student')
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-light btn-sm"><i data-lucide="arrow-left" style="width:14px"></i></a>
        <div class="page-icon" style="background:linear-gradient(135deg,#0d9488,#14b8a6)"><i data-lucide="edit-2"></i></div>
        <h1 class="page-title">{{ $isFr ? 'Modifier' : 'Edit' }}: {{ $student->user->name }}</h1>
    </div>
</div>

<div class="row"><div class="col-lg-7">
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.students.update', $student->id) }}">
@csrf @method('PUT')

@if($errors->any())
<div class="alert alert-danger mb-4">@foreach($errors->all() as $err)<div style="font-size:.83rem">• {{ $err }}</div>@endforeach</div>
@endif

<div class="row gy-3">
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Nom complet' : 'Full name' }} <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $student->user->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $student->user->email) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Classe' : 'Class' }} <span class="text-danger">*</span></label>
        <select name="class_id" class="form-select" required>
            @foreach($classes as $class)
            <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Matricule</label>
        <input type="text" class="form-control" value="{{ $student->matricule }}" disabled>
        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem">{{ $isFr ? 'Non modifiable' : 'Read only' }}</div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">
        <i data-lucide="save" style="width:14px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}
    </button>
    <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-light">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
</div>
</form>
</div></div>
</div></div>
@endsection


