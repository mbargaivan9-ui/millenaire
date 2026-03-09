@extends('layouts.app')
@section('title', 'Statistiques Disciplinaires')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4">Statistiques Disciplinaires</h1>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card shadow-sm text-center"><div class="card-body"><div class="fs-2 fw-bold text-danger">{{ $total ?? 0 }}</div><div class="text-muted">Total Incidents</div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm text-center"><div class="card-body"><div class="fs-2 fw-bold text-warning">{{ $thisMonth ?? 0 }}</div><div class="text-muted">Ce Mois</div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm text-center"><div class="card-body"><div class="fs-2 fw-bold text-info">{{ $resolved ?? 0 }}</div><div class="text-muted">Résolus</div></div></div></div>
        <div class="col-md-3"><div class="card shadow-sm text-center"><div class="card-body"><div class="fs-2 fw-bold text-primary">{{ $studentsAffected ?? 0 }}</div><div class="text-muted">Élèves Concernés</div></div></div></div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header"><h6 class="mb-0">Incidents par Classe</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light"><tr><th>Classe</th><th>Nombre</th><th>Type Principal</th></tr></thead>
                    <tbody>
                        @forelse($byClass ?? [] as $row)
                        <tr>
                            <td>{{ $row->class_name }}</td>
                            <td>{{ $row->count }}</td>
                            <td>{{ $row->main_type ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Aucune donnée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
