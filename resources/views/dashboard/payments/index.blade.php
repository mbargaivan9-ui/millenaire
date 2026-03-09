@extends('layouts.app')

@section('title', 'Suivi des Paiements')

@section('content')
<div class="container-fluid">
    
    <div class="page-header mb-4">
        <h1 class="fw-bold text-dark">Suivi des Frais de Scolarité</h1>
        <p class="text-muted">Gérez les paiements et les dettes</p>
    </div>

    <!-- Cards de synthèse -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">Total Collecté</h6>
                            <h3 class="fw-bold text-success">{{ number_format($totalCollected, 0) }}</h3>
                        </div>
                        <i class="fas fa-coins text-success fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">Montant Dû</h6>
                            <h3 class="fw-bold text-danger">{{ number_format($totalOutstanding, 0) }}</h3>
                        </div>
                        <i class="fas fa-exclamation-circle text-danger fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">En Attente</h6>
                            <h3 class="fw-bold text-warning">{{ $pendingCount }}</h3>
                        </div>
                        <i class="fas fa-clock text-warning fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">Taux de Collecte</h6>
                            <h3 class="fw-bold text-primary">{{ $collectionRate }}%</h3>
                        </div>
                        <i class="fas fa-chart-pie text-primary fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et actions -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Nom élève..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="completed" @selected(request('status') == 'completed')>Payé</option>
                        <option value="pending" @selected(request('status') == 'pending')>En attente</option>
                        <option value="failed" @selected(request('status') == 'failed')>Échoué</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="month" class="form-select">
                        <option value="">Tous les mois</option>
                        @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" @selected(request('month') == $i)>
                            {{ \Carbon\Carbon::create(2024, $i)->format('F') }}
                        </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-filter me-2"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des paiements -->
    <div class="card border-0">
        <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0">Historique des Paiements</h5>
            <button class="btn btn-sm btn-primary">
                <i class="fas fa-download me-2"></i> Exporter
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Élève</th>
                        <th>Classe</th>
                        <th>Montant</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Reçu</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>
                            <strong>{{ $payment->student->user->name }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-light-primary">{{ $payment->student->classe->name ?? 'N/A' }}</span>
                        </td>
                        <td class="fw-bold">{{ number_format($payment->amount, 0) }}</td>
                        <td>
                            @if($payment->method === 'online')
                                <i class="fas fa-credit-card me-1 text-primary"></i> En ligne
                            @elseif($payment->method === 'cash')
                                <i class="fas fa-money-bill me-1 text-success"></i> Espèces
                            @else
                                <i class="fas fa-university me-1 text-info"></i> Virement
                            @endif
                        </td>
                        <td>
                            @if($payment->status === 'completed')
                                <span class="badge bg-success">Payé</span>
                            @elseif($payment->status === 'pending')
                                <span class="badge bg-warning">En attente</span>
                            @else
                                <span class="badge bg-danger">Échoué</span>
                            @endif
                        </td>
                        <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($payment->receipt)
                                <a href="{{ route('receipts.show', $payment->receipt->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" title="Éditer">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            Aucun paiement enregistré
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($payments->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $payments->links() }}
    </div>
    @endif

</div>
@endsection
