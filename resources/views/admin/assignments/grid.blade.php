{{--
    |--------------------------------------------------------------------------
    | admin/assignments/grid.blade.php — Affectations Grid par Classe
    |--------------------------------------------------------------------------
    | Affiche les affectations pour une classe spécifique en format grille
    --}}

@extends('layouts.app')

@section('title', 'Affectations - ' . ($class?->name ?? 'Toutes'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            @if($class)
                <h1 class="mb-1">Affectations de {{ $class->name }}</h1>
                <p class="text-muted mb-0">Gestion des affectations pour cette classe</p>
            @else
                <h1 class="mb-1">Grille des Affectations</h1>
                <p class="text-muted mb-0">Vue complète des affectations</p>
            @endif
        </div>
        <div>
            @if($class)
                <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
                    <i data-lucide="arrow-left" style="width:16px"></i> Retour aux classes
                </a>
            @else
                <a href="{{ route('admin.assignments.index') }}" class="btn btn-outline-secondary">
                    <i data-lucide="arrow-left" style="width:16px"></i> Retour
                </a>
            @endif
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:20%">Classe</th>
                        <th style="width:30%">Professeur</th>
                        <th style="width:25%">Matière</th>
                        <th style="width:15%">Salle</th>
                        <th style="width:10%;text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                    <tr>
                        <td>
                            <strong>{{ $assignment->class?->name ?? '—' }}</strong>
                        </td>
                        <td>
                            @if($assignment->teacher)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-xs" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%;">
                                        {{ substr($assignment->teacher->user->name ?? '', 0, 1) }}
                                    </div>
                                    <span>{{ $assignment->teacher->user->name ?? '—' }}</span>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            {{ $assignment->subject?->name ?? '—' }}
                        </td>
                        <td>
                            {{ $assignment->room ?? '—' }}
                        </td>
                        <td style="text-align:center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.assignments.edit', $assignment->id) }}" class="btn btn-outline-primary" title="Modifier">
                                    <i data-lucide="edit-2" style="width:14px"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" title="Supprimer" onclick="if(confirm('Confirmer la suppression?')) { document.getElementById('delete-form-{{ $assignment->id }}').submit(); }">
                                    <i data-lucide="trash-2" style="width:14px"></i>
                                </button>
                            </div>
                            <form id="delete-form-{{ $assignment->id }}" action="{{ route('admin.assignments.destroy', $assignment->id) }}" method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i data-lucide="inbox" style="width:32px; opacity:.3; display:block; margin:0 auto .75rem"></i>
                            Aucune affectation encontrée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Initialize Lucide icons
document.querySelectorAll('[data-lucide]').forEach(el => {
    const name = el.getAttribute('data-lucide');
    el.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></svg>`;
});
</script>
@endpush
@endsection
