{{-- admin/users/create.blade.php --}}
@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Nouveau compte' : 'New Account')
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px"></i>
        </a>
        <div class="page-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5)">
            <i data-lucide="user-plus"></i>
        </div>
        <h1 class="page-title">{{ $isFr ? 'Nouveau compte utilisateur' : 'New User Account' }}</h1>
    </div>
</div>

<div class="row">
<div class="col-lg-7">
<div class="card">
<div class="card-body">

<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf

    @if($errors->any())
    <div class="alert alert-danger mb-4">
        @foreach($errors->all() as $err)
        <div style="font-size:.83rem">• {{ $err }}</div>
        @endforeach
    </div>
    @endif

    {{-- Role selector --}}
    <div class="mb-4">
        <label class="form-label fw-semibold">
            {{ $isFr ? 'Rôle' : 'Role' }} <span class="text-danger">*</span>
        </label>
        <div class="d-flex gap-2 flex-wrap">
            @php
            $roles = [
                'admin'   => ['🛡️', 'Admin',                    '#6366f1'],
                'teacher' => ['👩‍🏫', $isFr ? 'Enseignant' : 'Teacher', '#3b82f6'],
                'parent'  => ['👨‍👩‍👦', 'Parent',                   '#0d9488'],
                'student' => ['🎓', $isFr ? 'Élève' : 'Student', '#f59e0b'],
            ];
            $selRole = old('role', 'student');
            @endphp
            @foreach($roles as $val => [$emoji, $label, $color])
            <label style="cursor:pointer" onclick="selectRole('{{ $val }}')">
                <input type="radio" name="role" value="{{ $val }}" hidden
                       {{ $selRole === $val ? 'checked' : '' }}>
                <span id="role-pill-{{ $val }}"
                      style="display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border:1.5px solid {{ $selRole === $val ? $color : 'var(--border)' }};border-radius:20px;font-size:.82rem;font-weight:700;transition:all .15s;background:{{ $selRole === $val ? $color.'18' : 'transparent' }};color:{{ $selRole === $val ? $color : 'var(--text-secondary)' }}">
                    {{ $emoji }} {{ $label }}
                </span>
            </label>
            @endforeach
        </div>
    </div>

    <div class="row gy-3">
        <div class="col-md-6">
            <label class="form-label">{{ $isFr ? 'Nom complet' : 'Full name' }} <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name') }}" required
                   placeholder="{{ $isFr ? 'Nom Prénom' : 'Full Name' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ $isFr ? 'Mot de passe' : 'Password' }} <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control"
                   required minlength="8"
                   placeholder="{{ $isFr ? 'Min. 8 caractères' : 'Min. 8 characters' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ $isFr ? 'Confirmer' : 'Confirm password' }} <span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ $isFr ? 'Téléphone' : 'Phone' }}</label>
            <input type="tel" name="phone" class="form-control"
                   value="{{ old('phone') }}" placeholder="+237 6XX XXX XXX">
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ $isFr ? 'Langue préférée' : 'Preferred language' }}</label>
            <select name="preferred_language" class="form-select">
                <option value="fr" {{ old('preferred_language', 'fr') === 'fr' ? 'selected' : '' }}>🇫🇷 Français</option>
                <option value="en" {{ old('preferred_language') === 'en' ? 'selected' : '' }}>🇺🇸 English</option>
            </select>
        </div>

        {{-- Student-specific: class assignment --}}
        <div class="col-12" id="class-field" style="{{ old('role','student') === 'student' ? '' : 'display:none' }}">
            <label class="form-label">{{ $isFr ? 'Classe (pour élève)' : 'Class (for student)' }}</label>
            <select name="class_id" class="form-select">
                <option value="">{{ $isFr ? 'Choisir...' : 'Choose...' }}</option>
                @foreach($classes as $c)
                <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="alert alert-info mt-4" style="font-size:.82rem">
        <i data-lucide="info" style="width:14px" class="me-1"></i>
        {{ $isFr
            ? 'Le compte sera créé immédiatement. Partagez les identifiants avec l\'utilisateur.'
            : 'The account will be created immediately. Share the credentials with the user.' }}
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
            <i data-lucide="user-plus" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Créer le compte' : 'Create account' }}
        </button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-light">
            {{ $isFr ? 'Annuler' : 'Cancel' }}
        </a>
    </div>
</form>

</div>
</div>
</div>
</div>

@endsection

@push('scripts')
<script>
const roleColors = {
    admin:   '#6366f1',
    teacher: '#3b82f6',
    parent:  '#0d9488',
    student: '#f59e0b',
};

window.selectRole = function(val) {
    Object.keys(roleColors).forEach(r => {
        const pill = document.getElementById(`role-pill-${r}`);
        if (!pill) return;
        if (r === val) {
            pill.style.borderColor = roleColors[r];
            pill.style.background  = roleColors[r] + '18';
            pill.style.color       = roleColors[r];
        } else {
            pill.style.borderColor = 'var(--border)';
            pill.style.background  = 'transparent';
            pill.style.color       = 'var(--text-secondary)';
        }
    });
    document.querySelector(`[name="role"][value="${val}"]`).checked = true;
    // Show/hide class field
    document.getElementById('class-field').style.display = val === 'student' ? '' : 'none';
};
</script>
@endpush


