@extends('layouts.app')
@section('title', 'Rapport de Présence')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Rapport de Présence</h1>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Imprimer</button>
    </div>
    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Classe</label>
                    <select name="class_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date de début</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date de fin</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-light"><tr><th>Élève</th><th>Classe</th><th>Présences</th><th>Absences</th><th>Retards</th><th>Taux Présence</th></tr></thead>
                    <tbody>
                        @forelse($report ?? [] as $row)
                        <tr>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ $row->class_name }}</td>
                            <td class="text-success fw-bold">{{ $row->present_count }}</td>
                            <td class="text-danger fw-bold">{{ $row->absent_count }}</td>
                            <td class="text-warning fw-bold">{{ $row->late_count ?? 0 }}</td>
                            <td>
                                <div class="progress" style="height:8px;min-width:60px">
                                    <div class="progress-bar {{ $row->rate >= 80 ? 'bg-success' : ($row->rate >= 60 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $row->rate }}%"></div>
                                </div>
                                <small>{{ $row->rate }}%</small>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucune donnée disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
