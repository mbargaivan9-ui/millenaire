@extends('layouts.app')

@section('title', __('student.bulletins'))

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-file-pdf text-danger me-2"></i>
                @if(app()->getLocale() === 'fr')
                    Mes Bulletins
                @else
                    My Bulletins
                @endif
            </h1>
        </div>
    </div>

    @if($bulletins->count() > 0)
        <div class="row">
            @foreach($bulletins as $bulletin)
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 hover">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">
                                        @if(app()->getLocale() === 'fr')
                                            Trimestre {{ $bulletin->term }} - Séquence {{ $bulletin->sequence ?? '-' }}
                                        @else
                                            Term {{ $bulletin->term }} - Sequence {{ $bulletin->sequence ?? '-' }}
                                        @endif
                                    </h5>
                                    <p class="card-text text-muted small">
                                        {{ $bulletin->student->classe?->name ?? '—' }}
                                    </p>
                                </div>
                                @if($bulletin->moyenne)
                                    <div class="text-end">
                                        <div class="fs-5 fw-bold text-primary">
                                            {{ number_format($bulletin->moyenne, 2) }}
                                        </div>
                                        <small class="text-muted">/20</small>
                                    </div>
                                @endif
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <small class="text-muted">
                                        @if(app()->getLocale() === 'fr')
                                            Rang
                                        @else
                                            Rank
                                        @endif
                                    </small>
                                    <div class="fw-bold">
                                        @if($bulletin->rang)
                                            n°{{ $bulletin->rang }}
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        @if(app()->getLocale() === 'fr')
                                            Appréciation
                                        @else
                                            Assessment
                                        @endif
                                    </small>
                                    <div class="fw-bold" style="color: {{ $bulletin->appreciation_color ?? '#94a3b8' }}">
                                        {{ $bulletin->appreciation ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            <small class="text-muted">
                                @if(app()->getLocale() === 'fr')
                                    Publié le {{ $bulletin->published_at?->format('d/m/Y') ?? '—' }}
                                @else
                                    Published {{ $bulletin->published_at?->format('m/d/Y') ?? '—' }}
                                @endif
                            </small>

                            <div class="mt-3 pt-3 border-top d-flex gap-2">
                                <a href="{{ route('student.bulletins.show', $bulletin->id) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="fas fa-eye me-1"></i>
                                    @if(app()->getLocale() === 'fr') Voir @else View @endif
                                </a>
                                <a href="{{ route('student.bulletins.pdf', $bulletin->id) }}" class="btn btn-sm btn-danger flex-grow-1">
                                    <i class="fas fa-download me-1"></i>
                                    @if(app()->getLocale() === 'fr') PDF @else PDF @endif
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $bulletins->links() }}
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            @if(app()->getLocale() === 'fr')
                Aucun bulletin publié pour le moment.
            @else
                No bulletins published yet.
            @endif
        </div>
    @endif
</div>

<style>
    .hover {
        transition: all 0.3s ease;
    }

    .hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endsection
