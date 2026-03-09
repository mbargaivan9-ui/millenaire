@extends('layouts.app')
@section('title', 'Mes Bulletins')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4">Mes Bulletins de Notes</h1>
    <div class="row g-3">
        @forelse($bulletins ?? [] as $bulletin)
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">{{ $bulletin->term_label ?? 'Trimestre' }}</h6>
                    <div class="d-flex justify-content-between text-muted small mb-3">
                        <span>{{ $bulletin->academic_year ?? date('Y') }}-{{ (date('Y')+1) }}</span>
                        <span class="badge bg-{{ $bulletin->published ? 'success' : 'warning' }}">{{ $bulletin->published ? 'Publié' : 'En cours' }}</span>
                    </div>
                    @if($bulletin->average)
                    <div class="text-center py-2">
                        <div class="display-6 fw-bold text-{{ $bulletin->average >= 10 ? 'success' : 'danger' }}">{{ number_format($bulletin->average, 2) }}</div>
                        <small class="text-muted">Moyenne /20</small>
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('student.bulletin.view', $bulletin) }}" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-eye me-1"></i>Consulter</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12"><div class="alert alert-info">Aucun bulletin disponible pour le moment.</div></div>
        @endforelse
    </div>
</div>
@endsection
