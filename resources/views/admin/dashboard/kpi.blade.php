{{-- resources/views/admin/dashboard/kpi.blade.php --}}
@extends('layouts.app')

@section('title', 'Tableau de bord KPI — Millenaire')

@push('styles')
<style>
    /* ── KPI Cards ── */
    .kpi-card {
        border: none;
        border-radius: 16px;
        transition: transform .2s, box-shadow .2s;
    }
    .kpi-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
    .kpi-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }

    /* ── Progress bars ── */
    .completion-bar { height: 8px; border-radius: 4px; }

    /* ── Drag & Drop ── */
    .assignment-card { cursor: grab; border: 1px solid #dee2e6; border-radius: 8px; }
    .assignment-card.dragging { opacity: .5; cursor: grabbing; }
    .drop-zone { border: 2px dashed #adb5bd; border-radius: 8px; min-height: 60px; transition: all .2s; }
    .drop-zone.drag-over { border-color: #0d6efd; background: #e7f1ff; }

    /* ── Alert badges ── */
    .alert-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }

    /* ── Refresh button spin ── */
    .spin { animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    /* ── Dark mode support ── */
    @media (prefers-color-scheme: dark) {
        .kpi-card { background: #1e2a3a !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">

    {{-- ── EN-TÊTE ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">📊 Tableau de bord KPI</h1>
            <p class="text-muted mb-0">Millenaire Connect — Pilotage pédagogique en temps réel</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            {{-- Sélecteur période --}}
            <select id="termSelector" class="form-select form-select-sm" style="width:auto">
                @for ($t = 1; $t <= 3; $t++)
                    <option value="{{ $t }}" {{ $t == $term ? 'selected' : '' }}>Trimestre {{ $t }}</option>
                @endfor
            </select>
            <select id="yearSelector" class="form-select form-select-sm" style="width:auto">
                @foreach (['2024-2025', '2025-2026', '2026-2027'] as $y)
                    <option value="{{ $y }}" {{ $y == $academicYear ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button id="refreshBtn" class="btn btn-outline-primary btn-sm" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt" id="refreshIcon"></i> Actualiser
            </button>
            <a href="{{ route('admin.kpi.export-csv', ['term' => $term, 'academic_year' => $academicYear]) }}" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- ── ALERTE : NOTES NON SAISIES ── --}}
    @if(count($alerts) > 0)
    <div class="alert alert-warning alert-dismissible border-0 mb-4" style="background:#fff3cd; border-left: 4px solid #ffc107 !important;">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>{{ count($alerts) }} matière(s)</strong> sans aucune note saisie pour ce trimestre.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ── CARTES KPI PRINCIPALES ── --}}
    <div class="row g-3 mb-4">
        {{-- Élèves actifs --}}
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Élèves actifs</p>
                            <h2 class="fw-bold mb-0">{{ number_format($kpis['total_students']) }}</h2>
                        </div>
                        <div class="kpi-icon bg-primary bg-opacity-10 text-primary">👥</div>
                    </div>
                    <div class="mt-2 small text-muted">{{ $kpis['total_classes'] }} classes</div>
                </div>
            </div>
        </div>

        {{-- Complétion globale --}}
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Saisies complétées</p>
                            <h2 class="fw-bold mb-0" id="kpiCompletion">{{ $kpis['global_completion'] }}%</h2>
                        </div>
                        <div class="kpi-icon bg-info bg-opacity-10 text-info">📝</div>
                    </div>
                    <div class="progress mt-2 completion-bar">
                        <div class="progress-bar bg-info" id="completionBar" style="width:{{ $kpis['global_completion'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Moyenne générale --}}
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Moyenne générale</p>
                            <h2 class="fw-bold mb-0" id="kpiAvg">{{ $kpis['global_avg'] }}/20</h2>
                        </div>
                        <div class="kpi-icon {{ $kpis['global_avg'] >= 10 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $kpis['global_avg'] >= 10 ? 'text-success' : 'text-danger' }}">📈</div>
                    </div>
                    <div class="small mt-2 {{ $kpis['global_avg'] >= 10 ? 'text-success' : 'text-danger' }}">
                        Taux de réussite : {{ $kpis['pass_rate'] }}%
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertes critiques --}}
        <div class="col-6 col-lg-3">
            <div class="card kpi-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Alertes notes critiques</p>
                            <h2 class="fw-bold mb-0 text-danger" id="kpiAlerts">{{ $kpis['critical_alerts'] }}</h2>
                        </div>
                        <div class="kpi-icon bg-danger bg-opacity-10 text-danger">⚠️</div>
                    </div>
                    <div class="small text-muted mt-2">
                        {{ $kpis['locked_classes'] }} classe(s) verrouillée(s)
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── GRAPHIQUES + TABLEAU ── --}}
    <div class="row g-4 mb-4">
        {{-- Graphique distribution notes --}}
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="fw-semibold mb-0">📊 Distribution des moyennes</h6>
                </div>
                <div class="card-body">
                    <canvas id="gradeDistChart" height="220"></canvas>
                </div>
            </div>
        </div>

        {{-- Matières avec le plus d'échecs --}}
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="fw-semibold mb-0">🔴 Matières avec le plus d'échecs</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Matière</th>
                                <th class="text-center">Nb échecs</th>
                                <th class="text-center">Moy. classe</th>
                                <th>Alerte</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($criticalSubjects as $cs)
                            <tr>
                                <td class="fw-semibold">{{ $cs['subject'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-danger">{{ $cs['count'] }}</span>
                                </td>
                                <td class="text-center">{{ $cs['avg_score'] }}/20</td>
                                <td>
                                    @if($cs['avg_score'] < 7)
                                        <span class="badge bg-danger">Critique</span>
                                    @elseif($cs['avg_score'] < 10)
                                        <span class="badge bg-warning text-dark">Faible</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Aucune matière critique 🎉</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SUIVI PAR CLASSE ── --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between">
                    <h6 class="fw-semibold mb-0">🏫 Suivi par classe</h6>
                    <small class="text-muted">Cliquer sur une classe pour les détails</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Classe</th>
                                    <th class="text-center">Élèves</th>
                                    <th>Complétion</th>
                                    <th class="text-center">Moy.</th>
                                    <th class="text-center">Statut</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($classesCompletion as $cc)
                                <tr class="cursor-pointer" onclick="showClassDetails({{ $cc['class_id'] }})">
                                    <td class="fw-semibold">{{ $cc['class_name'] }}</td>
                                    <td class="text-center">{{ $cc['students'] }}</td>
                                    <td style="min-width: 200px">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1 completion-bar">
                                                <div class="progress-bar {{ $cc['completion'] >= 100 ? 'bg-success' : ($cc['completion'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                     style="width:{{ $cc['completion'] }}%"></div>
                                            </div>
                                            <span class="small text-muted">{{ $cc['completion'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold {{ $cc['avg_grade'] >= 10 ? 'text-success' : 'text-danger' }}">
                                            {{ $cc['avg_grade'] ?: '—' }}/20
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($cc['is_locked'])
                                            <span class="badge bg-secondary">🔒 Verrouillé</span>
                                        @elseif($cc['completion'] >= 100)
                                            <span class="badge bg-success">✓ Complet</span>
                                        @elseif($cc['completion'] > 0)
                                            <span class="badge bg-warning text-dark">En cours</span>
                                        @else
                                            <span class="badge bg-danger">Non démarré</span>
                                        @endif
                                    </td>
                                    <td>
                                        <i class="fas fa-chevron-right text-muted small"></i>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Aucune classe active.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── DRAG & DROP AFFECTATIONS ── --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-semibold mb-0">👨‍🏫 Affectations Professeurs (Drag & Drop)</h6>
                    <small class="text-muted">Glissez un professeur vers une matière pour l'affecter</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Liste des professeurs --}}
                        <div class="col-md-3">
                            <h6 class="text-muted small text-uppercase fw-semibold mb-2">Professeurs disponibles</h6>
                            <div id="teachersList">
                                @foreach($teachers as $teacher)
                                <div class="assignment-card p-2 mb-2 bg-light"
                                     draggable="true"
                                     data-teacher-id="{{ $teacher->id }}"
                                     data-teacher-name="{{ $teacher->user->name }}"
                                     ondragstart="dragTeacher(event)">
                                    <i class="fas fa-user-tie text-primary me-2"></i>
                                    <span class="small fw-semibold">{{ $teacher->user->name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Grille des classes × matières --}}
                        <div class="col-md-9">
                            <h6 class="text-muted small text-uppercase fw-semibold mb-2">Affectations par classe</h6>
                            <div class="accordion" id="classesAccordion">
                                @foreach($classes as $classe)
                                <div class="accordion-item border mb-2">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#class{{ $classe->id }}">
                                            <strong>{{ $classe->name }}</strong>
                                        </button>
                                    </h2>
                                    <div id="class{{ $classe->id }}" class="accordion-collapse collapse"
                                         data-bs-parent="#classesAccordion">
                                        <div class="accordion-body p-2">
                                            @foreach($classe->classSubjectTeachers as $cst)
                                            <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-1 drop-zone"
                                                 data-cst-id="{{ $cst->id }}"
                                                 ondragover="allowDrop(event)"
                                                 ondrop="dropTeacher(event, {{ $cst->id }})">
                                                <span class="small">📚 <strong>{{ $cst->subject?->name }}</strong></span>
                                                <span class="small text-muted" id="teacher-cst-{{ $cst->id }}">
                                                    {{ $cst->teacher?->user?->name ?? '— Non affecté —' }}
                                                </span>
                                            </div>
                                            @endforeach
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
    </div>

</div>

{{-- ── MODAL DÉTAILS CLASSE ── --}}
<div class="modal fade" id="classDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="classDetailTitle">Détails de la classe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="classDetailBody">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
// ── Configuration globale ──
const BASE_URL   = '{{ url('') }}';
const CSRF_TOKEN = '{{ csrf_token() }}';
let currentTerm = {{ $term }};
let currentYear = '{{ $academicYear }}';

// ── Graphique Distribution ──
const distCtx = document.getElementById('gradeDistChart').getContext('2d');
const distData = @json($gradeDistribution);

new Chart(distCtx, {
    type: 'bar',
    data: {
        labels: distData.map(d => d.label),
        datasets: [{
            label: 'Nombre d\'élèves',
            data:  distData.map(d => d.count),
            backgroundColor: distData.map(d => d.color + 'cc'),
            borderColor: distData.map(d => d.color),
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// ── Refresh dashboard ──
async function refreshDashboard() {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('spin');
    currentTerm = document.getElementById('termSelector').value;
    currentYear = document.getElementById('yearSelector').value;

    try {
        const res  = await fetch(`${BASE_URL}/admin/kpi/refresh?term=${currentTerm}&academic_year=${currentYear}`);
        const data = await res.json();

        document.getElementById('kpiCompletion').textContent = data.kpis.global_completion + '%';
        document.getElementById('completionBar').style.width  = data.kpis.global_completion + '%';
        document.getElementById('kpiAvg').textContent = data.kpis.global_avg + '/20';
        document.getElementById('kpiAlerts').textContent = data.kpis.critical_alerts;
    } catch(e) {
        console.error(e);
    } finally {
        icon.classList.remove('spin');
    }
}

// ── Détails classe ──
async function showClassDetails(classId) {
    const modal = new bootstrap.Modal(document.getElementById('classDetailModal'));
    document.getElementById('classDetailBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    modal.show();

    const res  = await fetch(`${BASE_URL}/admin/kpi/class/${classId}?term=${currentTerm}&academic_year=${currentYear}`);
    const data = await res.json();

    document.getElementById('classDetailTitle').textContent = data.class_name;
    document.getElementById('classDetailBody').innerHTML = `
        <div class="row g-3 mb-3">
            <div class="col-4 text-center"><strong>${data.students}</strong><br><small>Élèves</small></div>
            <div class="col-4 text-center"><strong>${data.avg_grade}/20</strong><br><small>Moy. classe</small></div>
            <div class="col-4 text-center"><strong>${data.pass_rate}%</strong><br><small>Taux réussite</small></div>
        </div>
        <table class="table table-sm">
            <thead><tr><th>Matière</th><th>Prof</th><th>Complétion</th><th>Dernière saisie</th></tr></thead>
            <tbody>
                ${data.subjects.map(s => `
                <tr>
                    <td><strong>${s.subject}</strong></td>
                    <td>${s.teacher}</td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <div class="progress flex-grow-1" style="height:6px">
                                <div class="progress-bar ${s.completion >= 100 ? 'bg-success' : s.completion >= 50 ? 'bg-warning' : 'bg-danger'}"
                                     style="width:${s.completion}%"></div>
                            </div>
                            <small>${s.completion}%</small>
                        </div>
                    </td>
                    <td><small class="text-muted">${s.last_entry || '—'}</small></td>
                </tr>`).join('')}
            </tbody>
        </table>
        ${data.is_locked ? '<span class="badge bg-secondary">🔒 Classe verrouillée</span>' : ''}
    `;
}

// ── Drag & Drop Affectations ──
let draggedTeacherId   = null;
let draggedTeacherName = null;

function dragTeacher(e) {
    draggedTeacherId   = e.target.dataset.teacherId;
    draggedTeacherName = e.target.dataset.teacherName;
    e.target.classList.add('dragging');
}

function allowDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over');
}

async function dropTeacher(e, cstId) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');

    if (!draggedTeacherId) return;

    try {
        const res = await fetch(`${BASE_URL}/admin/kpi/assign-teacher`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ class_subject_teacher_id: cstId, teacher_id: draggedTeacherId })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById(`teacher-cst-${cstId}`).textContent = draggedTeacherName;
            showToast('success', data.message);
        }
    } catch(err) {
        showToast('danger', 'Erreur lors de l\'affectation.');
    }

    draggedTeacherId = null;
}

// Enlever la classe drag-over quand on quitte
document.querySelectorAll('.drop-zone').forEach(z => {
    z.addEventListener('dragleave', () => z.classList.remove('drag-over'));
});

// ── Toast helper ──
function showToast(type, message) {
    const t = document.createElement('div');
    t.className = `toast align-items-center text-white bg-${type} border-0 show position-fixed bottom-0 end-0 m-3`;
    t.style.zIndex = 9999;
    t.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button></div>`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}

// Auto-refresh toutes les 2 minutes
setInterval(refreshDashboard, 120000);
</script>
@endpush

