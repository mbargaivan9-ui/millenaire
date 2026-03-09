@extends('layouts.app')
@section('title', 'Rapport des Frais Scolaires')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Rapport des Frais Scolaires</h1>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Imprimer</button>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body"><div class="fs-2 fw-bold text-success">{{ number_format($totalCollected ?? 0, 0, ',', ' ') }} FCFA</div><div class="text-muted small">Total Collecté</div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body"><div class="fs-2 fw-bold text-danger">{{ number_format($totalPending ?? 0, 0, ',', ' ') }} FCFA</div><div class="text-muted small">En Attente</div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body"><div class="fs-2 fw-bold text-primary">{{ $totalStudents ?? 0 }}</div><div class="text-muted small">Élèves Total</div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body"><div class="fs-2 fw-bold text-warning">{{ $paymentRate ?? 0 }}%</div><div class="text-muted small">Taux de Paiement</div></div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-light"><tr><th>Classe</th><th>Élèves</th><th>Payé</th><th>Dû</th><th>Taux</th></tr></thead>
                    <tbody>
                        @forelse($classSummaries ?? [] as $row)
                        <tr>
                            <td>{{ $row->class_name }}</td>
                            <td>{{ $row->total_students }}</td>
                            <td class="text-success">{{ number_format($row->paid_amount, 0, ',', ' ') }} FCFA</td>
                            <td class="text-danger">{{ number_format($row->pending_amount, 0, ',', ' ') }} FCFA</td>
                            <td>
                                <div class="progress" style="height:8px;min-width:80px">
                                    <div class="progress-bar bg-success" style="width:{{ $row->rate }}%"></div>
                                </div>
                                <small>{{ $row->rate }}%</small>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucune donnée disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


