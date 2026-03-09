@extends('layouts.app')
@section('title', 'Tableau de Bord Comptable')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4"><i class="bi bi-calculator text-primary me-2"></i>Espace Comptabilité</h1>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="fs-2 fw-bold text-success">{{ number_format($totalCollected ?? 0, 0, ',', ' ') }} FCFA</div>
                    <div class="text-muted small">Total Collecté</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="fs-2 fw-bold text-warning">{{ number_format($pending ?? 0, 0, ',', ' ') }} FCFA</div>
                    <div class="text-muted small">En Attente</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header"><h6 class="mb-0">Derniers Paiements</h6></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Élève</th><th>Montant</th><th>Méthode</th><th>Statut</th></tr></thead>
                    <tbody>
                        @forelse($recentPayments ?? [] as $p)
                        <tr>
                            <td>{{ $p->created_at->format('d/m/Y') }}</td>
                            <td>{{ $p->student->user->name ?? '—' }}</td>
                            <td>{{ number_format($p->amount, 0, ',', ' ') }} FCFA</td>
                            <td>{{ $p->method ?? '—' }}</td>
                            <td><span class="badge bg-{{ $p->status === 'paid' ? 'success' : 'warning' }}">{{ $p->status }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucun paiement récent.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
