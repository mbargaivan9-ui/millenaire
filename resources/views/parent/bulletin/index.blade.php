@extends('layouts.app')

@section('title', __('parent.bulletins'))

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-file-pdf text-danger me-2"></i>
                @if(app()->getLocale() === 'fr')
                    Bulletins de Mes Enfants
                @else
                    My Children's Bulletins
                @endif
            </h1>
        </div>
    </div>

    @if($bulletins->count() > 0)
        {{-- Bulletins by Child --}}
        <div class="row">
            @php
                $bulletinsByChild = $bulletins->groupBy('student_id');
            @endphp

            @foreach($bulletinsByChild as $studentId => $childBulletins)
                @php
                    $child = $childBulletins->first()->student;
                @endphp
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-0">
                            <h5 class="mb-0">
                                <i class="fas fa-user-graduate text-primary me-2"></i>
                                {{ $child->user->name ?? '—' }}
                            </h5>
                            <small class="text-muted">{{ $child->classe?->name ?? '—' }}</small>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @foreach($childBulletins->sortByDesc('term')->sortByDesc('sequence') as $bulletin)
                                    <a href="{{ route('parent.bulletins.show', $bulletin->id) }}" 
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <h6 class="mb-1">
                                                @if(app()->getLocale() === 'fr')
                                                    Trimestre {{ $bulletin->term }} - Séquence {{ $bulletin->sequence ?? '-' }}
                                                @else
                                                    Term {{ $bulletin->term }} - Sequence {{ $bulletin->sequence ?? '-' }}
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                @if(app()->getLocale() === 'fr')
                                                    Publié le {{ $bulletin->published_at?->format('d/m/Y') ?? '—' }}
                                                @else
                                                    Published {{ $bulletin->published_at?->format('m/d/Y') ?? '—' }}
                                                @endif
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            @if($bulletin->moyenne)
                                                <span class="badge bg-primary">
                                                    {{ number_format($bulletin->moyenne, 2) }}/20
                                                </span>
                                            @endif
                                            <i class="fas fa-chevron-right text-muted ms-2"></i>
                                        </div>
                                    </a>
                                @endforeach
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
                Aucun bulletin publié pour vos enfants.
            @else
                No published bulletins for your children yet.
            @endif
        </div>
    @endif
</div>
@endsection
