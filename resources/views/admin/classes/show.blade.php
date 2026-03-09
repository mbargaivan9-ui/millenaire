{{--
    | admin/classes/show.blade.php — Afficher les détails d'une classe
    --}}
@extends('layouts.app')
@section('title', 'Détails de la classe - ' . $classe->name)

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i data-lucide="grid-3x3"></i></div>
            <div>
                <h1 class="page-title">{{ $classe->name }}</h1>
                <p class="page-subtitle">{{ $isFr ? 'Détails et gestion' : 'Details and management' }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.classes.edit', $classe) }}" class="btn btn-primary btn-sm">
                <i data-lucide="edit-2" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Éditer' : 'Edit' }}
            </a>
            <a href="{{ route('admin.classes.index') }}" class="btn btn-light btn-sm">
                <i data-lucide="arrow-left" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Retour' : 'Back' }}
            </a>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
    {{-- Section Principal --}}
    <div>
        <div class="card">
            <div class="card-header">
                <i data-lucide="info" style="width:16px;height:16px"></i>
                <span>{{ $isFr ? 'Informations Générales' : 'General Information' }}</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label style="font-size:.85rem;color:var(--text-muted);font-weight:500">{{ $isFr ? 'Nom' : 'Name' }}</label>
                    <div style="font-size:1.1rem;font-weight:600">{{ $classe->name }}</div>
                </div>

                <div class="mb-3">
                    <label style="font-size:.85rem;color:var(--text-muted);font-weight:500">{{ $isFr ? 'Section' : 'Section' }}</label>
                    <div>
                        @if($classe->section === 'anglophone')
                            <span class="badge" style="background:#0066cc;color:white">🇬🇧 Anglophone</span>
                        @else
                            <span class="badge" style="background:#ff6b35;color:white">🇫🇷 Francophone</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label style="font-size:.85rem;color:var(--text-muted);font-weight:500">{{ $isFr ? 'Capacité maximale' : 'Max Capacity' }}</label>
                    <div style="font-size:1rem">{{ $classe->capacity ?? '—' }}</div>
                </div>

                <div class="mb-3">
                    <label style="font-size:.85rem;color:var(--text-muted);font-weight:500">{{ $isFr ? 'Professeur Principal' : 'Head Teacher' }}</label>
                    <div style="font-size:1rem">
                        @if($classe->profPrincipal)
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--light);display:flex;align-items:center;justify-content:center;font-size:.8rem">
                                    {{ substr($classe->profPrincipal->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight:500">{{ $classe->profPrincipal->user->name }}</div>
                                    <div style="font-size:.75rem;color:var(--text-muted)">{{ $classe->profPrincipal->user->email }}</div>
                                </div>
                            </div>
                        @else
                            <span style="color:var(--text-muted)">{{ $isFr ? 'Non assigné' : 'Not assigned' }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistiques --}}
    <div>
        <div class="card">
            <div class="card-header">
                <i data-lucide="bar-chart-3" style="width:16px;height:16px"></i>
                <span>{{ $isFr ? 'Statistiques' : 'Statistics' }}</span>
            </div>
            <div class="card-body">
                <div class="stat-item" style="padding:15px 0;border-bottom:1px solid var(--light)">
                    <div style="font-size:.85rem;color:var(--text-muted)">{{ $isFr ? 'Nombre d\'élèves' : 'Number of Students' }}</div>
                    <div style="font-size:1.4rem;font-weight:600;margin-top:5px">
                        <span>{{ $classe->students->count() }}</span>
                        @if($classe->capacity)
                            <span style="font-size:.85rem;color:var(--text-muted)">/{{ $classe->capacity }}</span>
                        @endif
                    </div>
                    @if($classe->capacity)
                        <div style="font-size:.75rem;color:var(--text-muted);margin-top:5px">
                            {{ round(($classe->students->count() / $classe->capacity) * 100) }}% {{ $isFr ? 'occupée' : 'occupied' }}
                        </div>
                    @endif
                </div>

                <div class="stat-item" style="padding:15px 0;border-bottom:1px solid var(--light)">
                    <div style="font-size:.85rem;color:var(--text-muted)">{{ $isFr ? 'Matières enseignées' : 'Subjects Taught' }}</div>
                    <div style="font-size:1.4rem;font-weight:600;margin-top:5px">
                        {{ $classe->classSubjectTeachers->count() }}
                    </div>
                </div>

                <div class="stat-item" style="padding:15px 0">
                    <div style="font-size:.85rem;color:var(--text-muted)">{{ $isFr ? 'Enseignants' : 'Teachers' }}</div>
                    <div style="font-size:1.4rem;font-weight:600;margin-top:5px">
                        {{ $classe->classSubjectTeachers->pluck('teacher_id')->unique()->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Élèves --}}
@if($classe->students->count() > 0)
<div class="card">
    <div class="card-header">
        <i data-lucide="users" style="width:16px;height:16px"></i>
        <span>{{ $isFr ? 'Élèves inscrits' : 'Enrolled Students' }} ({{ $classe->students->count() }})</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ $isFr ? 'Nom complet' : 'Full Name' }}</th>
                        <th>{{ $isFr ? 'Email' : 'Email' }}</th>
                        <th>{{ $isFr ? 'Téléphone' : 'Phone' }}</th>
                        <th>{{ $isFr ? 'Statut' : 'Status' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classe->students as $student)
                    <tr>
                        <td class="fw-500">{{ $student->user->name }}</td>
                        <td style="font-size:.85rem">{{ $student->user->email }}</td>
                        <td style="font-size:.85rem">{{ $student->user->phone ?? '—' }}</td>
                        <td>
                            @if($student->user->is_active)
                                <span class="badge" style="background:#28a745">{{ $isFr ? 'Actif' : 'Active' }}</span>
                            @else
                                <span class="badge" style="background:#dc3545">{{ $isFr ? 'Inactif' : 'Inactive' }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Enseignants & Matières --}}
@if($classe->classSubjectTeachers->count() > 0)
<div class="card" style="margin-top:20px">
    <div class="card-header">
        <i data-lucide="book-open" style="width:16px;height:16px"></i>
        <span>{{ $isFr ? 'Affectations Enseignants/Matières' : 'Teacher/Subject Assignments' }} ({{ $classe->classSubjectTeachers->count() }})</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ $isFr ? 'Matière' : 'Subject' }}</th>
                        <th>{{ $isFr ? 'Enseignant' : 'Teacher' }}</th>
                        <th>{{ $isFr ? 'Email' : 'Email' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classe->classSubjectTeachers as $cst)
                    <tr>
                        <td class="fw-500">{{ $cst->subject?->name ?? '—' }}</td>
                        <td>{{ $cst->teacher?->user?->name ?? '—' }}</td>
                        <td style="font-size:.85rem">{{ $cst->teacher?->user?->email ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Actions Dangereuses --}}
<div class="card" style="margin-top:20px;border-left:4px solid #dc3545">
    <div class="card-header" style="background:#fff5f5">
        <i data-lucide="alert-circle" style="width:16px;height:16px;color:#dc3545"></i>
        <span style="color:#dc3545">{{ $isFr ? 'Actions Dangereuses' : 'Dangerous Actions' }}</span>
    </div>
    <div class="card-body">
        <p style="font-size:.9rem;color:var(--text-muted);margin-bottom:15px">
            {{ $isFr ? 'Ces actions sont irréversibles. Procédez avec précaution.' : 'These actions are irreversible. Proceed with caution.' }}
        </p>
        
        @if($classe->students->count() === 0)
            <form method="POST" action="{{ route('admin.classes.destroy', $classe) }}" 
                  onsubmit="return confirm('{{ $isFr ? 'Êtes-vous sûr de vouloir supprimer cette classe ?' : 'Are you sure you want to delete this class?' }}');" 
                  style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i data-lucide="trash-2" style="width:14px" class="me-1"></i>
                    {{ $isFr ? 'Supprimer cette classe' : 'Delete this class' }}
                </button>
            </form>
        @else
            <div style="padding:10px;background:#fff3cd;border-radius:var(--radius-sm);color:#856404;font-size:.85rem">
                <i data-lucide="lock" style="width:14px;vertical-align:middle;margin-right:5px"></i>
                {{ $isFr ? 'Cette classe contient des élèves et ne peut pas être supprimée.' : 'This class has students and cannot be deleted.' }}
            </div>
        @endif
    </div>
</div>

@endsection
