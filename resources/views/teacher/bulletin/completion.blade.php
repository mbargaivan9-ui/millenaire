@extends('layouts.app')

@section('title', 'Tableau de Complétion')

@push('styles')
    <style>
        .completion-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            transition: all .2s;
        }
        .completion-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); }

        .progress-ring {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .bar-container { height: 10px; background:#f1f5f9; border-radius:99px; overflow:hidden; }
        .bar-fill { height: 100%; border-radius:99px; transition: width 1s ease; }

        .status-done    { background: linear-gradient(90deg,#10b981,#34d399); }
        .status-partial { background: linear-gradient(90deg,#f59e0b,#fbbf24); }
        .status-empty   { background: linear-gradient(90deg,#ef4444,#f87171); }

        .ring-done    { background:#dcfce7; color:#166534; }
        .ring-partial { background:#fef3c7; color:#92400e; }
        .ring-empty   { background:#fee2e2; color:#991b1b; }

        .notify-btn {
            background: none;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 5px 12px;
            font-size: .78rem;
            color: #64748b;
            cursor: pointer;
            transition: all .15s;
        }
        .notify-btn:hover { background:#f0f4ff; border-color:#4F46E5; color:#4F46E5; }

        .lock-section {
            background: linear-gradient(135deg,#1e293b,#334155);
            border-radius: 14px;
            color: #fff;
        }
    </style>
    @endpush

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.bulletin.index') }}">Bulletins</a></li>
                    <li class="breadcrumb-item active">Complétion — {{ $classe->name }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0" style="color:#1e293b">
                <i class="fas fa-chart-bar me-2 text-primary"></i>
                Tableau de complétion · {{ $classe->name }}
            </h2>
            <p class="text-muted mb-0 small">
                Trimestre {{ $term }} · Séquence {{ $sequence }} · {{ $academicYear }}
                &bull; {{ $students->count() }} élèves
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <select id="sel-term" class="form-select form-select-sm" style="width:auto">
                @for($t=1;$t<=3;$t++)
                    <option value="{{ $t }}" {{ $t==$term?'selected':'' }}>Trim. {{ $t }}</option>
                @endfor
            </select>
            <select id="sel-seq" class="form-select form-select-sm" style="width:auto">
                <option value="1" {{ $sequence==1?'selected':'' }}>Séq. 1</option>
                <option value="2" {{ $sequence==2?'selected':'' }}>Séq. 2</option>
            </select>
        </div>
    </div>

    {{-- ── RÉSUMÉ GLOBAL ───────────────────────────────────────────── --}}
    @php
        $totalSubjects   = count($completionData);
        $doneSubjects    = collect($completionData)->where('percentage', 100)->count();
        $overallPct      = $totalSubjects > 0
            ? round(collect($completionData)->avg('percentage'), 1)
            : 0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-1 fw-black text-primary mb-1">{{ $overallPct }}%</div>
                <div class="text-muted small text-uppercase fw-semibold">Complétion globale</div>
                <div class="bar-container mt-2">
                    <div class="bar-fill {{ $overallPct >= 100 ? 'status-done' : ($overallPct >= 50 ? 'status-partial' : 'status-empty') }}"
                         style="width:{{ min($overallPct,100) }}%"></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-1 fw-black text-success mb-1">{{ $doneSubjects }}</div>
                <div class="text-muted small text-uppercase fw-semibold">Matières complètes</div>
                <div class="text-muted small">/ {{ $totalSubjects }} matières</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-1 fw-black {{ $isLocked ? 'text-warning' : 'text-danger' }} mb-1">
                    <i class="fas {{ $isLocked ? 'fa-lock' : 'fa-lock-open' }}"></i>
                </div>
                <div class="text-muted small text-uppercase fw-semibold">
                    {{ $isLocked ? 'Trimestre verrouillé' : 'Saisie ouverte' }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── LISTE MATIÈRES ──────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        @foreach($completionData as $item)
        <div class="col-md-6">
            <div class="completion-card p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="progress-ring ring-{{ $item['status_color'] === 'success' ? 'done' : ($item['status_color'] === 'warning' ? 'partial' : 'empty') }}">
                        {{ $item['percentage'] }}%
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="fw-bold">{{ $item['subject_name'] }}</span>
                            <span class="badge bg-{{ $item['status_color'] }}">{{ $item['status_label'] }}</span>
                        </div>
                        <div class="text-muted small mb-2">
                            <i class="fas fa-user me-1"></i>{{ $item['teacher_name'] }}
                            &nbsp;·&nbsp;
                            {{ $item['filled'] }}/{{ $item['total'] }} élèves
                        </div>
                        <div class="bar-container">
                            <div class="bar-fill status-{{ $item['status_color'] === 'success' ? 'done' : ($item['status_color'] === 'warning' ? 'partial' : 'empty') }}"
                                 style="width:{{ min($item['percentage'],100) }}%"></div>
                        </div>
                    </div>
                    @if($item['percentage'] < 100)
                    <button class="notify-btn"
                            onclick="sendReminder({{ $item['cst_id'] }}, '{{ addslashes($item['teacher_name']) }}', '{{ addslashes($item['subject_name']) }}')"
                            title="Envoyer un rappel au professeur">
                        <i class="fas fa-bell me-1"></i>Relancer
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        @if(empty($completionData))
        <div class="col-12 text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x opacity-25 mb-3"></i>
            <p>Aucune matière trouvée pour cette classe.</p>
        </div>
        @endif
    </div>

    {{-- ── SECTION VERROUILLAGE ────────────────────────────────────── --}}
    <div class="lock-section p-4 mt-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="fas {{ $isLocked ? 'fa-lock text-warning' : 'fa-lock-open text-success' }} me-2"></i>
                    {{ $isLocked ? 'Classe verrouillée' : 'Verrouiller la saisie' }}
                </h5>
                <p class="mb-0 opacity-70 small">
                    @if($isLocked)
                        Le trimestre {{ $term }} est verrouillé. Les professeurs ne peuvent plus modifier les notes.
                        Vous pouvez déverrouiller si nécessaire.
                    @else
                        Une fois verrouillé, aucun professeur ne pourra modifier les notes du trimestre {{ $term }}.
                        Vous serez le seul à pouvoir déverrouiller.
                    @endif
                </p>
            </div>
            <div>
                @if($isLocked)
                <button class="btn btn-warning fw-bold" id="btn-unlock">
                    <i class="fas fa-lock-open me-2"></i>Déverrouiller le trimestre
                </button>
                @else
                <button class="btn btn-danger fw-bold" id="btn-lock"
                        {{ $overallPct < 80 ? 'title=Attention : moins de 80% des notes sont saisies.' : '' }}>
                    <i class="fas fa-lock me-2"></i>Verrouiller le trimestre {{ $term }}
                </button>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (() => {
        const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
        const CLS_ID = {{ $classe->id }};
        const TERM   = {{ $term }};
        const YEAR   = "{{ $academicYear }}";

        const LOCK_URL   = `/teacher/bulletin/${CLS_ID}/lock`;
        const UNLOCK_URL = `/teacher/bulletin/${CLS_ID}/unlock`;
        const NOTIFY_URL = "{{ route('api.bulletin.notify') ?? '#' }}";

        // ── Sélecteurs ──────────────────────────────────────────
        ['sel-term','sel-seq'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', () => {
                const t = document.getElementById('sel-term').value;
                const s = document.getElementById('sel-seq').value;
                window.location.href = `/teacher/bulletin/${CLS_ID}/completion?term=${t}&sequence=${s}&academic_year=${YEAR}`;
            });
        });

        // ── Verrouillage ─────────────────────────────────────────
        document.getElementById('btn-lock')?.addEventListener('click', () => {
            if (!confirm(`Verrouiller le trimestre ${TERM} de {{ $classe->name }} ?\n\nLes professeurs ne pourront plus modifier les notes.`)) return;
            postAction(LOCK_URL, {term:TERM, academic_year:YEAR, reason:'Clôture manuelle par le Prof Principal'});
        });

        document.getElementById('btn-unlock')?.addEventListener('click', () => {
            if (!confirm('Déverrouiller le trimestre ? Les professeurs pourront à nouveau modifier les notes.')) return;
            postAction(UNLOCK_URL, {term:TERM, academic_year:YEAR});
        });

        async function postAction(url, body) {
            const r = await fetch(url, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                body: JSON.stringify(body)
            });
            const j = await r.json();
            if (j.success) { showToast(j.message,'success'); setTimeout(()=>location.reload(),1500); }
            else showToast(j.error || 'Erreur','danger');
        }

        // ── Relancer un prof ──────────────────────────────────────
        window.sendReminder = function(cstId, teacherName, subjectName) {
            if (!confirm(`Envoyer un rappel à ${teacherName} pour la saisie des notes de ${subjectName} ?`)) return;
            showToast(`Rappel envoyé à ${teacherName} pour ${subjectName}.`, 'info');
            // TODO: Appeler l'API de notification quand implémentée
        };

        // ── Toast ─────────────────────────────────────────────────
        function showToast(msg, type) {
            const el = document.createElement('div');
            el.className = `toast align-items-center text-bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
            el.style.zIndex='9999';
            el.innerHTML = `<div class="d-flex"><div class="toast-body fw-semibold">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
            document.body.appendChild(el);
            new bootstrap.Toast(el,{delay:4000}).show();
            el.addEventListener('hidden.bs.toast',()=>el.remove());
        }
    })();
    </script>
    @endpush

@section('content')

{{-- Page Header --}}
<div class="page-header mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#0EA5E9,#0284C7)">
                <i data-lucide="chart-bar"></i>
            </div>
            <div>
                <h1 class="page-title">Tableau de Complétion</h1>
                <p class="page-subtitle text-muted">{{ $classe->name ?? 'Classe' }} — Vue globale de la saisie des notes</p>
            </div>
        </div>
    </div>
</div>
