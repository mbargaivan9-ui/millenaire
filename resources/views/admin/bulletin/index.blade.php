@extends('layouts.app')

@section('title', __('admin.bulletins'))

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-file-pdf text-danger me-2"></i>
                @if(app()->getLocale() === 'fr')
                    Gestion des Bulletins
                @else
                    Bulletin Management
                @endif
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.bulletins.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-sync"></i>
                @if(app()->getLocale() === 'fr')
                    Rafraîchir
                @else
                    Refresh
                @endif
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.bulletins.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">
                        @if(app()->getLocale() === 'fr')
                            Statut
                        @else
                            Status
                        @endif
                    </label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">— {{ __('All') }} —</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>
                            @if(app()->getLocale() === 'fr')
                                En rédaction
                            @else
                                Draft
                            @endif
                        </option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>
                            @if(app()->getLocale() === 'fr')
                                Soumis
                            @else
                                Submitted
                            @endif
                        </option>
                        <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>
                            @if(app()->getLocale() === 'fr')
                                Validé
                            @else
                                Validated
                            @endif
                        </option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>
                            @if(app()->getLocale() === 'fr')
                                Publié
                            @else
                                Published
                            @endif
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        @if(app()->getLocale() === 'fr')
                            Classe
                        @else
                            Class
                        @endif
                    </label>
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">— {{ __('All') }} —</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">
                        @if(app()->getLocale() === 'fr')
                            Rechercher
                        @else
                            Search
                        @endif
                    </label>
                    <input type="text" name="search" class="form-control form-control-sm" 
                        placeholder="Élève, classe..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('admin.bulletins.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulletins Table --}}
    @if($bulletins->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>@if(app()->getLocale() === 'fr') Élève @else Student @endif</th>
                            <th>@if(app()->getLocale() === 'fr') Classe @else Class @endif</th>
                            <th>@if(app()->getLocale() === 'fr') Période @else Period @endif</th>
                            <th>@if(app()->getLocale() === 'fr') Statut @else Status @endif</th>
                            <th>@if(app()->getLocale() === 'fr') Moyenne @else Average @endif</th>
                            <th>@if(app()->getLocale() === 'fr') Rang @else Rank @endif</th>
                            <th>@if(app()->getLocale() === 'fr') Créé @else Created @endif</th>
                            <th>@if(app()->getLocale() === 'fr') Actions @else Actions @endif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bulletins as $bulletin)
                            <tr>
                                <td>
                                    <strong>{{ $bulletin->student->user->name ?? '—' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $bulletin->student->matricule ?? '—' }}</small>
                                </td>
                                <td>{{ $bulletin->student->classe?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        T{{ $bulletin->term }}/S{{ $bulletin->sequence ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'submitted' => 'warning',
                                            'validated' => 'info',
                                            'published' => 'success',
                                        ];
                                        $color = $statusColors[$bulletin->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ $bulletin->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $bulletin->moyenne ? number_format($bulletin->moyenne, 2) : '—' }}</strong>/20
                                </td>
                                <td>
                                    @if($bulletin->rang)
                                        <span class="badge bg-primary">n°{{ $bulletin->rang }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $bulletin->created_at?->format('d/m/Y') ?? '—' }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.bulletins.show', $bulletin->id) }}" 
                                            class="btn btn-outline-primary" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($bulletin->status === 'submitted')
                                            <form action="{{ route('admin.bulletins.validate', $bulletin->id) }}" 
                                                method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Valider">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectModal{{ $bulletin->id }}"
                                                title="Rejeter">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Reject Modal --}}
                            @if($bulletin->status === 'submitted')
                                <div class="modal fade" id="rejectModal{{ $bulletin->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    @if(app()->getLocale() === 'fr')
                                                        Rejeter le bulletin
                                                    @else
                                                        Reject bulletin
                                                    @endif
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.bulletins.reject', $bulletin->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">
                                                            @if(app()->getLocale() === 'fr')
                                                                Raison du rejet
                                                            @else
                                                                Reason for rejection
                                                            @endif
                                                        </label>
                                                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        @if(app()->getLocale() === 'fr') Annuler @else Cancel @endif
                                                    </button>
                                                    <button type="submit" class="btn btn-danger">
                                                        @if(app()->getLocale() === 'fr') Rejeter @else Reject @endif
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $bulletins->links() }}
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            @if(app()->getLocale() === 'fr')
                Aucun bulletin trouvé.
            @else
                No bulletins found.
            @endif
        </div>
    @endif
</div>
@endsection
