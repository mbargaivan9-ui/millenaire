@extends('layouts.app')
@section('title', 'Détails de l\'Enfant')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('parent.dashboard') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h4 mb-0">{{ $student->user->name ?? 'Enfant' }}</h1>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px">
                        <span class="text-white fs-2">{{ substr($student->user->name ?? 'E', 0, 1) }}</span>
                    </div>
                    <h5>{{ $student->user->name ?? '—' }}</h5>
                    <p class="text-muted">{{ $student->classe->name ?? 'Classe non assignée' }}</p>
                    <p class="small text-muted">Matricule : {{ $student->matricule ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header"><h6 class="mb-0">Résultats Récents</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Matière</th><th>Note</th><th>Date</th></tr></thead>
                            <tbody>
                                @forelse($recentMarks ?? [] as $mark)
                                <tr>
                                    <td>{{ $mark->subject->name ?? '—' }}</td>
                                    <td><span class="badge bg-{{ $mark->score >= 10 ? 'success' : 'danger' }}">{{ $mark->score }}/20</span></td>
                                    <td>{{ $mark->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted">Aucune note récente.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header"><h6 class="mb-0">Absences Récentes</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Date</th><th>Statut</th><th>Justifiée</th></tr></thead>
                            <tbody>
                                @forelse($recentAbsences ?? [] as $absence)
                                <tr>
                                    <td>{{ $absence->date }}</td>
                                    <td>{{ $absence->status }}</td>
                                    <td>{{ $absence->justified ? 'Oui' : 'Non' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted">Aucune absence.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
