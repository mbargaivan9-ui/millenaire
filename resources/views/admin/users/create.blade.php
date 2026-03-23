{{-- admin/users/create.blade.php --}}
@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Nouveau compte' : 'New Account')
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<style>
.role-selector { display: flex; gap: 0.5rem; flex-wrap: wrap; margin: 0; }
.role-btn { 
    padding: 0.6rem 1.2rem;
    border: 2px solid var(--border);
    border-radius: 25px;
    background: transparent;
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 0.875rem;
    font-weight: 600;
}
.role-btn:hover { transform: translateY(-2px); }
.role-btn.active { border-color: currentColor; }
.role-btn.active { background: rgba(99, 102, 241, 0.08); }

.section-checkbox {
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    transition: all 0.15s ease;
}
.section-checkbox:hover { border-color: #6366f1; background: rgba(99, 102, 241, 0.02); }

.permission-badge {
    display: inline-flex;
    gap: 0.25rem;
    padding: 0.25rem 0.6rem;
    background: #f0f9ff;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #0284c7;
}

.card { border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
</style>

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

<div class="row g-3">
<div class="col-lg-8">
<div class="card">
<div class="card-body">

<form method="POST" action="{{ route('admin.users.store') }}" id="userForm">
    @csrf

    @if($errors->any())
    <div class="alert alert-danger mb-4">
        @foreach($errors->all() as $err)
        <div style="font-size:.83rem">• {{ $err }}</div>
        @endforeach
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 1: RÔLE PRINCIPAL --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}

    <div class="mb-5">
        <label class="form-label fw-semibold mb-3">
            {{ $isFr ? 'Rôle Principal' : 'Main Role' }} <span class="text-danger">*</span>
        </label>
        
        <div class="role-selector">
            @php
            $mainRoles = [
                'admin'   => ['🛡️', 'Admin', '#6366f1'],
                'teacher' => ['👩‍🏫', $isFr ? 'Enseignant' : 'Teacher', '#3b82f6'],
                'parent'  => ['👨‍👩‍👦', 'Parent', '#0d9488'],
                'student' => ['🎓', $isFr ? 'Élève' : 'Student', '#f59e0b'],
            ];
            $selRole = old('role', 'student');
            @endphp
            @foreach($mainRoles as $val => [$emoji, $label, $color])
            <label class="role-btn {{ $selRole === $val ? 'active' : '' }}" style="color: {{ $color }}">
                <input type="radio" name="role" value="{{ $val }}" hidden
                       onchange="updateRoleUI(this)"
                       {{ $selRole === $val ? 'checked' : '' }}>
                <span>{{ $emoji }} {{ $label }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 2: INFORMATIONS PERSONNELLES --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}

    <div class="mb-5">
        <h6 class="text-uppercase text-muted mb-3" style="font-size:0.75rem;letter-spacing:0.5px">
            <i data-lucide="user" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Informations Personnelles' : 'Personal Information' }}
        </h6>

        <div class="row gy-3">
            <div class="col-md-6">
                <label class="form-label fw-500">{{ $isFr ? 'Nom complet' : 'Full name' }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name') }}" required
                       placeholder="{{ $isFr ? 'Nom Prénom' : 'Full Name' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-500">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email') }}" required
                       placeholder="email@example.com">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-500">{{ $isFr ? 'Mot de passe' : 'Password' }} <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control"
                       required minlength="8"
                       placeholder="{{ $isFr ? 'Min. 8 caractères' : 'Min. 8 characters' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-500">{{ $isFr ? 'Confirmer mot de passe' : 'Confirm password' }} <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-500">{{ $isFr ? 'Téléphone' : 'Phone' }}</label>
                <input type="tel" name="phone" class="form-control"
                       value="{{ old('phone') }}" placeholder="+237 6XX XXX XXX">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-500">{{ $isFr ? 'Langue' : 'Language' }}</label>
                <select name="preferred_language" class="form-select">
                    <option value="fr" {{ old('preferred_language', 'fr') === 'fr' ? 'selected' : '' }}>🇫🇷 Français</option>
                    <option value="en" {{ old('preferred_language') === 'en' ? 'selected' : '' }}>🇺🇸 English</option>
                </select>
            </div>

            {{-- Student-specific: class assignment --}}
            <div class="col-12" id="student-class-field" style="{{ $selRole === 'student' ? '' : 'display:none' }}">
                <label class="form-label fw-500">{{ $isFr ? 'Classe' : 'Class' }}</label>
                <select name="class_id" class="form-select">
                    <option value="">{{ $isFr ? '-- Choisir une classe --' : '-- Select a class --' }}</option>
                    @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 3: RÔLE SPÉCIALISÉ (ADMIN) --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}

    <div id="specialized-role-section" class="mb-5" style="display:none">
        <h6 class="text-uppercase text-muted mb-3" style="font-size:0.75rem;letter-spacing:0.5px">
            <i data-lucide="shield" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Rôle Spécialisé' : 'Specialized Role' }}
        </h6>

        <div class="mb-3">
            <label class="form-label fw-500">{{ $isFr ? 'Sélectionner un rôle' : 'Select specialized role' }}</label>
            <select name="specialized_role_id" class="form-select" onchange="updateSectionOptions(this)">
                <option value="">{{ $isFr ? '-- Aucun rôle spécialisé --' : '-- No specialized role --' }}</option>
                @foreach($specializedRoles as $role)
                <option value="{{ $role->id }}" data-code="{{ $role->code }}">
                    {{ $role->icon ?? '' }} {{ $role->name }} — {{ $role->description }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Sections Assignment --}}
        <div id="sections-container" style="display:none">
            <label class="form-label fw-500 mb-3">
                {{ $isFr ? 'Sections Accessibles' : 'Accessible Sections' }}
            </label>
            <div class="row g-2">
                @foreach($sections as $section)
                <div class="col-md-6">
                    <div class="form-check section-checkbox">
                        <input class="form-check-input" type="checkbox" name="assigned_sections[]"
                               value="{{ $section->id }}" id="section-{{ $section->id }}"
                               {{ old('assigned_sections') && in_array($section->id, (array)old('assigned_sections')) ? 'checked' : '' }}>
                        <label class="form-check-label w-100" for="section-{{ $section->id }}">
                            <div class="fw-500">{{ $section->icon ?? '📋' }} {{ $section->name }}</div>
                            <div style="font-size:0.75rem;color:var(--text-secondary)">{{ $section->description }}</div>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Role Notes --}}
        <div class="mt-3">
            <label class="form-label fw-500">{{ $isFr ? 'Notes' : 'Notes' }}</label>
            <textarea name="role_notes" class="form-control" rows="3"
                      placeholder="{{ $isFr ? 'Notes sur l\'assignation du rôle...' : 'Notes about role assignment...' }}">{{ old('role_notes') }}</textarea>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- ACTIONS --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}

    <div class="alert alert-info" style="font-size:0.85rem">
        <i data-lucide="info" style="width:14px" class="me-1"></i>
        {{ $isFr
            ? 'Un email d\'activation sera envoyé. L\'utilisateur devra changer son mot de passe à la première connexion.'
            : 'An activation email will be sent. User must change password on first login.' }}
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i data-lucide="save" style="width:16px" class="me-1"></i>
            {{ $isFr ? 'Créer le compte' : 'Create Account' }}
        </button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            {{ $isFr ? 'Annuler' : 'Cancel' }}
        </a>
    </div>
</form>

</div>
</div>
</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- SIDEBAR: QUICK INFO --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}

<div class="col-lg-4">
<div class="card mb-3">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i data-lucide="info" style="width:16px" class="me-1"></i>
            {{ $isFr ? 'À savoir' : 'Quick Info' }}
        </h6>
    </div>
    <div class="card-body" style="font-size:0.85rem">
        <div class="mb-3">
            <strong>{{ $isFr ? '🛡️ Admin' : '🛡️ Admin' }}</strong>
            <div style="color:var(--text-secondary)">{{ $isFr ? 'Accès complet au système' : 'Full system access' }}</div>
        </div>
        <div class="mb-3">
            <strong>👩‍🏫 {{ $isFr ? 'Enseignant' : 'Teacher' }}</strong>
            <div style="color:var(--text-secondary)">{{ $isFr ? 'Gestion des classes et notes' : 'Class and grade management' }}</div>
        </div>
        <div class="mb-3">
            <strong>👨‍👩‍👦 Parent</strong>
            <div style="color:var(--text-secondary)">{{ $isFr ? 'Suivi des enfants' : 'Child monitoring' }}</div>
        </div>
        <div>
            <strong>🎓 {{ $isFr ? 'Élève' : 'Student' }}</strong>
            <div style="color:var(--text-secondary)">{{ $isFr ? 'Consultation des bulletins' : 'View grades and bulletins' }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i data-lucide="shield-alert" style="width:16px" class="me-1"></i>
            {{ $isFr ? 'Rôles Spécialisés' : 'Specialized Roles' }}
        </h6>
    </div>
    <div class="card-body" style="font-size:0.85rem;max-height:400px;overflow-y:auto">
        @foreach($specializedRoles as $role)
        <div class="mb-3 pb-3" style="border-bottom:1px solid var(--border)">
            <div class="fw-600" style="color:{{ $role->color ?? '#666' }}">
                {{ $role->icon ?? '📋' }} {{ $role->name }}
            </div>
            <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:0.25rem">
                {{ $role->description }}
            </div>
        </div>
        @endforeach
    </div>
</div>
</div>

</div>

<script>
function updateRoleUI(radio) {
    const role = radio.value;
    
    // Update role display
    document.querySelectorAll('.role-btn').forEach(el => {
        el.classList.remove('active');
    });
    radio.closest('.role-btn').classList.add('active');
    
    // Show/hide role-specific fields
    document.getElementById('student-class-field').style.display = 
        role === 'student' ? '' : 'none';
    
    document.getElementById('specialized-role-section').style.display = 
        role === 'admin' ? '' : 'none';
}

function updateSectionOptions(select) {
    const container = document.getElementById('sections-container');
    if (select.value) {
        container.style.display = '';
    } else {
        container.style.display = 'none';
    }
}

// Init on page load
document.addEventListener('DOMContentLoaded', function() {
    const roleInput = document.querySelector('input[name="role"]:checked');
    if (roleInput) {
        updateRoleUI(roleInput);
    }
    
    const specializationSelect = document.querySelector('select[name="specialized_role_id"]');
    if (specializationSelect && specializationSelect.value) {
        updateSectionOptions(specializationSelect);
    }
});
</script>

@endsection
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


