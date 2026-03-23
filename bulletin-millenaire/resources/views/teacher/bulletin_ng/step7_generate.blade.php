@extends('layouts.app')
@section('title', 'Bulletin NG — Bulletins Générés')
@php $currentStep = 7; $isEN = $config->langue === 'EN'; @endphp
@section('content')
<div class="bng-page">

    <div class="bng-page-header">
        <div class="bng-page-header-inner">
            <div class="bng-page-icon">🎓</div>
            <div>
                <h1 class="bng-page-title">{{ $isEN ? 'Step 7 — Report Cards' : 'Étape 7 — Bulletins Générés' }}</h1>
                <p class="bng-page-subtitle">{{ $config->nom_classe }} | {{ $config->trimestre_label }} | {{ $config->annee_academique }}</p>
            </div>
        </div>
        <a href="{{ route('teacher.bulletin_ng.pdf.all', $config->id) }}"
           class="bng-btn bng-btn-primary" target="_blank">
            📥 {{ $isEN ? 'Export All PDFs' : 'Exporter Tous les PDFs' }}
        </a>
    </div>

    @include('teacher.bulletin_ng.partials.wizard_header')

    {{-- Stats classe --}}
    <div class="bng-stats-bar">
        <div class="bng-stat-item bng-stat-primary">
            <div class="bng-stat-value">{{ number_format($stats['avg'],2) }}</div>
            <div class="bng-stat-label">{{ $isEN ? 'Class Average' : 'Moy. Classe' }}</div>
        </div>
        <div class="bng-stat-item bng-stat-success">
            <div class="bng-stat-value">{{ $stats['pct'] }}%</div>
            <div class="bng-stat-label">{{ $isEN ? 'Success Rate' : 'Taux Réussite' }}</div>
        </div>
        <div class="bng-stat-item">
            <div class="bng-stat-value">{{ number_format($stats['max'],2) }}</div>
            <div class="bng-stat-label">{{ $isEN ? 'Highest' : 'Moy. Max' }}</div>
        </div>
        <div class="bng-stat-item bng-stat-warning">
            <div class="bng-stat-value">{{ number_format($stats['min'],2) }}</div>
            <div class="bng-stat-label">{{ $isEN ? 'Lowest' : 'Moy. Min' }}</div>
        </div>
        <div class="bng-stat-item">
            <div class="bng-stat-value">{{ $stats['passing'] }}/{{ $students->count() }}</div>
            <div class="bng-stat-label">{{ $isEN ? 'Passing' : 'Au-dessus 10' }}</div>
        </div>
    </div>

    {{-- Grille bulletins --}}
    <div class="bng-bulletins-grid">
        @foreach($students as $student)
        @php
            $avg  = $stats['avgs'][$student->id] ?? 0;
            $rank = $stats['ranks'][$student->id] ?? '-';
            $app  = match(true) {
                $avg < 10  => $isEN ? 'Fail'        : 'Échec',
                $avg < 12  => $isEN ? 'Pass'        : 'Passable',
                $avg < 15  => $isEN ? 'Fairly Good' : 'Assez Bien',
                $avg < 17  => $isEN ? 'Good'        : 'Bien',
                default    => 'Excellent',
            };
            $appClass = $avg >= 15 ? 'app-good' : ($avg >= 10 ? 'app-ok' : 'app-bad');
        @endphp
        <div class="bng-bulletin-card">
            <div class="bng-bulletin-card-header">
                <div>
                    <div class="bng-bulletin-name">{{ $student->nom }}</div>
                    <div class="bng-bulletin-mat">{{ $student->matricule }}</div>
                </div>
                <div class="bng-bulletin-avg">
                    <div class="bng-bulletin-avg-val">{{ number_format($avg,2) }}</div>
                    <div class="bng-bulletin-avg-lbl">/20</div>
                </div>
            </div>
            <div class="bng-bulletin-card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-size:12px;color:var(--bng-text-sec);">{{ $isEN ? 'Rank' : 'Rang' }}</span>
                    <span style="font-weight:700;font-size:12px;">{{ $rank }}/{{ $students->count() }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span style="font-size:12px;color:var(--bng-text-sec);">{{ $isEN ? 'Appreciation' : 'Appréciation' }}</span>
                    <span class="bng-app-badge {{ $appClass }}">{{ $app }}</span>
                </div>
                @if($student->conduite && $student->conduite->tableau_honneur)
                    <div style="background:#fef9c3;border-radius:8px;padding:4px 10px;font-size:11px;color:#a16207;font-weight:600;margin-top:8px;">
                        🏅 {{ $isEN ? 'Honor Roll' : "Tableau d'Honneur" }}
                    </div>
                @endif
                <div class="bng-bulletin-actions">
                    <a href="{{ route('teacher.bulletin_ng.preview.student', [$config->id, $student->id]) }}"
                       class="bng-btn bng-btn-ghost" style="flex:1;justify-content:center;font-size:12px;" target="_blank">
                        👁 {{ $isEN ? 'Preview' : 'Aperçu' }}
                    </a>
                    <a href="{{ route('teacher.bulletin_ng.pdf.student', [$config->id, $student->id]) }}"
                       class="bng-btn bng-btn-primary" style="flex:1;justify-content:center;font-size:12px;" target="_blank">
                        🖨 {{ $isEN ? 'Print' : 'Imprimer' }}
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
