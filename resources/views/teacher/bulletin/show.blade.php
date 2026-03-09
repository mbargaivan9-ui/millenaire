@extends('layouts.app')

@section('title', 'Bulletin — ' . ($student->user->name ?? 'Étudiant'))

@push('styles')
    <style>
        /* ─── Fond page ──────────────────────────────────────── */
        .bulletin-page {
            background: #f0f4f8;
            min-height: 100vh;
        }

        /* ─── Barre de navigation élèves ─────────────────────── */
        .nav-bar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .nav-btn {
            background: #4F46E5;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 14px;
            font-weight: 600;
            font-size: .85rem;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background .15s;
        }
        .nav-btn:hover { background:#3730A3; }
        .nav-btn:disabled { background:#c7d2fe; cursor:not-allowed; }
        .progress-label { color:#64748b; font-size:.85rem; font-weight:600; }

        /* ─── Quick-Filter flottant ───────────────────────────── */
        .qf-wrap { position: relative; }
        .qf-input {
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            padding: 6px 12px 6px 34px;
            font-size: .85rem;
            width: 200px;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%2364748b' stroke-width='2' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") no-repeat 8px center / 16px;
            transition: all .15s;
        }
        .qf-input:focus { border-color:#4F46E5; outline:none; width:240px; background-color:#fff; }
        .qf-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
            width: 280px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 200;
            display: none;
        }
        .qf-item {
            padding: 8px 14px;
            cursor: pointer;
            font-size: .85rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background .1s;
        }
        .qf-item:hover { background: #f0f4ff; }
        .qf-matricule { color:#94a3b8; font-size:.75rem; }

        /* ─── Bulletin Card ───────────────────────────────────── */
        .bulletin-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0,0,0,.08);
            overflow: hidden;
            max-width: 900px;
            margin: 0 auto;
        }

        /* ─── En-tête bulletin ────────────────────────────────── */
        .bulletin-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            color: #fff;
            padding: 28px 32px 20px;
        }
        .school-name { font-size: 1.3rem; font-weight: 800; letter-spacing: .02em; }
        .bulletin-title { font-size: 1rem; opacity: .85; font-weight: 400; }
        .student-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            margin-top: 16px;
            background: rgba(255,255,255,.12);
            border-radius: 10px;
            padding: 14px 18px;
        }
        .info-item label { font-size: .7rem; opacity: .7; text-transform: uppercase; letter-spacing: .07em; display: block; }
        .info-item span  { font-weight: 700; font-size: 1rem; }

        /* ─── Tableau des notes ───────────────────────────────── */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
        }
        .grades-table th {
            background: #f8fafc;
            color: #475569;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: 10px 14px;
            border-bottom: 2px solid #e2e8f0;
        }
        .grades-table td {
            padding: 9px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-size: .88rem;
        }
        .grades-table tbody tr:hover { background: #f8faff; }

        /* Ligne de matière éditable vs lecture seule */
        .row-editable  { background: #fafbff !important; }
        .row-readonly  { background: #f9fafb; }
        .row-readonly td { color: #94a3b8 !important; }
        .row-readonly .score-cell { opacity: .5; }

        /* Champ note dans le bulletin */
        .live-score {
            width: 68px;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 5px 6px;
            font-weight: 700;
            font-size: .95rem;
            transition: all .15s;
            background: #fff;
        }
        .live-score:focus { border-color:#4F46E5; outline:none; box-shadow:0 0 0 3px rgba(79,70,229,.12); }
        .live-score.saving { border-color:#f59e0b !important; }
        .live-score.saved  { border-color:#10b981 !important; }
        .live-score.error  { border-color:#ef4444 !important; }
        .live-score.focused-col { background:#eff6ff; border-color:#93c5fd; }
        .live-score:disabled { background:#f1f5f9; cursor:not-allowed; border-color:#e5e7eb; color:#94a3b8; }
        .score-readonly { font-weight: 700; color:#1e293b; }

        /* Moyenne matière calculée */
        .mat-avg {
            font-weight: 800;
            font-size: 1rem;
            padding: 3px 10px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #1e293b;
            display: inline-block;
            transition: all .3s;
        }
        .mat-avg.passing  { background:#dcfce7; color:#166534; }
        .mat-avg.failing  { background:#fee2e2; color:#991b1b; }

        /* ─── Appréciation suggestion ─────────────────────────── */
        .appreciation-card {
            border-radius: 10px;
            padding: 8px 14px;
            font-size: .8rem;
            border: 1px solid;
            transition: all .3s;
            cursor: pointer;
        }
        .appreciation-card:hover { transform:scale(1.02); }

        /* ─── Résumé bas de page ──────────────────────────────── */
        .summary-bar {
            background: #1e293b;
            color: #fff;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }
        .summary-item label { font-size:.7rem; opacity:.6; display:block; text-transform:uppercase; letter-spacing:.07em; }
        .summary-item span  { font-weight:800; font-size:1.3rem; }
        .avg-display { font-size:2rem; font-weight:900; }

        /* ─── Lock badge ──────────────────────────────────────── */
        .lock-badge {
            background:#fef3c7;
            color:#92400e;
            border:1px solid #fcd34d;
            border-radius:20px;
            padding:3px 12px;
            font-size:.75rem;
            font-weight:700;
        }
    </style>
    @endpush

    {{-- ── BARRE DE NAVIGATION ────────────────────────────────────── --}}
    <div class="nav-bar">
        {{-- Prev/Next --}}
        @if($prevStudent)
        <a href="{{ route('teacher.bulletin.show', [$classe->id, $prevStudent->id]) }}?term={{ $term }}&academic_year={{ $academicYear }}"
           class="nav-btn">
            <i class="fas fa-chevron-left"></i> Précédent
        </a>
        @else
        <button class="nav-btn" disabled><i class="fas fa-chevron-left"></i> Précédent</button>
        @endif

        <span class="progress-label">
            <strong>{{ $currentIndex + 1 }}</strong> / {{ $totalStudents }}
        </span>

        @if($nextStudent)
        <a href="{{ route('teacher.bulletin.show', [$classe->id, $nextStudent->id]) }}?term={{ $term }}&academic_year={{ $academicYear }}"
           class="nav-btn" id="btn-next">
            Suivant <i class="fas fa-chevron-right"></i>
        </a>
        @else
        <button class="nav-btn" disabled>Suivant <i class="fas fa-chevron-right"></i></button>
        @endif

        {{-- Quick-Filter --}}
        <div class="qf-wrap ms-3">
            <input type="text" class="qf-input" id="qf-input" placeholder="Rechercher élève…" autocomplete="off">
            <div class="qf-dropdown" id="qf-dropdown"></div>
        </div>

        {{-- Sélecteurs --}}
        <select id="sel-term" class="form-select form-select-sm ms-2" style="width:auto">
            @for($t=1;$t<=3;$t++)
                <option value="{{ $t }}" {{ $t==$term?'selected':'' }}>Trimestre {{ $t }}</option>
            @endfor
        </select>

        {{-- Mode Grille --}}
        @php $cstId = $classe->classSubjectTeachers->where('is_active', true)->first()?->id; @endphp
        @if($cstId)
        <a href="{{ route('teacher.bulletin.grid', $cstId) }}?term={{ $term }}&sequence=1&academic_year={{ $academicYear }}"
           class="btn btn-outline-secondary btn-sm ms-1">
            <i class="fas fa-table me-1"></i>Mode Grille
        </a>
        @endif

        {{-- Lock status --}}
        @if($bulletinData['is_locked'])
        <span class="lock-badge ms-auto"><i class="fas fa-lock me-1"></i>Trimestre verrouillé</span>
        @endif

        {{-- Prof Principal : Tableau de complétion --}}
        @if($isPrincipal)
        <a href="{{ route('teacher.bulletin.completion', $classe->id) }}?term={{ $term }}&academic_year={{ $academicYear }}"
           class="btn btn-sm btn-outline-primary ms-auto">
            <i class="fas fa-chart-bar me-1"></i>Complétion
        </a>
        @endif
    </div>

    <div class="p-3 p-md-4 bulletin-page">
        <div class="bulletin-card">

            {{-- ── EN-TÊTE DU BULLETIN ──────────────────────────────────── --}}
            <div class="bulletin-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <div class="school-name">MILLÉNAIRE CONNECT</div>
                        <div class="bulletin-title">Bulletin Scolaire — Trimestre {{ $term }} · {{ $academicYear }}</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-semibold opacity-80 small">Classe</div>
                        <div class="fw-bold fs-5">{{ $classe->name }}</div>
                    </div>
                </div>

                <div class="student-info-grid">
                    <div class="info-item">
                        <label>Nom & Prénom</label>
                        <span>{{ $student->user->name }}</span>
                    </div>
                    <div class="info-item">
                        <label>Matricule</label>
                        <span>{{ $student->matricule ?? '—' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Rang actuel</label>
                        <span id="live-rank">{{ $bulletinData['rank_display'] ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- ── TABLEAU DES NOTES ────────────────────────────────────── --}}
            <div class="p-0">
                <div class="table-responsive">
                    <table class="grades-table">
                        <thead>
                            <tr>
                                <th style="min-width:180px">Matière</th>
                                <th style="width:50px">Coeff</th>
                                <th style="width:80px" class="text-center">Prof</th>
                                <th style="width:90px" class="text-center">Séq. 1</th>
                                <th style="width:90px" class="text-center">Séq. 2</th>
                                <th style="width:90px" class="text-center">Moy.</th>
                                <th style="min-width:200px">Appréciation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bulletinData['subjects'] as $sub)
                            @php
                                $canEdit1 = in_array($sub['subject_id'], $editableSubjectIds) && !$bulletinData['is_locked'];
                                $canEdit1 = $canEdit1 || ($isPrincipal && !$bulletinData['is_locked']); // PP override
                                $canEdit2 = $canEdit1;
                            @endphp
                            <tr class="{{ in_array($sub['subject_id'], $editableSubjectIds) || $isPrincipal ? 'row-editable' : 'row-readonly' }}"
                                data-cst="{{ $sub['cst_id'] }}"
                                data-subj="{{ $sub['subject_id'] }}">
                                <td>
                                    <div class="fw-semibold">{{ $sub['subject_name'] }}</div>
                                    <div class="text-muted" style="font-size:.72rem">{{ $sub['subject_code'] }}</div>
                                </td>
                                <td class="text-center fw-semibold text-primary">{{ $sub['coefficient'] }}</td>
                                <td class="text-center text-muted small">
                                    {{ $sub['teacher_name'] !== 'N/A' ? explode(' ', $sub['teacher_name'])[0] : '—' }}
                                </td>
                                <td class="text-center score-cell" data-seq="1">
                                    @if($canEdit1 || $isPrincipal)
                                    <input type="number"
                                           class="live-score {{ in_array($sub['subject_id'], $editableSubjectIds) ? 'focused-col' : '' }}"
                                           data-student="{{ $student->id }}"
                                           data-cst="{{ $sub['cst_id'] }}"
                                           data-term="{{ $term }}"
                                           data-seq="1"
                                           data-year="{{ $academicYear }}"
                                           data-subj="{{ $sub['subject_id'] }}"
                                           value="{{ $sub['seq1_score'] !== null ? number_format($sub['seq1_score'], 2, '.', '') : '' }}"
                                           placeholder="—"
                                           min="0" max="20" step="0.25"
                                           {{ (!$canEdit1 && !$isPrincipal) ? 'disabled' : '' }}>
                                    @else
                                    <span class="score-readonly">
                                        {{ $sub['seq1_score'] !== null ? number_format($sub['seq1_score'], 2) : '—' }}
                                    </span>
                                    @endif
                                </td>
                                <td class="text-center score-cell" data-seq="2">
                                    @if($canEdit2 || $isPrincipal)
                                    <input type="number"
                                           class="live-score {{ in_array($sub['subject_id'], $editableSubjectIds) ? 'focused-col' : '' }}"
                                           data-student="{{ $student->id }}"
                                           data-cst="{{ $sub['cst_id'] }}"
                                           data-term="{{ $term }}"
                                           data-seq="2"
                                           data-year="{{ $academicYear }}"
                                           data-subj="{{ $sub['subject_id'] }}"
                                           value="{{ $sub['seq2_score'] !== null ? number_format($sub['seq2_score'], 2, '.', '') : '' }}"
                                           placeholder="—"
                                           min="0" max="20" step="0.25"
                                           {{ (!$canEdit2 && !$isPrincipal) ? 'disabled' : '' }}>
                                    @else
                                    <span class="score-readonly">
                                        {{ $sub['seq2_score'] !== null ? number_format($sub['seq2_score'], 2) : '—' }}
                                    </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="mat-avg {{ ($sub['subject_average'] ?? 0) >= 10 ? 'passing' : (($sub['subject_average'] ?? null) !== null ? 'failing' : '') }}"
                                          id="mat-avg-{{ $sub['cst_id'] }}">
                                        {{ $sub['subject_average'] !== null ? number_format($sub['subject_average'], 2) : '—' }}
                                    </span>
                                </td>
                                <td>
                                    @if($sub['auto_appreciation'])
                                    <span class="appreciation-card {{ $sub['auto_appreciation']['class'] }}"
                                          id="appr-{{ $sub['cst_id'] }}"
                                          title="Cliquez pour utiliser cette appréciation">
                                        {{ $sub['auto_appreciation']['emoji'] ?? '' }}
                                        {{ $sub['auto_appreciation']['text'] }}
                                    </span>
                                    @else
                                    <span class="text-muted small" id="appr-{{ $sub['cst_id'] }}">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── RÉSUMÉ DU BULLETIN ────────────────────────────────────── --}}
            <div class="summary-bar">
                <div class="summary-item">
                    <label>Moy. Séq. 1</label>
                    <span id="live-seq1">{{ $bulletinData['seq1_average'] !== null ? number_format($bulletinData['seq1_average'],2) : '—' }}</span>
                </div>
                <div class="summary-item">
                    <label>Moy. Séq. 2</label>
                    <span id="live-seq2">{{ $bulletinData['seq2_average'] !== null ? number_format($bulletinData['seq2_average'],2) : '—' }}</span>
                </div>
                <div class="ms-auto text-center">
                    <label style="font-size:.65rem;opacity:.6;text-transform:uppercase;letter-spacing:.1em;display:block">Moyenne Générale</label>
                    <span class="avg-display" id="live-term-avg">
                        {{ $bulletinData['term_average'] !== null ? number_format($bulletinData['term_average'],2) : '—' }}
                    </span>
                    <span style="opacity:.5">/20</span>
                </div>
                <div class="summary-item ms-4">
                    <label>Rang</label>
                    <span id="live-rank-bottom">{{ $bulletinData['rank_display'] ?? '—' }}</span>
                </div>
                <div class="summary-item">
                    <label>Mention</label>
                    <span id="live-mention">{{ $bulletinData['appreciation'] ?? '—' }}</span>
                </div>
            </div>

            {{-- ── OBSERVATION / COMMENTAIRE ─────────────────────────── --}}
            @if($isPrincipal)
            <div class="p-4 border-top">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Observation du Professeur Principal</label>
                        <textarea class="form-control" rows="3" id="principal-comment"
                                  placeholder="Commentaire général sur l'élève…">{{ $bulletinData['summary']?->principal_teacher_comment ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Observation générale</label>
                        <textarea class="form-control" rows="3" id="general-obs"
                                  placeholder="Ex: Félicitations pour les efforts…">{{ $bulletinData['summary']?->general_observation ?? '' }}</textarea>
                    </div>
                </div>
            </div>
            @endif

        </div>{{-- /.bulletin-card --}}
    </div>

    @push('scripts')
    <script>
    (() => {
        'use strict';

        const SAVE_URL   = "{{ route('teacher.bulletin.entry.save') }}";
        const SEARCH_URL = "{{ route('teacher.bulletin.students.search', $classe->id) }}";
        const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
        const IS_LOCKED  = @json($bulletinData['is_locked']);
        const IS_PP      = @json($isPrincipal);

        // ── Quick-Filter ─────────────────────────────────────────
        const qfInput    = document.getElementById('qf-input');
        const qfDropdown = document.getElementById('qf-dropdown');
        let qfTimer;

        qfInput?.addEventListener('input', () => {
            clearTimeout(qfTimer);
            qfTimer = setTimeout(() => {
                const q = qfInput.value.trim();
                if (!q) { qfDropdown.style.display='none'; return; }
                fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`, {headers:{'Accept':'application/json'}})
                    .then(r=>r.json())
                    .then(data => {
                        if (!data.students?.length) { qfDropdown.style.display='none'; return; }
                        qfDropdown.innerHTML = data.students.slice(0,8).map(s => `
                            <div class="qf-item" onclick="navigateTo(${s.id})">
                                <div>
                                    <div class="fw-semibold">${s.name}</div>
                                    <div class="qf-matricule">${s.matricule}</div>
                                </div>
                            </div>`).join('');
                        qfDropdown.style.display = 'block';
                    });
            }, 200);
        });

        document.addEventListener('click', e => {
            if (!qfInput?.contains(e.target)) qfDropdown.style.display='none';
        });

        window.navigateTo = function(studentId) {
            const term = document.getElementById('sel-term').value;
            const year = "{{ $academicYear }}";
            window.location.href = `/teacher/bulletin/{{ $classe->id }}/student/${studentId}?term=${term}&academic_year=${year}`;
        };

        // ── Sélecteur trimestre ───────────────────────────────────
        document.getElementById('sel-term')?.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('term', this.value);
            window.location.href = url.toString();
        });

        // ── Sauvegarde AJAX ───────────────────────────────────────
        const debounce = {};
        function saveScore(inp) {
            const scoreRaw = inp.value.trim();
            const score    = scoreRaw === '' ? null : parseFloat(scoreRaw);

            if (score !== null && (score < 0 || score > 20)) {
                inp.classList.add('error');
                return;
            }
            inp.classList.remove('saved','error');
            inp.classList.add('saving');

            return fetch(SAVE_URL, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                body: JSON.stringify({
                    student_id:               parseInt(inp.dataset.student),
                    class_subject_teacher_id: parseInt(inp.dataset.cst),
                    term:                     parseInt(inp.dataset.term),
                    sequence:                 parseInt(inp.dataset.seq),
                    academic_year:            inp.dataset.year,
                    score:                    score,
                })
            })
            .then(r => r.json())
            .then(json => {
                if (!json.success) throw new Error(json.error || 'Erreur');

                inp.classList.remove('saving');
                inp.classList.add('saved');

                const d = json.data;
                const cstId = inp.dataset.cst;

                // Mettre à jour la moyenne de la matière
                const matAvg = document.getElementById(`mat-avg-${cstId}`);
                if (matAvg) {
                    matAvg.textContent = d.subject_average !== null ? parseFloat(d.subject_average).toFixed(2) : '—';
                    matAvg.className = `mat-avg ${d.subject_average >= 10 ? 'passing' : 'failing'}`;
                }

                // Mettre à jour l'appréciation
                const appr = document.getElementById(`appr-${cstId}`);
                if (appr && d.appreciation) {
                    appr.textContent = `${d.appreciation.emoji || ''} ${d.appreciation.text}`;
                    appr.className   = `appreciation-card ${d.appreciation.class}`;
                }

                // Mettre à jour la moyenne générale
                if (d.term_average !== undefined) {
                    const ta = document.getElementById('live-term-avg');
                    if (ta) ta.textContent = parseFloat(d.term_average).toFixed(2);
                }

                // Rang
                if (d.rank_display) {
                    ['live-rank','live-rank-bottom'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = d.rank_display;
                    });
                }

                // Mention
                if (d.appreciation) {
                    const mention = document.getElementById('live-mention');
                    if (mention) mention.textContent = d.appreciation.text;
                }

                // Alerte note critique
                if (d.alert_sent) {
                    showToast('⚠️ Note critique — Alerte envoyée au Prof Principal et aux parents.', 'warning');
                }
            })
            .catch(err => {
                inp.classList.remove('saving');
                inp.classList.add('error');
                showToast('Erreur sauvegarde : ' + err.message, 'danger');
            });
        }

        document.querySelectorAll('.live-score').forEach(inp => {
            inp.addEventListener('input', () => {
                clearTimeout(debounce[inp.dataset.cst + inp.dataset.seq]);
                debounce[inp.dataset.cst + inp.dataset.seq] = setTimeout(() => saveScore(inp), 700);
            });
            inp.addEventListener('blur', () => {
                clearTimeout(debounce[inp.dataset.cst + inp.dataset.seq]);
                saveScore(inp);
            });

            // Touche Entrée → matière suivante
            inp.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const all = [...document.querySelectorAll('.live-score:not([disabled])')];
                    const idx = all.indexOf(inp);
                    if (idx < all.length - 1) all[idx+1].focus();
                }
            });
        });

        // ── Toast ─────────────────────────────────────────────────
        function showToast(msg, type) {
            const el = document.createElement('div');
            el.className = `toast align-items-center text-bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
            el.style.zIndex = '9999';
            el.innerHTML = `<div class="d-flex"><div class="toast-body fw-semibold">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
            document.body.appendChild(el);
            new bootstrap.Toast(el, {delay:4000}).show();
            el.addEventListener('hidden.bs.toast', () => el.remove());
        }
    })();
    </script>
    @endpush

@section('content')

{{-- Page Header --}}
<div class="page-header mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#4F46E5,#7c3aed)">
                <i data-lucide="scroll"></i>
            </div>
            <div>
                <h1 class="page-title">Bulletin</h1>
                <p class="page-subtitle text-muted">{{ $student->user->name ?? 'Étudiant' }} — {{ $classe->name ?? 'Classe' }}</p>
            </div>
        </div>
    </div>
</div>
