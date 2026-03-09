{{-- resources/views/teacher/advanced/dashboard.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Mon espace enseignant — Millenaire')

@push('styles')
<style>
    .completion-mini { height: 6px; border-radius: 3px; }
    .assignment-row { border-left: 3px solid transparent; transition: all .15s; }
    .assignment-row:hover { background: #f8f9fa; border-left-color: #0d6efd; }
    .stat-chip { background: #e9ecef; border-radius: 20px; padding: 2px 10px; font-size: .75rem; font-weight: 600; }
    .bulk-paste-area { font-family: monospace; font-size: .85rem; resize: vertical; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">

    {{-- EN-TÊTE --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">👨‍🏫 Mon espace enseignant</h1>
            <p class="text-muted mb-0">Trimestre {{ $term }} — {{ $academicYear }}</p>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" style="width:auto" onchange="changePeriod('term', this.value)">
                @for($t=1;$t<=3;$t++)<option value="{{ $t }}" {{ $t==$term?'selected':'' }}>Trimestre {{ $t }}</option>@endfor
            </select>
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                <i class="fas fa-file-import me-1"></i> Import en masse
            </button>
        </div>
    </div>

    {{-- MES MATIÈRES : COMPLÉTION --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-transparent border-0">
            <h6 class="fw-semibold mb-0">📊 Mes matières — Avancement des saisies</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Classe</th>
                            <th>Matière</th>
                            <th>Séq.1</th>
                            <th>Séq.2</th>
                            <th class="text-center">Moy. classe</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($completionStats as $stat)
                        <tr class="assignment-row">
                            <td><strong>{{ $stat['class'] }}</strong></td>
                            <td>{{ $stat['subject'] }}</td>
                            <td style="min-width:140px">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1 completion-mini">
                                        <div class="progress-bar {{ $stat['seq1_pct'] >= 100 ? 'bg-success' : ($stat['seq1_pct'] > 0 ? 'bg-warning' : 'bg-danger') }}"
                                             style="width:{{ $stat['seq1_pct'] }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $stat['seq1_filled'] }}/{{ $stat['total'] }}</small>
                                </div>
                            </td>
                            <td style="min-width:140px">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1 completion-mini">
                                        <div class="progress-bar {{ $stat['seq2_pct'] >= 100 ? 'bg-success' : ($stat['seq2_pct'] > 0 ? 'bg-warning' : 'bg-danger') }}"
                                             style="width:{{ $stat['seq2_pct'] }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $stat['seq2_filled'] }}/{{ $stat['total'] }}</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold {{ ($stat['avg_score'] ?? 0) >= 10 ? 'text-success' : 'text-danger' }}">
                                    {{ $stat['avg_score'] ?? '—' }}/20
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @php
                                    $cst = $assignments->firstWhere('id', $stat['cst_id']);
                                    @endphp
                                    @if($cst)
                                    <a href="{{ route('teacher.bulletin.grid', ['classSubjectTeacher' => $stat['cst_id'], 'term' => $term, 'sequence' => 1]) }}"
                                       class="btn btn-xs btn-outline-primary" title="Grille Séq.1">S1</a>
                                    <a href="{{ route('teacher.bulletin.grid', ['classSubjectTeacher' => $stat['cst_id'], 'term' => $term, 'sequence' => 2]) }}"
                                       class="btn btn-xs btn-outline-primary" title="Grille Séq.2">S2</a>
                                    <button class="btn btn-xs btn-outline-secondary"
                                            onclick="loadStats({{ $stat['cst_id'] }})" title="Statistiques">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- STATISTIQUES MATIÈRE --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-semibold mb-0" id="statsTitle">📈 Statistiques d'une matière</h6>
                </div>
                <div class="card-body">
                    <div id="statsContent" class="text-center text-muted py-5">
                        <i class="fas fa-chart-bar fa-3x mb-3 opacity-25"></i>
                        <div>Cliquer sur <i class="fas fa-chart-bar"></i> d'une matière pour voir les stats</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- HISTOGRAMME --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-semibold mb-0">📊 Distribution des notes</h6>
                </div>
                <div class="card-body">
                    <canvas id="histoChart" height="200" style="display:none"></canvas>
                    <div id="histoPlaceholder" class="text-center text-muted py-5">
                        <i class="fas fa-chart-bar fa-3x mb-3 opacity-25"></i>
                        <div>Sélectionnez une matière</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── MODAL IMPORT EN MASSE ── --}}
<div class="modal fade" id="bulkImportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📥 Import en masse de notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    Collez le tableau de notes ci-dessous.
                    Format attendu : <code>ID_ÉLÈVE;NOTE;COMMENTAIRE</code> (une ligne par élève).
                    Exemple : <code>12;14.5;Bon travail</code>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Matière</label>
                        <select id="bulkCstId" class="form-select form-select-sm">
                            <option value="">— Choisir —</option>
                            @foreach($assignments as $cst)
                            <option value="{{ $cst->id }}">{{ $cst->classe?->name }} — {{ $cst->subject?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Trimestre</label>
                        <select id="bulkTerm" class="form-select form-select-sm">
                            @for($t=1;$t<=3;$t++)<option value="{{ $t }}" {{ $t==$term?'selected':'' }}>Trim. {{ $t }}</option>@endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Séquence</label>
                        <select id="bulkSequence" class="form-select form-select-sm">
                            <option value="1">Séquence 1</option>
                            <option value="2">Séquence 2</option>
                        </select>
                    </div>
                </div>

                <textarea id="bulkPasteArea" class="form-control bulk-paste-area mb-3"
                          rows="8" placeholder="12;14.5;Bon travail&#10;13;11;Peut mieux faire&#10;14;8;Insuffisant"></textarea>

                <div id="bulkResult" style="display:none"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-primary" onclick="submitBulkImport()">
                    <i class="fas fa-save me-1"></i> Enregistrer les notes
                </button>
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
const TERM       = {{ $term }};
const YEAR       = '{{ $academicYear }}';
let histoChart   = null;

// ── Charger les stats d'une matière ──
async function loadStats(cstId) {
    document.getElementById('statsContent').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    document.getElementById('histoPlaceholder').style.display = 'none';
    document.getElementById('histoChart').style.display = 'block';

    const res  = await fetch(`${BASE_URL}/teacher/bulletin/stats/${cstId}?term=${TERM}&academic_year=${YEAR}`);
    const data = await res.json();

    if (data.error) {
        document.getElementById('statsContent').innerHTML = `<div class="text-danger">${data.error}</div>`;
        return;
    }

    document.getElementById('statsContent').innerHTML = `
        <div class="row g-3 text-center">
            <div class="col-4"><div class="h4 fw-bold text-primary">${data.avg}/20</div><small>Moyenne</small></div>
            <div class="col-4"><div class="h4 fw-bold text-success">${data.pass_rate}%</div><small>Admis</small></div>
            <div class="col-4"><div class="h4 fw-bold">${data.count}</div><small>Élèves notés</small></div>
            <div class="col-4"><div class="fw-bold">${data.min}/20</div><small class="text-muted">Min</small></div>
            <div class="col-4"><div class="fw-bold">${data.max}/20</div><small class="text-muted">Max</small></div>
            <div class="col-4"><div class="fw-bold">${data.std_dev}</div><small class="text-muted">Éc. type</small></div>
        </div>
    `;

    // Histogramme
    const ctx = document.getElementById('histoChart').getContext('2d');
    if (histoChart) histoChart.destroy();

    histoChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.histogram.map(h => h.label),
            datasets: [{
                label: 'Élèves',
                data:  data.histogram.map(h => h.count),
                backgroundColor: data.histogram.map((_, i) => {
                    const idx = Math.floor(i / (data.histogram.length / 6));
                    const colors = ['#dc3545','#fd7e14','#ffc107','#20c997','#0d6efd','#198754'];
                    return colors[Math.min(idx, 5)] + 'cc';
                }),
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

// ── Import en masse ──
async function submitBulkImport() {
    const cstId    = document.getElementById('bulkCstId').value;
    const term     = document.getElementById('bulkTerm').value;
    const sequence = document.getElementById('bulkSequence').value;
    const text     = document.getElementById('bulkPasteArea').value.trim();

    if (!cstId || !text) {
        alert('Veuillez choisir une matière et coller les notes.');
        return;
    }

    const entries = [];
    const lines   = text.split('\n').filter(l => l.trim());

    for (const line of lines) {
        const parts = line.split(';');
        if (parts.length < 2) continue;
        entries.push({
            student_id: parseInt(parts[0]),
            score:      parseFloat(parts[1]) || null,
            comment:    parts[2]?.trim() || null,
        });
    }

    const res  = await fetch(`${BASE_URL}/teacher/bulletin/bulk-save`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body:    JSON.stringify({
            class_subject_teacher_id: cstId,
            term, sequence,
            academic_year: YEAR,
            entries,
        })
    });
    const data = await res.json();

    const el = document.getElementById('bulkResult');
    el.style.display = 'block';
    el.innerHTML = `
        <div class="alert ${data.errors?.length ? 'alert-warning' : 'alert-success'}">
            <strong>${data.saved} notes enregistrées</strong><br>
            ${data.errors?.length ? data.errors.map(e => `Ligne ${e.row}: ${e.message}`).join('<br>') : 'Import réussi !'}
        </div>
    `;
}

function changePeriod(key, val) {
    const url = new URL(window.location.href);
    url.searchParams.set(key, val);
    window.location.href = url.toString();
}
</script>
@endpush
