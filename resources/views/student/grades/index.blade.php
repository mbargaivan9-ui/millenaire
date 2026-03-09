@extends('layouts.app')
@section('title', 'Mes Notes')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4">Mes Notes</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light"><tr><th>Matière</th><th>Note</th><th>Date</th><th>Trimestre</th><th>Enseignant</th></tr></thead>
                    <tbody>
                        @forelse($grades ?? [] as $grade)
                        <tr>
                            <td>{{ $grade->subject->name ?? '—' }}</td>
                            <td><span class="badge fs-6 bg-{{ ($grade->score ?? 0) >= 10 ? 'success' : 'danger' }}">{{ $grade->score }}/20</span></td>
                            <td>{{ $grade->created_at->format('d/m/Y') }}</td>
                            <td>Trim. {{ $grade->term }}</td>
                            <td>{{ $grade->teacher->user->name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucune note disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
