@extends('layouts.app')
@section('title', 'Mes Devoirs')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Devoirs & Exercices</h1>
        <a href="{{ route('teacher.assignments.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Nouveau Devoir</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light"><tr><th>Titre</th><th>Classe</th><th>Matière</th><th>Échéance</th><th>Rendus</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($assignments ?? [] as $assignment)
                        <tr>
                            <td>{{ $assignment->title }}</td>
                            <td>{{ $assignment->classe->name ?? '—' }}</td>
                            <td>{{ $assignment->subject->name ?? '—' }}</td>
                            <td>{{ $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y') : '—' }}</td>
                            <td>{{ $assignment->submissions_count ?? 0 }}/{{ $assignment->total_students ?? 0 }}</td>
                            <td>
                                <a href="{{ route('teacher.assignments.details', $assignment) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun devoir créé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
