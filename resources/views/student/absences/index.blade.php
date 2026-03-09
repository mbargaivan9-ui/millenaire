@extends('layouts.app')
@section('title', 'Mes Absences')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4">Mes Absences</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light"><tr><th>Date</th><th>Statut</th><th>Justifiée</th><th>Motif</th></tr></thead>
                    <tbody>
                        @forelse($absences ?? [] as $absence)
                        <tr>
                            <td>{{ $absence->date }}</td>
                            <td><span class="badge bg-{{ $absence->status === 'present' ? 'success' : 'danger' }}">{{ $absence->status }}</span></td>
                            <td>{{ $absence->justified ? '<span class="text-success">Oui</span>' : '<span class="text-danger">Non</span>' }}</td>
                            <td>{{ $absence->reason ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Aucune absence enregistrée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
