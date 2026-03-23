{{--
    |--------------------------------------------------------------------------
    | parent/monitoring/index.blade.php — Tableau de Suivi Enfants
    |--------------------------------------------------------------------------
    | Phase 3 — Parent — Suivi académique et performances des enfants
    --}}

@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Suivi Académique' : 'Academic Monitoring');
@endphp

@section('title', $pageTitle)

@push('styles')
<style>
.child-card {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.child-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-color: var(--primary);
}

.performance-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
}

.performance-badge.excellent { background: #ecfdf5; color: #059669; }
.performance-badge.good { background: #eff6ff; color: #2563eb; }
.performance-badge.average { background: #fffbeb; color: #d97706; }
.performance-badge.poor { background: #fef2f2; color: #dc2626; }
</style>
@endpush

@section('content')

@php
    $isFr = app()->getLocale() === 'fr';
    $children = auth()->user()->children ?? collect();
@endphp

{{-- ─── Header ──────────────────────────────────────────────────────────────── --}}
<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb)">
            <i data-lucide="trending-up"></i>
        </div>
        <div>
            <h1 class="page-title mb-0">{{ $isFr ? 'Suivi Académique' : 'Academic Monitoring' }}</h1>
            <p class="page-subtitle text-muted">{{ $isFr ? 'Suivez la progression de vos enfants' : 'Track your children\'s progress' }}</p>
        </div>
    </div>
</div>

{{-- ─── Children Overview ──────────────────────────────────────────────────────── --}}
@if($children->count() > 0)
    <div class="row gy-4">
        @foreach($children as $child)
            @php
                $avgGrade = $child->estimates->avg('value') ?? 0;
                $attendanceRate = $child->calculateAttendanceRate() ?? 0;
                $performanceLevel = $avgGrade >= 14 ? 'excellent' : ($avgGrade >= 10 ? 'good' : ($avgGrade >= 8 ? 'average' : 'poor'));
            @endphp
            <div class="col-lg-6">
                <div class="child-card">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">{{ $child->user->full_name ?? 'N/A' }}</h5>
                            <p class="text-muted small">{{ $child->classe->name ?? 'N/A' }} {{ $isFr ? '•' : '•' }} {{ $child->matricule ?? '' }}</p>
                        </div>
                        <span class="performance-badge {{ $performanceLevel }}">
                            @if($performanceLevel === 'excellent')
                                {{ $isFr ? 'Excellent' : 'Excellent' }}
                            @elseif($performanceLevel === 'good')
                                {{ $isFr ? 'Bon' : 'Good' }}
                            @elseif($performanceLevel === 'average')
                                {{ $isFr ? 'Passable' : 'Average' }}
                            @else
                                {{ $isFr ? 'Faible' : 'Poor' }}
                            @endif
                        </span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <div class="text-center">
                                <div class="h5 mb-1" style="color: var(--primary);">{{ number_format($avgGrade, 1) }}/20</div>
                                <small class="text-muted">{{ $isFr ? 'Moyenne' : 'Average' }}</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <div class="h5 mb-1" style="color: #10b981;">{{ $attendanceRate }}%</div>
                                <small class="text-muted">{{ $isFr ? 'Présence' : 'Attendance' }}</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <div class="h5 mb-1" style="color: #f59e0b;">{{ $child->estimates->count() ?? 0 }}</div>
                                <small class="text-muted">{{ $isFr ? 'Évaluations' : 'Grades' }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('parent.monitoring.index') }}" class="btn btn-sm btn-primary flex-grow-1">
                            <i data-lucide="chart-line" style="width: 14px" class="me-1"></i>
                            {{ $isFr ? 'Détails' : 'Details' }}
                        </a>
                        <a href="{{ route('parent.child.marks', $child->id) }}" class="btn btn-sm btn-outline-secondary">
                            {{ $isFr ? 'Notes' : 'Grades' }}
                        </a>
                        <a href="{{ route('parent.child.attendance', $child->id) }}" class="btn btn-sm btn-outline-secondary">
                            {{ $isFr ? 'Absences' : 'Attendance' }}
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info d-flex align-items-center">
        <i data-lucide="info" style="width: 20px; margin-right: 10px;"></i>
        <div>
            {{ $isFr ? 'Vous n\'avez pas d\'enfants assignés à votre compte.' : 'You have no children assigned to your account.' }}
        </div>
    </div>
@endif

@endsection
