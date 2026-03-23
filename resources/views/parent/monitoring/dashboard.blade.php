{{-- resources/views/parent/monitoring/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Suivi de mon enfant — Millenaire')

@push('styles')
<style>
    .subject-row { border-left: 4px solid transparent; transition: all .2s; }
    .subject-row.pass  { border-left-color: #198754; }
    .subject-row.fail  { border-left-color: #dc3545; }
    .subject-row:hover { background: #f8f9fa; }

    .score-badge { width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 700; }
    .score-badge.pass { background: #d1e7dd; color: #0f5132; }
    .score-badge.fail { background: #f8d7da; color: #842029; }
    .score-badge.pending { background: #e9ecef; color: #6c757d; }

    .trend-up   { color: #198754; }
    .trend-down { color: #dc3545; }
    .trend-stable { color: #6c757d; }

    .child-tab { cursor: pointer; border-radius: 12px; transition: all .2s; }
    .child-tab.active { background: #0d6efd; color: #fff !important; }
    .child-tab:hover:not(.active) { background: #e7f1ff; }

    .stat-mini { border-radius: 12px; padding: 16px; text-align: center; }
    .notification-dot { width: 10px; height: 10px; background: #dc3545; border-radius: 50%; display: inline-block; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">

    {{-- ── EN-TÊTE ── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">👨‍👩‍👧 Suivi Scolaire</h1>
            <p class="text-muted mb-0">Année {{ $academicYear }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($notifications->count() > 0)
            <button class="btn btn-outline-danger btn-sm position-relative" onclick="showAlerts()">
                <i class="fas fa-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge bg-danger">{{ $notifications->count() }}</span>
            </button>
            @endif
            <select id="termSelect" class="form-select form-select-sm" style="width:auto" onchange="loadChildData()">
                @for($t = 1; $t <= 3; $t++)
                <option value="{{ $t }}" {{ $t == $term ? 'selected' : '' }}>Trimestre {{ $t }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- ── SÉLECTEUR ENFANT (si plusieurs) ── --}}
    @if($children->count() > 1)
    <div class="d-flex gap-2 mb-4 overflow-auto pb-1">
        @foreach($children as $child)
        <div class="child-tab px-3 py-2 border {{ $child->id == $selectedStudent->id ? 'active' : '' }}"
             onclick="switchChild({{ $child->id }})">
            <div class="fw-semibold small">{{ $child->user->name }}</div>
            <div class="opacity-75" style="font-size:.75rem">{{ $child->classe?->name }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── RÉSUMÉ GÉNÉRAL ── --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-5">
                            <h5 class="fw-bold mb-1" id="studentName">{{ $selectedStudent->user->name }}</h5>
                            <p class="mb-0 opacity-75">{{ $selectedStudent->classe?->name }} • {{ $selectedStudent->matricule }}</p>
                        </div>
                        <div class="col-md-7">
                            <div class="row g-2 text-center">
                                <div class="col-3">
                                    <div class="stat-mini" style="background: rgba(255,255,255,.15); border-radius: 12px; padding: 16px;">
                                        <div class="h4 fw-bold mb-0" id="summaryAvg">
                                            {{ $dashboardData['summary']['term_average'] ? number_format($dashboardData['summary']['term_average'], 2) : '—' }}
                                        </div>
                                        <small class="opacity-75">Moy. Trim.</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stat-mini" style="background: rgba(255,255,255,.15); border-radius: 12px; padding: 16px;">
                                        <div class="h4 fw-bold mb-0" id="summaryRank">{{ $dashboardData['summary']['rank_display'] }}</div>
                                        <small class="opacity-75">Rang</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stat-mini" style="background: rgba(255,255,255,.15); border-radius: 12px; padding: 16px;">
                                        <div class="h4 fw-bold mb-0" id="classAvg">{{ $dashboardData['class_avg'] }}/20</div>
                                        <small class="opacity-75">Moy. Classe</small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stat-mini" style="background: rgba(255,255,255,.15); border-radius: 12px; padding: 16px;">
                                        @if($dashboardData['trend'])
                                            <div class="h4 fw-bold mb-0 {{ $dashboardData['trend']['direction'] == 'up' ? '' : '' }}">
                                                {{ $dashboardData['trend']['label'] }}
                                                {!! $dashboardData['trend']['direction'] == 'up' ? '↑' : ($dashboardData['trend']['direction'] == 'down' ? '↓' : '→') !!}
                                            </div>
                                        @else
                                            <div class="h4 fw-bold mb-0">—</div>
                                        @endif
                                        <small class="opacity-75">Évolution</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($dashboardData['summary']['appreciation'])
                    <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                        <i class="fas fa-comment-dots me-2 opacity-75"></i>
                        <em class="opacity-90">{{ $dashboardData['summary']['appreciation'] }}</em>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── ALERTES MATIÈRES CRITIQUES ── --}}
    @if($dashboardData['critical_subjects']->count() > 0)
    <div class="alert alert-danger border-0 border-start border-danger border-4 mb-4">
        <strong>⚠️ Attention :</strong> {{ $selectedStudent->user->name }} a des difficultés dans
        {{ $dashboardData['critical_subjects']->count() }} matière(s) (moyenne &lt; 8/20) :
        <strong>{{ $dashboardData['critical_subjects']->pluck('subject_name')->join(', ') }}</strong>
    </div>
    @endif

    {{-- ── TABLEAU DES MATIÈRES ── --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold mb-0">📚 Résultats par matière</h6>
                    <span class="badge bg-light text-dark">Trimestre {{ $term }}</span>
                </div>
                <div class="card-body p-0">
                    <div id="subjectsTable">
                        @foreach($dashboardData['subject_breakdown'] as $subject)
                        <div class="subject-row p-3 border-bottom d-flex align-items-center gap-3 {{ $subject['status'] }}">
                            {{-- Badge note --}}
                            <div class="score-badge {{ $subject['status'] }}">
                                {{ $subject['average'] !== null ? number_format($subject['average'], 1) : '?' }}
                            </div>

                            {{-- Info matière --}}
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2">
                                    <strong>{{ $subject['subject_name'] }}</strong>
                                    @if($subject['status'] == 'fail')
                                        <span class="badge bg-danger" style="font-size:.7rem">En difficulté</span>
                                    @elseif($subject['status'] == 'pass')
                                        <span class="badge bg-success" style="font-size:.7rem">Admis</span>
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    Coeff. {{ $subject['coefficient'] }} •
                                    Séq.1: {{ $subject['seq1_score'] !== null ? $subject['seq1_score'].'/20' : '—' }} •
                                    Séq.2: {{ $subject['seq2_score'] !== null ? $subject['seq2_score'].'/20' : '—' }}
                                </div>
                                @if($subject['teacher_comment'])
                                <div class="small text-muted mt-1 fst-italic">
                                    <i class="fas fa-quote-left me-1 opacity-50"></i>{{ $subject['teacher_comment'] }}
                                </div>
                                @endif
                            </div>

                            {{-- Comparaison classe --}}
                            <div class="text-end small">
                                @if($subject['class_avg'] !== null)
                                    <div class="{{ $subject['above_class'] ? 'text-success' : 'text-danger' }}">
                                        {!! $subject['above_class'] ? '▲' : '▼' !!}
                                        vs {{ number_format($subject['class_avg'], 1) }}
                                    </div>
                                    <small class="text-muted">Moy. classe</small>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── GRAPHIQUE ÉVOLUTION ── --}}
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-semibold mb-0">📈 Évolution annuelle</h6>
                </div>
                <div class="card-body">
                    <canvas id="evolutionChart" height="200"></canvas>
                </div>
            </div>

            {{-- Points forts / faibles --}}
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-semibold mb-0">🎯 Bilan rapide</h6>
                </div>
                <div class="card-body">
                    @if($dashboardData['summary']['term_average'])
                        @php $avg = $dashboardData['summary']['term_average']; @endphp
                        <div class="text-center mb-3">
                            <div class="display-6 fw-bold {{ $avg >= 10 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($avg, 2) }}/20
                            </div>
                            <div class="text-muted small">Moyenne du trimestre</div>
                        </div>
                        <div class="alert {{ $avg >= 10 ? 'alert-success' : 'alert-danger' }} py-2 small">
                            {{ $dashboardData['summary']['appreciation'] ?? ($avg >= 10 ? 'Continue ainsi !' : 'Des efforts sont nécessaires.') }}
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-hourglass-half fa-2x mb-2 d-block"></i>
                            En attente des notes
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── MODAL ALERTES ── --}}
<div class="modal fade" id="alertsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🔔 Alertes de notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @forelse($notifications as $notif)
                <div class="alert alert-warning py-2 mb-2 small">
                    <strong>{{ $notif->title }}</strong><br>
                    {{ $notif->message }}
                    <div class="text-muted mt-1" style="font-size:.75rem">{{ $notif->created_at->diffForHumans() }}</div>
                </div>
                @empty
                <p class="text-muted text-center">Aucune alerte.</p>
                @endforelse
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" onclick="markRead()">Tout marquer comme lu</button>
                <button class="btn btn-sm btn-primary" data-bs-dismiss="modal">Fermer</button>
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
const STUDENT_ID = {{ $selectedStudent->id }};
const ACADEMIC_Y = '{{ $academicYear }}';

// ── Graphique évolution ──
let evolutionChart = null;

async function loadEvolutionChart() {
    const res  = await fetch(`${BASE_URL}/parent/child/${STUDENT_ID}/evolution?academic_year=${ACADEMIC_Y}`);
    const data = await res.json();

    const ctx = document.getElementById('evolutionChart').getContext('2d');
    if (evolutionChart) evolutionChart.destroy();

    const labels     = data.trimester_evolution.map(d => d.term);
    const studentAvg = data.trimester_evolution.map(d => d.student_avg);
    const classAvg   = data.trimester_evolution.map(d => d.class_avg);

    evolutionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Votre enfant',
                    data: studentAvg,
                    borderColor: '#0d6efd',
                    backgroundColor: '#0d6efd20',
                    fill: true,
                    tension: .4,
                    pointRadius: 6,
                },
                {
                    label: 'Moy. classe',
                    data: classAvg,
                    borderColor: '#adb5bd',
                    borderDash: [5,5],
                    fill: false,
                    tension: .4,
                    pointRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } }
            },
            scales: {
                y: { min: 0, max: 20, ticks: { stepSize: 5 } }
            }
        }
    });
}

function showAlerts() {
    new bootstrap.Modal(document.getElementById('alertsModal')).show();
}

async function markRead() {
    await fetch(`${BASE_URL}/parent/notifications/read`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
    });
    document.querySelector('.btn-outline-danger')?.remove();
    bootstrap.Modal.getInstance(document.getElementById('alertsModal')).hide();
}

async function switchChild(studentId) {
    window.location.href = `${BASE_URL}/parent/monitoring?student_id=${studentId}&term={{ $term }}`;
}

async function loadChildData() {
    const term = document.getElementById('termSelect').value;
    window.location.href = `${BASE_URL}/parent/monitoring?student_id=${STUDENT_ID}&term=${term}`;
}

// Init
loadEvolutionChart();
</script>
@endpush
