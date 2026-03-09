{{-- resources/views/student/progress/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Ma progression — Millenaire')

@push('styles')
<style>
    .subject-card { border-radius: 12px; border: none; transition: transform .2s; }
    .subject-card:hover { transform: translateY(-3px); box-shadow: 0 4px 16px rgba(0,0,0,.1); }
    .progress-ring { width: 80px; height: 80px; }
    .strength-badge { background: #d1e7dd; color: #0f5132; border-radius: 20px; padding: 4px 12px; font-size: .8rem; }
    .weakness-badge { background: #f8d7da; color: #842029; border-radius: 20px; padding: 4px 12px; font-size: .8rem; }
    .rank-display { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); border-radius: 16px; color: #fff; }
    .chart-tabs .nav-link { border-radius: 8px; color: #6c757d; }
    .chart-tabs .nav-link.active { background: #0d6efd; color: #fff; }
    .subject-bar { height: 10px; border-radius: 5px; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">

    {{-- ── EN-TÊTE ── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">🎓 Ma Progression</h1>
            <p class="text-muted mb-0">{{ $student->user->name }} • {{ $student->classe?->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <select id="termSelect" class="form-select form-select-sm" style="width:auto"
                    onchange="loadTerm(this.value)">
                @for($t = 1; $t <= 3; $t++)
                <option value="{{ $t }}" {{ $t == $term ? 'selected' : '' }}>Trimestre {{ $t }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- ── HÉROS : Mon classement ── --}}
    <div class="row g-3 mb-4">
        {{-- Moyenne + Rang --}}
        <div class="col-md-5">
            <div class="card rank-display h-100 p-4 text-center">
                @if($progressData['term_average'])
                    <div style="font-size: 3.5rem; font-weight: 900; line-height: 1;">
                        {{ number_format($progressData['term_average'], 2) }}
                        <span style="font-size: 1.5rem; opacity: .7">/20</span>
                    </div>
                    <div class="mt-2 opacity-90">Moyenne du trimestre {{ $term }}</div>

                    @if($progressData['rank_display'])
                    <div class="mt-3 p-3" style="background: rgba(255,255,255,.2); border-radius: 12px;">
                        <div style="font-size: 1.5rem; font-weight: 800;">{{ $progressData['rank_display'] }}</div>
                        <div class="opacity-75 small">dans ta classe</div>
                    </div>
                    @endif

                    @if($progressData['percentile'] !== null)
                    <div class="mt-2 opacity-75 small">
                        Tu fais mieux que {{ $progressData['percentile'] }}% de ta classe
                    </div>
                    @endif

                    <div class="mt-3">
                        <span class="badge bg-white text-dark">
                            {{ $progressData['appreciation'] ?? ($progressData['is_passing'] ? 'Admis ✅' : 'Non admis ❌') }}
                        </span>
                    </div>
                @else
                    <div class="py-4">
                        <i class="fas fa-hourglass-half fa-3x mb-3 opacity-50"></i>
                        <div>En attente des notes du trimestre {{ $term }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Séquences + Points forts/faibles --}}
        <div class="col-md-7">
            <div class="row g-3 h-100">
                {{-- Moyennes séquences --}}
                <div class="col-6">
                    <div class="card shadow-sm text-center p-3 h-100">
                        <div class="text-muted small mb-1">📋 Séquence 1</div>
                        <div class="h3 fw-bold mb-0 {{ ($progressData['seq1_average'] ?? 0) >= 10 ? 'text-success' : 'text-danger' }}">
                            {{ $progressData['seq1_average'] ? number_format($progressData['seq1_average'], 2) : '—' }}/20
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm text-center p-3 h-100">
                        <div class="text-muted small mb-1">📋 Séquence 2</div>
                        <div class="h3 fw-bold mb-0 {{ ($progressData['seq2_average'] ?? 0) >= 10 ? 'text-success' : 'text-danger' }}">
                            {{ $progressData['seq2_average'] ? number_format($progressData['seq2_average'], 2) : '—' }}/20
                        </div>
                    </div>
                </div>

                {{-- Points forts --}}
                <div class="col-12">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex gap-2 mb-2">
                            <span>💪 <strong>Points forts</strong></span>
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse($progressData['strengths'] as $s)
                                <span class="strength-badge">{{ $s['subject_name'] }} ({{ $s['average'] }})</span>
                            @empty
                                <span class="text-muted small">Continue à travailler pour atteindre 12+ 🎯</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Points faibles --}}
                @if($progressData['weaknesses']->count() > 0)
                <div class="col-12">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex gap-2 mb-2">
                            <span>📌 <strong>À améliorer</strong></span>
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($progressData['weaknesses'] as $s)
                                <span class="weakness-badge">{{ $s['subject_name'] }} ({{ $s['average'] }})</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── GRAPHIQUES ── --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold mb-0">📊 Visualisation des notes</h6>
                    <ul class="nav chart-tabs nav-pills">
                        <li class="nav-item">
                            <button class="nav-link active py-1 px-2 small" onclick="switchChart('radar', this)">Radar</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-2 small" onclick="switchChart('bar', this)">Barres</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-2 small" onclick="switchChart('line', this)">Évolution</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <canvas id="mainChart" height="280"></canvas>
                </div>
            </div>
        </div>

        {{-- Tableau matières --}}
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-semibold mb-0">📚 Détail par matière</h6>
                </div>
                <div class="card-body p-0">
                    @foreach($progressData['subjects'] as $subject)
                    <div class="px-3 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="fw-semibold small">{{ $subject['subject_name'] }}</span>
                            <span class="badge {{ $subject['is_passing'] ? 'bg-success' : ($subject['average'] !== null ? 'bg-danger' : 'bg-secondary') }}">
                                {{ $subject['average'] !== null ? number_format($subject['average'], 1).'/20' : 'N/A' }}
                            </span>
                        </div>
                        @if($subject['average'] !== null)
                        <div class="progress subject-bar">
                            <div class="progress-bar {{ $subject['is_passing'] ? 'bg-success' : 'bg-danger' }}"
                                 style="width: {{ ($subject['average'] / 20) * 100 }}%"></div>
                        </div>
                        @if($subject['rank'])
                        <div class="text-muted small mt-1">{{ $subject['rank'] }}ème / {{ $subject['class_count'] }} élèves en matière</div>
                        @endif
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── ÉVOLUTION ANNUELLE ── --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-semibold mb-0">📅 Mon évolution sur l'année {{ $academicYear }}</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($yearlyData as $yearTerm)
                        <div class="col-md-4">
                            <div class="card {{ $yearTerm['term'] == $term ? 'border-primary' : '' }} h-100">
                                <div class="card-body text-center">
                                    <h6 class="fw-bold mb-3">Trimestre {{ $yearTerm['term'] }}</h6>
                                    @if($yearTerm['student_avg'])
                                        <div class="h3 fw-bold {{ $yearTerm['student_avg'] >= 10 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($yearTerm['student_avg'], 2) }}/20
                                        </div>
                                        @if($yearTerm['rank'])
                                        <div class="text-muted small">{{ $yearTerm['rank'] }}ème / {{ $yearTerm['total'] }}</div>
                                        @endif
                                        <div class="mt-2 text-muted small">
                                            Séq.1: {{ $yearTerm['seq1'] ?? '—' }} | Séq.2: {{ $yearTerm['seq2'] ?? '—' }}
                                        </div>
                                    @else
                                        <div class="text-muted py-2">
                                            <i class="fas fa-hourglass-half"></i> En attente
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const BASE_URL   = '{{ url('') }}';
const CSRF_TOKEN = '{{ csrf_token() }}';
const ACADEMIC_Y = '{{ $academicYear }}';
let mainChart = null;
let currentChartType = 'radar';

// ── Charger et afficher un graphique ──
async function loadChart(type = 'radar') {
    const res  = await fetch(`${BASE_URL}/student/progress/chart?type=${type}&academic_year=${ACADEMIC_Y}`);
    const data = await res.json();

    const ctx = document.getElementById('mainChart').getContext('2d');
    if (mainChart) mainChart.destroy();

    if (type === 'radar') {
        mainChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: data.labels,
                datasets: data.datasets.map(ds => ({
                    label: ds.label,
                    data: ds.data,
                    borderColor: ds.color,
                    backgroundColor: ds.color + '30',
                    pointBackgroundColor: ds.color,
                    fill: true,
                    borderWidth: 2,
                }))
            },
            options: {
                responsive: true,
                scales: { r: { min: 0, max: 20, ticks: { stepSize: 5 } } },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    } else if (type === 'bar') {
        mainChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: data.datasets.map(ds => ({
                    label: ds.label,
                    data: ds.data,
                    backgroundColor: ds.colors || ds.color,
                    borderRadius: 6,
                }))
            },
            options: {
                responsive: true,
                scales: { y: { min: 0, max: 20, ticks: { stepSize: 5 } } },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    } else { // line
        mainChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: data.datasets.map(ds => ({
                    label: ds.label,
                    data: ds.data,
                    borderColor: ds.color,
                    backgroundColor: ds.color + '20',
                    fill: true,
                    tension: .4,
                    spanGaps: true,
                    pointRadius: 5,
                }))
            },
            options: {
                responsive: true,
                scales: { y: { min: 0, max: 20, ticks: { stepSize: 5 } } },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
}

function switchChart(type, btn) {
    document.querySelectorAll('.chart-tabs .nav-link').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');
    currentChartType = type;
    loadChart(type);
}

function loadTerm(term) {
    window.location.href = `${BASE_URL}/student/progress?term=${term}&academic_year=${ACADEMIC_Y}`;
}

// Init
loadChart('radar');
</script>
@endpush
