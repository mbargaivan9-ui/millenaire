@extends('layouts.app')
@section('title', 'Notes — Administration')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Gestion des Notes</h1>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr><th>Élève</th><th>Matière</th><th>Note</th><th>Trimestre</th><th>Enseignant</th></tr>
                    </thead>
                    <tbody>
                        @forelse($grades ?? [] as $grade)
                        <tr>
                            <td>{{ $grade->student->user->name ?? '—' }}</td>
                            <td>{{ $grade->subject->name ?? '—' }}</td>
                            <td><strong>{{ $grade->score }}/20</strong></td>
                            <td>{{ $grade->term }}</td>
                            <td>{{ $grade->teacher->user->name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucune note enregistrée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


