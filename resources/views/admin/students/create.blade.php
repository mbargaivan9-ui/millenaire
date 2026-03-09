{{-- admin/students/create.blade.php --}}
@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Ajouter un élève' : 'Add Student')
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.students.index') }}" class="btn btn-light btn-sm"><i data-lucide="arrow-left" style="width:14px"></i></a>
        <div class="page-icon" style="background:linear-gradient(135deg,#0d9488,#14b8a6)"><i data-lucide="user-plus"></i></div>
        <h1 class="page-title">{{ $isFr ? 'Ajouter un élève' : 'Add Student' }}</h1>
    </div>
</div>

<div class="row"><div class="col-lg-7">
<div class="card">
<div class="card-body">

<form method="POST" action="{{ route('admin.students.store') }}">
@csrf

@if($errors->any())
<div class="alert alert-danger mb-4">
    @foreach($errors->all() as $err)<div style="font-size:.83rem">• {{ $err }}</div>@endforeach
</div>
@endif

<div class="row gy-3">
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Prénom' : 'First name' }} <span class="text-danger">*</span></label>
        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Nom de famille' : 'Last name' }} <span class="text-danger">*</span></label>
        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="{{ $isFr ? 'Pour la connexion' : 'For login' }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Classe' : 'Class' }} <span class="text-danger">*</span></label>
        <select name="class_id" class="form-select" required>
            <option value="">{{ $isFr ? 'Choisir...' : 'Choose...' }}</option>
            @foreach($classes as $class)
            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Matricule</label>
        <input type="text" name="matricule" class="form-control" value="{{ old('matricule') }}" placeholder="{{ $isFr ? 'Auto-généré si vide' : 'Auto-generated if empty' }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Date de naissance' : 'Date of birth' }}</label>
        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ $isFr ? 'Sexe' : 'Gender' }}</label>
        <select name="gender" class="form-select">
            <option value="">{{ $isFr ? 'Non spécifié' : 'Not specified' }}</option>
            <option value="M" {{ old('gender') === 'M' ? 'selected' : '' }}>{{ $isFr ? 'Masculin' : 'Male' }}</option>
            <option value="F" {{ old('gender') === 'F' ? 'selected' : '' }}>{{ $isFr ? 'Féminin' : 'Female' }}</option>
        </select>
    </div>
</div>

<div class="alert alert-info mt-4" style="font-size:.82rem">
    <i data-lucide="info" style="width:14px" class="me-1"></i>
    {{ $isFr ? 'Un email de réinitialisation de mot de passe sera envoyé à l\'élève.' : 'A password reset email will be sent to the student.' }}
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">
        <i data-lucide="user-plus" style="width:14px" class="me-1"></i>{{ $isFr ? 'Créer l\'élève' : 'Create student' }}
    </button>
    <a href="{{ route('admin.students.index') }}" class="btn btn-light">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
</div>
</form>

</div></div>
</div></div>
@endsection


