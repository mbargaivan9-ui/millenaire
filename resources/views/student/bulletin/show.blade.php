@extends('layouts.app')

@php $isFr = app()->getLocale() === 'fr'; @endphp

@section('title', $isFr ? 'Bulletin' : 'Report Card')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-file-pdf text-danger me-2"></i>
                {{ $bulletin->student->user->name ?? '—' }}
            </h1>
            <p class="text-muted">
                {{ $isFr ? 'Classe' : 'Class' }}: <strong>{{ $bulletin->student->classe?->name ?? '—' }}</strong>
                • {{ $isFr ? 'Trimestre' : 'Term' }}: <strong>T{{ $bulletin->term }}</strong>
                • {{ $isFr ? 'Séquence' : 'Sequence' }}: <strong>S{{ $bulletin->sequence ?? '-' }}</strong>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('student.bulletins.pdf', $bulletin->id) }}" class="btn btn-danger btn-sm">
                <i class="fas fa-download"></i>
                @if($isFr) Télécharger PDF @else Download PDF @endif
            </a>
            <a href="{{ route('student.bulletins.index') }}" class="btn btn-outline-secondary btn-sm">
                {{ $isFr ? 'Retour' : 'Back' }}
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-5 fw-bold text-primary">
                        {{ $bulletin->moyenne ? number_format($bulletin->moyenne, 2) : '—' }}
                    </div>
                    <small class="text-muted">/20</small>
                    <p class="mb-0 small mt-2">
                        @if($isFr) Moyenne générale @else General Average @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-5 fw-bold text-success">
                        @if($bulletin->rang) n°{{ $bulletin->rang }} @else — @endif
                    </div>
                    <small class="text-muted">/{{ $totalStudents }}</small>
                    <p class="mb-0 small mt-2">
                        @if($isFr) Classement @else Ranking @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="fs-5 fw-bold" style="color: {{ $bulletin->appreciation_color ?? '#94a3b8' }}">
                        {{ $bulletin->appreciation ?? '—' }}
                    </div>
                    <p class="mb-0 small mt-2">
                        @if($isFr) Appréciation @else Assessment @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <p class="text-muted small mb-1">
                        @if($isFr) Votre classement @else Your rank @endif
                    </p>
                    <div class="fs-6 text-muted">
                        @if(app()->getLocale() === 'fr')
                            Vous êtes 
                            <strong>{{ $bulletin->rang ? 'n°' . $bulletin->rang : 'non classé' }}</strong>
                            sur {{ $totalStudents }} élèves
                        @else
                            You are 
                            <strong>{{ $bulletin->rang ? '#' . $bulletin->rang : 'unranked' }}</strong>
                            out of {{ $totalStudents }} students
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Marks Table --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-0">
            <h5 class="mb-0">
                @if($isFr) Tableau des Notes @else Grades Table @endif
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>@if($isFr) Matière @else Subject @endif</th>
                        <th class="text-center">@if($isFr) Coefficient @else Coeff @endif</th>
                        <th class="text-center">@if($isFr) Votre Note @else Your Score @endif</th>
                        <th class="text-center">@if($isFr) Appréciation @else Assessment @endif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bulletin->marks()->with('subject')->get() as $mark)
                        @php
                            $subject = $mark->subject;
                            $coef = $subject?->coefficient ?? 1;
                            $appreciation = app(\App\Services\GradeCalculationService::class)
                                ->suggestAppreciation((float)($mark->score ?? 0));
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $subject?->name ?? '—' }}</td>
                            <td class="text-center">{{ $coef }}</td>
                            <td class="text-center">
                                @if($mark->score)
                                    <span class="badge" style="background-color: {{ $appreciation['color'] ?? '#94a3b8' }}">
                                        {{ number_format($mark->score, 2) }}/20
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($mark->score)
                                    <small class="text-muted">{{ $appreciation['label'] ?? '—' }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                @if($isFr) Aucune note enregistrée @else No grades recorded @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Observations --}}
    @if($bulletin->observation)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light border-0">
                <h5 class="mb-0">
                    @if($isFr) Observations @else Observations @endif
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $bulletin->observation }}</p>
            </div>
        </div>
    @endif

    {{-- Recommendations --}}
    @if($bulletin->recommendations)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light border-0">
                <h5 class="mb-0">
                    @if($isFr) Recommandations @else Recommendations @endif
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $bulletin->recommendations }}</p>
            </div>
        </div>
    @endif

    {{-- Footer --}}
    <div class="row mt-5 pt-4 border-top">
        <div class="col-md-12">
            <p class="text-muted small">
                @if($isFr)
                    Ce bulletin a été publié le {{ $bulletin->published_at?->format('d/m/Y à H:i') ?? '—' }}
                @else
                    This report card was published on {{ $bulletin->published_at?->format('m/d/Y at H:i') ?? '—' }}
                @endif
            </p>
        </div>
    </div>
</div>

<style>
    .badge {
        display: inline-block;
        min-width: 60px;
        padding: 0.35em 0.65em;
    }
</style>
@endsection
