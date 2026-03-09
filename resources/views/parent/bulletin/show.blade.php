{{-- parent/bulletin/show.blade.php — Lecture du bulletin par le parent --}}
@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Bulletin de notes' : 'Report Card')
@section('content')
@php
    $isFr    = app()->getLocale() === 'fr';
    $marks   = $bulletin->marks()->with('subject')->get();
    $moyenne = $bulletin->moyenne;
    $rang    = $bulletin->rang;
    $apprColor = match(true) {
        $moyenne === null => '#94a3b8', $moyenne < 10 => '#ef4444',
        $moyenne < 13 => '#f59e0b', $moyenne < 16 => '#3b82f6',
        $moyenne < 19 => '#10b981', default => '#8b5cf6',
    };
    $apprLabel = match(true) {
        $moyenne === null => '—', $moyenne < 10 => $isFr ? 'Insuffisant' : 'Insufficient',
        $moyenne < 13 => $isFr ? 'Assez Bien' : 'Fair', $moyenne < 16 => $isFr ? 'Bien' : 'Good',
        $moyenne < 19 => $isFr ? 'Très Bien' : 'Very Good', default => 'Excellent',
    };
@endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('parent.dashboard') }}" class="btn btn-light btn-sm"><i data-lucide="arrow-left" style="width:14px"></i></a>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Bulletin de Notes' : 'Report Card' }}</h1>
                <p class="page-subtitle text-muted">{{ $bulletin->student->user->name }} · {{ $isFr ? 'Trimestre' : 'Term' }} {{ $bulletin->term }} — {{ $isFr ? 'Séquence' : 'Sequence' }} {{ $bulletin->sequence }}</p>
            </div>
        </div>
        <a href="{{ route('parent.bulletin.pdf', $bulletin->id) }}" class="btn btn-primary btn-sm">
            <i data-lucide="download" style="width:14px" class="me-1"></i>{{ $isFr ? 'Télécharger PDF' : 'Download PDF' }}
        </a>
    </div>
</div>

{{-- Summary stats --}}
<div class="row gy-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card text-center">
            <div class="stat-value" style="color:{{ $apprColor }}">{{ $moyenne !== null ? number_format((float)$moyenne, 2) : '—' }}</div>
            <div class="stat-label">{{ $isFr ? 'Moyenne Générale' : 'General Average' }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card text-center">
            <div class="stat-value">{{ $rang !== null ? $rang . 'e/' . $totalStudents : '—' }}</div>
            <div class="stat-label">{{ $isFr ? 'Rang' : 'Rank' }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card text-center">
            <div class="stat-value" style="font-size:1.2rem;color:{{ $apprColor }}">{{ $apprLabel }}</div>
            <div class="stat-label">{{ $isFr ? 'Appréciation' : 'Grade' }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card text-center">
            <div class="stat-value" style="font-size:1.2rem">{{ $classMoyenne ?? '—' }}</div>
            <div class="stat-label">{{ $isFr ? 'Moy. de classe' : 'Class average' }}</div>
        </div>
    </div>
</div>

{{-- Grades table --}}
<div class="card">
    <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="table-2" style="width:16px" class="me-2"></i>{{ $isFr ? 'Détail des notes' : 'Grade detail' }}</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ $isFr ? 'Matière' : 'Subject' }}</th>
                        <th style="text-align:center">Coef.</th>
                        <th style="text-align:center">{{ $isFr ? 'Note /20' : 'Grade /20' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Points' : 'Points' }}</th>
                        <th>{{ $isFr ? 'Appréciation' : 'Remark' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($marks as $mark)
                    @php
                        $score = (float)($mark->score ?? 0);
                        $coef  = $mark->subject?->coefficient ?? 1;
                        $c     = $score>=16?'#10b981':($score>=13?'#3b82f6':($score>=10?'#f59e0b':'#ef4444'));
                        $a     = $score>=16?'Très Bien':($score>=13?'Bien':($score>=10?'Assez Bien':'Insuffisant'));
                    @endphp
                    <tr>
                        <td class="fw-semibold" style="font-size:.86rem">{{ $mark->subject?->name }}</td>
                        <td style="text-align:center;font-size:.83rem">{{ $coef }}</td>
                        <td style="text-align:center">
                            <span style="background:{{ $c }}22;color:{{ $c }};padding:.2rem .6rem;border-radius:12px;font-size:.8rem;font-weight:700">{{ number_format($score, 2) }}</span>
                        </td>
                        <td style="text-align:center;font-size:.83rem;color:var(--text-muted)">{{ number_format($score * $coef, 2) }}</td>
                        <td><span style="background:{{ $c }}15;color:{{ $c }};padding:.15rem .55rem;border-radius:10px;font-size:.72rem;font-weight:700">{{ $a }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:var(--primary);color:#fff">
                        <td colspan="2" class="fw-bold">{{ $isFr ? 'MOYENNE GÉNÉRALE' : 'GENERAL AVERAGE' }}</td>
                        <td style="text-align:center;font-weight:900;font-size:1rem">{{ $moyenne !== null ? number_format((float)$moyenne, 2) : '—' }}/20</td>
                        <td colspan="2" style="font-weight:700">{{ $apprLabel }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@if($bulletin->observations)
<div class="card mt-4">
    <div class="card-header"><h6 class="card-title mb-0">{{ $isFr ? 'Observations du Conseil de Classe' : "Class Council's Remarks" }}</h6></div>
    <div class="card-body"><p style="font-size:.88rem;color:var(--text-secondary);margin:0;font-style:italic">"{{ $bulletin->observations }}"</p></div>
</div>
@endif

@endsection
