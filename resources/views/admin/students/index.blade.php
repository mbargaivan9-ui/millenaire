{{--
    | admin/students/index.blade.php — Gestion des Élèves
    --}}

@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Gestion des Élèves' : 'Student Management')

@push('styles')
<style>
.student-card { display:flex;align-items:center;gap:.75rem;padding:.75rem;border-radius:10px;border:1.5px solid var(--border);transition:all .15s ease; }
.student-card:hover { border-color:var(--primary);background:var(--primary-bg); }
</style>
@endpush

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#0d9488,#14b8a6)"><i data-lucide="users"></i></div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Gestion des Élèves' : 'Student Management' }}</h1>
                <p class="page-subtitle text-muted">{{ $students->total() ?? 0 }} {{ $isFr ? 'élèves inscrits' : 'enrolled students' }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.students.export') }}" class="btn btn-light btn-sm">
                <i data-lucide="download" style="width:14px" class="me-1"></i>Excel
            </a>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-sm">
                <i data-lucide="user-plus" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Ajouter' : 'Add student' }}
            </a>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
            <div style="flex:1;min-width:200px">
                <label class="form-label">{{ $isFr ? 'Rechercher' : 'Search' }}</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                       placeholder="{{ $isFr ? 'Nom, prénom, matricule...' : 'Name, ID...' }}">
            </div>
            <div style="min-width:160px">
                <label class="form-label">{{ $isFr ? 'Classe' : 'Class' }}</label>
                <select name="class_id" class="form-select">
                    <option value="">{{ $isFr ? 'Toutes' : 'All' }}</option>
                    @foreach($classes ?? [] as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:140px">
                <label class="form-label">{{ $isFr ? 'Section' : 'Section' }}</label>
                <select name="section" class="form-select">
                    <option value="">{{ $isFr ? 'Toutes' : 'All' }}</option>
                    <option value="francophone" {{ request('section') === 'francophone' ? 'selected' : '' }}>🇫🇷 Francophone</option>
                    <option value="anglophone"  {{ request('section') === 'anglophone'  ? 'selected' : '' }}>🇬🇧 Anglophone</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="search" style="width:14px" class="me-1"></i>
                    {{ $isFr ? 'Filtrer' : 'Filter' }}
                </button>
                @if(request()->hasAny(['search','class_id','section']))
                <a href="{{ route('admin.students.index') }}" class="btn btn-light ms-1">✕</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ $isFr ? 'Élève' : 'Student' }}</th>
                        <th>Matricule</th>
                        <th>{{ $isFr ? 'Classe' : 'Class' }}</th>
                        <th>{{ $isFr ? 'Section' : 'Section' }}</th>
                        <th>{{ $isFr ? 'Tuteur' : 'Guardian' }}</th>
                        <th>{{ $isFr ? 'Paiements' : 'Payments' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Actions' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;font-weight:700;font-size:.8rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    {{ strtoupper(substr($student->user->name ?? 'E', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:.85rem">{{ $student->user->display_name ?? $student->user->name }}</div>
                                    <div style="font-size:.72rem;color:var(--text-muted)">{{ $student->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><code style="font-size:.78rem">{{ $student->matricule }}</code></td>
                        <td class="fw-semibold" style="font-size:.83rem">{{ $student->classe?->name ?? '—' }}</td>
                        <td>
                            <span style="font-size:.75rem">{{ $student->classe?->section === 'anglophone' ? '🇬🇧 Anglophone' : '🇫🇷 Francophone' }}</span>
                        </td>
                        <td style="font-size:.8rem;color:var(--text-muted)">{{ $student->guardian?->user?->name ?? '—' }}</td>
                        <td>
                            @php $lastPayment = $student->payments?->last(); @endphp
                            @if($lastPayment?->status === 'success')
                                <span class="badge bg-success">✓ {{ $isFr ? 'À jour' : 'Up to date' }}</span>
                            @else
                                <span class="badge bg-danger">{{ $isFr ? 'En retard' : 'Overdue' }}</span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-xs btn-light" title="{{ $isFr ? 'Voir' : 'View' }}">
                                    <i data-lucide="eye" style="width:13px"></i>
                                </a>
                                <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-xs btn-light" title="{{ $isFr ? 'Modifier' : 'Edit' }}">
                                    <i data-lucide="edit-2" style="width:13px"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i data-lucide="users" style="width:32px;opacity:.3;display:block;margin:0 auto .75rem"></i>
                            {{ $isFr ? 'Aucun élève trouvé.' : 'No students found.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $students->withQueryString()->links() }}
        </div>
    </div>
</div>

@endsection


