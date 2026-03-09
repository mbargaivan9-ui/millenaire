@extends('layouts.app')
@section('title', 'Détails du Devoir')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('teacher.assignments') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h4 mb-0">{{ $assignment->title ?? 'Devoir' }}</h1>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Classe</dt><dd>{{ $assignment->classe->name ?? '—' }}</dd>
                        <dt>Matière</dt><dd>{{ $assignment->subject->name ?? '—' }}</dd>
                        <dt>Créé le</dt><dd>{{ $assignment->created_at->format('d/m/Y') }}</dd>
                        <dt>Échéance</dt><dd>{{ $assignment->due_date ? \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y') : '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header"><h6 class="mb-0">Description</h6></div>
                <div class="card-body">{{ $assignment->description ?? 'Aucune description.' }}</div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header"><h6 class="mb-0">Rendus ({{ ($assignment->submissions ?? collect())->count() }}/{{ $assignment->total_students ?? 0 }})</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Élève</th><th>Date Rendu</th><th>Note</th></tr></thead>
                            <tbody>
                                @forelse($assignment->submissions ?? [] as $sub)
                                <tr>
                                    <td>{{ $sub->student->user->name ?? '—' }}</td>
                                    <td>{{ $sub->submitted_at ? \Carbon\Carbon::parse($sub->submitted_at)->format('d/m/Y H:i') : '—' }}</td>
                                    <td>{{ $sub->grade ? $sub->grade.'/20' : 'Non noté' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted">Aucun rendu.</td></tr>
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
