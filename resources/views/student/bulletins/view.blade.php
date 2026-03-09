@extends('layouts.app')
@section('title', 'Bulletin de Notes')
@section('content')
<div class="container-fluid py-4" style="max-width:900px">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('student.bulletins') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
            <h1 class="h4 mb-0">Bulletin — {{ $bulletin->term_label ?? 'Trimestre' }}</h1>
        </div>
        <a href="{{ route('student.bulletin.pdf', $bulletin) }}" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-pdf me-1"></i>PDF</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr><th>Matière</th><th>Note /20</th><th>Coeff</th><th>Points</th><th>Rang</th><th>Appréciation</th></tr>
                    </thead>
                    <tbody>
                        @forelse($bulletin->entries ?? [] as $entry)
                        <tr>
                            <td>{{ $entry->subject->name ?? '—' }}</td>
                            <td class="fw-bold text-{{ ($entry->score ?? 0) >= 10 ? 'success' : 'danger' }} text-center">{{ number_format($entry->score ?? 0, 2) }}</td>
                            <td class="text-center">{{ $entry->coefficient ?? 1 }}</td>
                            <td class="text-center">{{ number_format(($entry->score ?? 0) * ($entry->coefficient ?? 1), 2) }}</td>
                            <td class="text-center">{{ $entry->rank ?? '—' }}</td>
                            <td>{{ $entry->appreciation ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucune note.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Moyenne Générale</td>
                            <td class="text-center text-{{ ($bulletin->average ?? 0) >= 10 ? 'success' : 'danger' }}">{{ number_format($bulletin->average ?? 0, 2) }}</td>
                            <td colspan="2"></td>
                            <td class="text-center">Rang : {{ $bulletin->rank ?? '—' }}/{{ $bulletin->class_size ?? '—' }}</td>
                            <td>{{ $bulletin->appreciation ?? '—' }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
