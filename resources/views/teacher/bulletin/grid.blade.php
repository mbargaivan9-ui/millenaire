{{--
    |--------------------------------------------------------------------------
    | teacher/bulletin/grid.blade.php — Grille de Saisie des Notes (Enrichie)
    |--------------------------------------------------------------------------
    | Phase 4 — Section 5.1 — Interface de Saisie des Notes
    | Fonctionnalités: Quick-Find, Calque Matière, Auto-Save AJAX 800ms,
    |                  Navigation Élève Suivant, Import CSV/Excel
    --}}

@extends('layouts.app')

@section('title', ($isFr ?? true ? 'Grille de Saisie' : 'Grade Grid') . ' — ' . ($class->name ?? ''))

@push('styles')
<style>
/* ─── Quick-Find Bar ──────────────────────────────────────────────────────── */
.quick-find-bar {
    display: flex; align-items: center; gap: .75rem;
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    padding: .5rem 1rem;
    margin-bottom: 1rem;
    transition: border-color .2s ease;
}
.quick-find-bar:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
.quick-find-bar input { border: none; outline: none; flex: 1; font-size: .9rem; background: transparent; color: var(--text-primary); }
.quick-find-bar .result-count { font-size: .78rem; color: var(--text-muted); white-space: nowrap; }

/* ─── Grade Table ─────────────────────────────────────────────────────────── */
.grade-table-wrapper { overflow-x: auto; max-height: calc(100vh - 300px); overflow-y: auto; }
.grade-table { min-width: 900px; border-collapse: separate; border-spacing: 0; width: 100%; }
.grade-table th {
    background: var(--surface-2); padding: .6rem .75rem;
    font-size: .75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .4px; color: var(--text-secondary);
    position: sticky; top: 0; z-index: 5; white-space: nowrap;
    border-bottom: 2px solid var(--border);
}
.grade-table td {
    padding: .45rem .6rem; border-bottom: 1px solid var(--border-light);
    vertical-align: middle;
}
.grade-table tr:hover td { background: var(--primary-bg); }
.grade-table tr.hidden-student { display: none; }
.grade-table tr.highlighted td { background: #fffbeb !important; }

/* Sticky first column */
.grade-table th:first-child,
.grade-table td:first-child {
    position: sticky; left: 0; z-index: 4;
    background: var(--surface); min-width: 200px;
    box-shadow: 2px 0 4px rgba(0,0,0,.05);
}
.grade-table th:first-child { z-index: 6; background: var(--surface-2); }

/* Student cell */
.student-cell { display: flex; align-items: center; gap: .6rem; }
.student-avatar {
    width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff; font-size: .75rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.student-name { font-weight: 600; font-size: .83rem; color: var(--text-primary); line-height: 1.2; }
.student-mat  { font-size: .71rem; color: var(--text-muted); }

/* ─── Grade Input ─────────────────────────────────────────────────────────── */
.grade-input {
    width: 62px; text-align: center;
    padding: .28rem .35rem; border-radius: 6px;
    font-size: .88rem; font-weight: 600;
    border: 1.5px solid transparent;
    background: transparent;
    transition: all .15s ease;
}
.grade-input:focus {
    border-color: var(--primary); background: var(--surface);
    box-shadow: 0 0 0 3px rgba(13,148,136,.12); outline: none;
}
.grade-input::placeholder { color: var(--text-muted); font-weight: 400; }
/* Color by score */
.grade-input.g-low    { color: #ef4444; }
.grade-input.g-mid    { color: #f59e0b; }
.grade-input.g-good   { color: #3b82f6; }
.grade-input.g-high   { color: #10b981; }
.grade-input.g-excel  { color: #8b5cf6; }
/* Disabled (other subject columns) */
.grade-input.col-disabled {
    opacity: .3; pointer-events: none; cursor: not-allowed;
    background: var(--surface-2);
}
/* Active column (my subject) */
.grade-input.col-active { background: rgba(13,148,136,.04); border-color: rgba(13,148,136,.15); }

/* ─── Subject Column Header States ───────────────────────────────────────── */
th.col-active-header  { background: rgba(13,148,136,.12) !important; color: var(--primary) !important; }
th.col-other-header   { opacity: .55; }

/* ─── Save Status Indicator ──────────────────────────────────────────────── */
.save-dot {
    display: inline-block; width: 8px; height: 8px;
    border-radius: 50%; margin-left: 3px;
    vertical-align: middle; flex-shrink: 0;
}
.save-dot.saving { background: #f59e0b; animation: pulse .8s infinite; }
.save-dot.saved  { background: #10b981; }
.save-dot.error  { background: #ef4444; }
@keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.3; } }

/* Global save indicator */
.global-save-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .3rem .75rem; border-radius: 20px;
    font-size: .78rem; font-weight: 600;
    transition: all .2s ease;
}
.global-save-pill.saving { background: #fffbeb; color: #d97706; }
.global-save-pill.saved  { background: #ecfdf5; color: #059669; }
.global-save-pill.error  { background: #fef2f2; color: #dc2626; }

/* ─── Moyenne / Rang / Appreciation cells ────────────────────────────────── */
td.cell-moyenne { font-weight: 800; text-align: center; color: var(--primary); background: rgba(13,148,136,.05); }
td.cell-rang    { font-weight: 700; text-align: center; color: var(--primary-dark); }
td.cell-appr    { min-width: 120px; }
.appr-badge {
    display: inline-block; padding: .18rem .55rem;
    border-radius: 12px; font-size: .72rem; font-weight: 700;
}

/* ─── Import Zone ─────────────────────────────────────────────────────────── */
.import-drop-zone {
    border: 2px dashed var(--border); border-radius: var(--radius-md);
    padding: 2.5rem 1rem; text-align: center;
    transition: all .2s ease; cursor: pointer;
}
.import-drop-zone.drag-over { border-color: var(--primary); background: var(--primary-bg); }
.import-drop-zone:hover { border-color: var(--primary-light); }

/* ─── Completion bar (Prof Principal) ────────────────────────────────────── */
.completion-row { display: flex; align-items: center; gap: .75rem; padding: .4rem 0; }
.completion-subject { flex: 0 0 140px; font-size: .82rem; font-weight: 600; color: var(--text-primary); }
.completion-bar-wrap { flex: 1; background: var(--surface-2); border-radius: 6px; height: 8px; overflow: hidden; }
.completion-bar-fill { height: 100%; border-radius: 6px; transition: width .4s ease; }
.completion-pct { font-size: .75rem; color: var(--text-muted); flex: 0 0 38px; text-align: right; }

/* Mode badges */
.mode-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .28rem .7rem; border-radius: 20px;
    font-size: .76rem; font-weight: 600;
}
</style>
@endpush

@section('content')

@php
    $isFr            = app()->getLocale() === 'fr';
    $mySubjectId     = $teacherSubjectId ?? null;
    $mySubjectName   = $mySubjectName ?? null;
    $isPrincipal     = $isProfPrincipal ?? false;
    $term            = $term ?? 1;
    $sequence        = $sequence ?? 1;
    $classId         = $class->id;

    // Appreciation helper
    $getApprColor = function(float $avg): string {
        if ($avg < 10) return '#ef4444';
        if ($avg < 13) return '#f59e0b';
        if ($avg < 16) return '#3b82f6';
        if ($avg < 19) return '#10b981';
        return '#8b5cf6';
    };
    $getApprLabel = function(float $avg) use ($isFr): string {
        if ($avg < 10) return $isFr ? 'Insuffisant' : 'Insufficient';
        if ($avg < 13) return $isFr ? 'Assez Bien' : 'Fair';
        if ($avg < 16) return $isFr ? 'Bien' : 'Good';
        if ($avg < 19) return $isFr ? 'Très Bien' : 'Very Good';
        return $isFr ? 'Excellent' : 'Excellent';
    };
@endphp

{{-- ─── Page Header ─────────────────────────────────────────────────────────── --}}
<div class="page-header mb-3">
    <div class="d-flex align-items-center flex-wrap gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,var(--primary),var(--primary-light))">
            <i data-lucide="table-2"></i>
        </div>
        <div>
            <h1 class="page-title">
                {{ $isFr ? 'Saisie des Notes' : 'Grade Entry' }}
                <span class="fw-normal text-muted" style="font-size:1rem">— {{ $class->name }}</span>
            </h1>
            <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                <span class="mode-badge" style="background:var(--primary-bg);color:var(--primary)">
                    <i data-lucide="table-2" style="width:12px"></i>
                    {{ $isFr ? 'Mode Grille' : 'Grid Mode' }}
                </span>
                @if($mySubjectId)
                <span class="mode-badge" style="background:#eff6ff;color:#2563eb">
                    <i data-lucide="book-open" style="width:12px"></i>
                    {{ $mySubjectName }}
                </span>
                @endif
                @if($isPrincipal)
                <span class="mode-badge" style="background:#fdf4ff;color:#9333ea">
                    <i data-lucide="star" style="width:12px"></i>
                    {{ $isFr ? 'Prof. Principal' : 'Head Teacher' }}
                </span>
                @endif
                <span class="text-muted" style="font-size:.8rem">
                    T{{ $term }} — Seq.{{ $sequence }}
                </span>
            </div>
        </div>

        <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
            {{-- Global save indicator --}}
            <span id="global-save-pill" class="global-save-pill" style="display:none">
                <span id="gsp-icon">⏳</span>
                <span id="gsp-text">{{ $isFr ? 'Sauvegarde...' : 'Saving...' }}</span>
            </span>

            {{-- Mode individuel --}}
            @if($students->isNotEmpty())
            <a href="{{ route('teacher.bulletin.show', ['class_id' => $classId, 'student_id' => $students->first()->id]) }}"
               class="btn btn-light btn-sm">
                <i data-lucide="user" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Individuel' : 'Individual' }}
            </a>
            @endif

            {{-- Import CSV --}}
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i data-lucide="upload" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Importer' : 'Import' }}
            </button>

            {{-- Export (Prof Principal only) --}}
            @if($isPrincipal)
            <a href="{{ route('teacher.bulletin.export-pdf', $classId) }}" class="btn btn-primary btn-sm" target="_blank">
                <i data-lucide="file-down" style="width:14px" class="me-1"></i>
                Export PDF
            </a>
            @endif
        </div>
    </div>
</div>

{{-- ─── Completion Progress (Prof Principal) ────────────────────────────────── --}}
@if($isPrincipal && isset($completion))
<div class="card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="card-title mb-0">
            <i data-lucide="bar-chart-2" style="width:16px" class="me-2"></i>
            {{ $isFr ? 'Avancement de la Saisie par Matière' : 'Grade Entry Progress by Subject' }}
        </h6>
        <button class="btn btn-warning btn-sm" id="relance-btn" onclick="sendRelance()">
            <i data-lucide="bell" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Relancer les retardataires' : 'Remind late teachers' }}
        </button>
    </div>
    <div class="card-body">
        @foreach($completion as $subjectId => $data)
        <div class="completion-row">
            <div class="completion-subject text-truncate">{{ $data['name'] }}</div>
            <div class="completion-bar-wrap">
                <div class="completion-bar-fill"
                     style="width:{{ $data['pct'] }}%; background: {{ $data['pct'] >= 100 ? '#10b981' : ($data['pct'] >= 50 ? '#f59e0b' : '#ef4444') }}">
                </div>
            </div>
            <div class="completion-pct">{{ $data['pct'] }}%</div>
            <span style="font-size:.75rem;color:var(--text-muted)">{{ $data['filled'] }}/{{ $data['total'] }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ─── Quick-Find Bar ──────────────────────────────────────────────────────── --}}
<div class="quick-find-bar" id="quick-find-bar">
    <i data-lucide="search" style="width:18px;color:var(--text-muted);flex-shrink:0"></i>
    <input type="text" id="qf-input"
           placeholder="{{ $isFr ? 'Rechercher élève — nom, prénom, matricule (ex: BI, Ndong, 2024001)' : 'Search student — name, surname, ID...' }}"
           autocomplete="off"
           oninput="filterStudents(this.value)">
    <span id="qf-count" class="result-count">{{ $students->count() }} {{ $isFr ? 'élèves' : 'students' }}</span>
    <button class="btn btn-sm btn-light px-2" onclick="document.getElementById('qf-input').value=''; filterStudents('')"
            title="{{ $isFr ? 'Effacer' : 'Clear' }}">
        <i data-lucide="x" style="width:14px"></i>
    </button>
</div>

{{-- ─── Grade Grid Table ────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-body p-0">
        <div class="grade-table-wrapper">
            <table class="grade-table" id="grade-table">
                <thead>
                    <tr>
                        <th>{{ $isFr ? 'Élève' : 'Student' }}</th>
                        @foreach($subjects as $subject)
                        <th class="{{ $mySubjectId == $subject->id ? 'col-active-header' : ($mySubjectId ? 'col-other-header' : '') }}"
                            data-subject-id="{{ $subject->id }}" style="text-align:center;min-width:80px">
                            {{ Str::limit($subject->name, 10) }}
                            @if(($subject->coefficient ?? null) > 1)
                                <br><small style="font-weight:400;font-size:.65rem">coef.{{ $subject->coefficient }}</small>
                            @endif
                        </th>
                        @endforeach
                        <th style="text-align:center;background:rgba(13,148,136,.08);color:var(--primary)">
                            {{ $isFr ? 'Moy.' : 'Avg.' }}
                        </th>
                        <th style="text-align:center">{{ $isFr ? 'Rang' : 'Rank' }}</th>
                        <th style="text-align:center;min-width:130px">{{ $isFr ? 'Appréciation' : 'Grade' }}</th>
                    </tr>
                </thead>
                <tbody id="grade-tbody">
                    @foreach($students as $student)
                    @php
                        $moyenne   = $bulletinData[$student->id]['moyenne'] ?? null;
                        $rang      = $bulletinData[$student->id]['rang'] ?? null;
                        $apprLabel = $moyenne !== null ? $getApprLabel((float)$moyenne) : '';
                        $apprColor = $moyenne !== null ? $getApprColor((float)$moyenne) : '#94a3b8';
                    @endphp
                    <tr class="student-row"
                        data-sid="{{ $student->id }}"
                        data-search="{{ strtolower(($student->user->name ?? '') . ' ' . ($student->user->first_name ?? $student->first_name ?? '') . ' ' . ($student->matricule ?? '')) }}">

                        {{-- Student Name Cell (sticky) --}}
                        <td>
                            <div class="student-cell">
                                @if($student->user->avatar_url ?? null)
                                    <img src="{{ asset($student->user->avatar_url) }}"
                                         class="student-avatar" style="object-fit:cover" alt="">
                                @else
                                    <div class="student-avatar">
                                        {{ strtoupper(substr($student->user->name ?? 'E', 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="student-name">{{ $student->user->display_name ?? $student->user->name }}</div>
                                    <div class="student-mat">{{ $student->matricule }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Grade Inputs --}}
                        @foreach($subjects as $subject)
                        @php
                            $mark      = $marks[$student->id][$subject->id] ?? null;
                            $scoreVal  = $mark ? number_format((float)$mark->score, 2, '.', '') : '';
                            $isMine    = !$mySubjectId || $subject->id == $mySubjectId;
                            $canEdit   = $isPrincipal || $isMine;
                            $colClass  = !$canEdit ? 'col-disabled' : ($isMine ? 'col-active' : '');
                            $colorClass= $scoreVal !== '' ? getGradeColorClass((float)$scoreVal) : '';
                        @endphp
                        <td style="text-align:center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <input type="number"
                                       id="g-{{ $student->id }}-{{ $subject->id }}"
                                       class="grade-input {{ $colClass }} {{ $colorClass }}"
                                       data-sid="{{ $student->id }}"
                                       data-subid="{{ $subject->id }}"
                                       data-cid="{{ $classId }}"
                                       data-term="{{ $term }}"
                                       data-seq="{{ $sequence }}"
                                       value="{{ $scoreVal }}"
                                       min="0" max="20" step="0.25"
                                       placeholder="—"
                                       {{ !$canEdit ? 'readonly tabindex="-1"' : '' }}
                                       oninput="onGradeInput(this)"
                                       onkeydown="onGradeKeydown(event, this)">
                                <span class="save-dot"
                                      id="dot-{{ $student->id }}-{{ $subject->id }}"
                                      style="display:none"></span>
                            </div>
                        </td>
                        @endforeach

                        {{-- Moyenne --}}
                        <td class="cell-moyenne" id="moy-{{ $student->id }}">
                            {{ $moyenne !== null ? number_format((float)$moyenne, 2) : '—' }}
                        </td>

                        {{-- Rang --}}
                        <td class="cell-rang" id="rg-{{ $student->id }}">
                            {{ $rang ?? '—' }}
                        </td>

                        {{-- Appréciation --}}
                        <td class="cell-appr" id="appr-{{ $student->id }}">
                            @if($apprLabel)
                            <span class="appr-badge"
                                  style="background:{{ $apprColor }}22;color:{{ $apprColor }}">
                                {{ $apprLabel }}
                            </span>
                            @else
                            <span class="text-muted" style="font-size:.78rem">—</span>
                            @endif
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Table Footer --}}
    <div class="card-footer d-flex align-items-center justify-content-between" style="font-size:.8rem;color:var(--text-muted)">
        <span>
            <i data-lucide="info" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Les notes sont sauvegardées automatiquement — aucun bouton "Enregistrer" nécessaire' : 'Grades are saved automatically — no "Save" button needed' }}
        </span>
        <span>
            {{ $students->count() }} {{ $isFr ? 'élèves' : 'students' }} ·
            {{ $subjects->count() }} {{ $isFr ? 'matières' : 'subjects' }}
        </span>
    </div>
</div>

{{-- ─── Navigate Between Students (Prof Principal / Individual Mode) ────────── --}}
@if($isPrincipal)
<div class="d-flex justify-content-between align-items-center mt-3">
    <button class="btn btn-light" id="prev-btn" disabled onclick="navigateStudent(-1)">
        <i data-lucide="chevron-left" style="width:16px" class="me-1"></i>
        {{ $isFr ? 'Élève Précédent' : 'Previous Student' }}
    </button>
    <span class="text-muted" style="font-size:.83rem" id="nav-info">
        {{ $isFr ? 'Navigation rapide entre les élèves' : 'Quick navigation between students' }}
    </span>
    <button class="btn btn-primary" id="next-btn" onclick="navigateStudent(1)">
        {{ $isFr ? 'Élève Suivant' : 'Next Student' }}
        <i data-lucide="chevron-right" style="width:16px" class="ms-1"></i>
    </button>
</div>
@endif

{{-- ─── Import CSV/Excel Modal ─────────────────────────────────────────────── --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i data-lucide="upload" style="width:18px" class="me-2"></i>
                    {{ $isFr ? 'Importer les Notes — CSV / Excel' : 'Import Grades — CSV / Excel' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info d-flex gap-2">
                    <i data-lucide="info" style="width:18px;flex-shrink:0;margin-top:2px"></i>
                    <div>
                        {{ $isFr
                            ? 'Le fichier CSV/Excel doit contenir 2 colonnes : "matricule" (ex: 2024001) et "note" (ex: 14.5). La première ligne est l\'en-tête.'
                            : 'CSV/Excel must have 2 columns: "matricule" (e.g. 2024001) and "score" (e.g. 14.5). First row is the header.' }}
                        <br>
                        <a href="{{ route('teacher.bulletin.import-template', $classId) }}" class="fw-semibold">
                            <i data-lucide="download" style="width:12px"></i>
                            {{ $isFr ? 'Télécharger le modèle CSV' : 'Download CSV template' }}
                        </a>
                    </div>
                </div>

                {{-- Drop zone --}}
                <div class="import-drop-zone" id="import-drop-zone"
                     onclick="document.getElementById('file-import').click()"
                     ondragover="event.preventDefault(); this.classList.add('drag-over')"
                     ondragleave="this.classList.remove('drag-over')"
                     ondrop="handleFileDrop(event)">
                    <i data-lucide="file-spreadsheet" style="width:48px;color:var(--primary);margin-bottom:.75rem"></i>
                    <p class="mb-1 fw-semibold">{{ $isFr ? 'Glissez votre fichier ici' : 'Drop your file here' }}</p>
                    <p class="text-muted mb-2" style="font-size:.83rem">CSV, XLSX, XLS — max 5MB</p>
                    <span class="btn btn-primary btn-sm">{{ $isFr ? 'Parcourir' : 'Browse' }}</span>
                    <input type="file" id="file-import" accept=".csv,.xlsx,.xls" hidden onchange="readImportFile(this.files[0])">
                </div>

                {{-- Preview --}}
                <div id="import-preview" style="display:none;margin-top:1.25rem">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0 fw-semibold">{{ $isFr ? 'Aperçu' : 'Preview' }}</h6>
                        <span id="import-summary" class="badge bg-primary"></span>
                    </div>
                    <div class="table-responsive" style="max-height:240px;overflow-y:auto;border:1px solid var(--border);border-radius:var(--radius)">
                        <table class="table table-sm mb-0" id="preview-tbl">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Matricule</th>
                                    <th>{{ $isFr ? 'Note' : 'Score' }}</th>
                                    <th>{{ $isFr ? 'Élève trouvé' : 'Student found' }}</th>
                                    <th>{{ $isFr ? 'Statut' : 'Status' }}</th>
                                </tr>
                            </thead>
                            <tbody id="preview-tbody"></tbody>
                        </table>
                    </div>
                    <div id="import-errors" class="alert alert-warning mt-2" style="display:none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    {{ $isFr ? 'Annuler' : 'Cancel' }}
                </button>
                <button type="button" id="do-import-btn" class="btn btn-primary" disabled onclick="executeImport()">
                    <i data-lucide="check" style="width:14px" class="me-1"></i>
                    {{ $isFr ? 'Importer les notes validées' : 'Import valid grades' }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/**
 * Grade Grid — JavaScript
 * Phase 4 · Section 5.1
 * Auto-save AJAX · Quick-Find · Navigation
 */
(function () {
'use strict';

// ─── Config ─────────────────────────────────────────────────────────────────
const DEBOUNCE_MS  = 800;
const SAVE_URL     = '{{ route('api.v1.teacher.grades.save') }}';
const CSRF         = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const MY_SUBJ_ID   = {{ $mySubjectId ?? 'null' }};
const IS_PRINCIPAL = {{ $isPrincipal ? 'true' : 'false' }};
const CLASS_ID     = {{ $classId }};

// ─── State ───────────────────────────────────────────────────────────────────
const timers      = {};   // debounce timers
const pendingRows = new Set();
let currentNavIdx = -1;   // for principal nav
const rows = [...document.querySelectorAll('tr.student-row')];

// ─── Grade color helper ───────────────────────────────────────────────────────
function gradeClass(v) {
    if (v < 10) return 'g-low';
    if (v < 13) return 'g-mid';
    if (v < 16) return 'g-good';
    if (v < 19) return 'g-high';
    return 'g-excel';
}
window.getGradeColorClass = gradeClass; // exposed for Blade

// ─── On grade input ───────────────────────────────────────────────────────────
window.onGradeInput = function (inp) {
    const v = parseFloat(inp.value);
    inp.classList.remove('g-low','g-mid','g-good','g-high','g-excel','is-invalid');
    if (inp.value !== '' && !isNaN(v)) inp.classList.add(gradeClass(v));

    const key = `${inp.dataset.sid}-${inp.dataset.subid}`;
    const dot  = document.getElementById(`dot-${inp.dataset.sid}-${inp.dataset.subid}`);
    if (dot) { dot.style.display = 'inline-block'; dot.className = 'save-dot saving'; }
    showGlobalPill('saving');

    clearTimeout(timers[key]);
    timers[key] = setTimeout(() => saveGrade(inp), DEBOUNCE_MS);
};

// ─── Save grade via AJAX ──────────────────────────────────────────────────────
async function saveGrade(inp) {
    const score = inp.value === '' ? null : parseFloat(inp.value);
    if (score !== null && (isNaN(score) || score < 0 || score > 20)) {
        inp.classList.add('is-invalid');
        setDot(inp, 'error');
        return;
    }
    inp.classList.remove('is-invalid');

    try {
        const res = await fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' },
            body: JSON.stringify({
                student_id: inp.dataset.sid,
                subject_id: inp.dataset.subid,
                class_id:   inp.dataset.cid,
                term:       inp.dataset.term,
                sequence:   inp.dataset.seq,
                score:      score,
            }),
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message ?? 'Error');

        setDot(inp, 'saved');
        showGlobalPill('saved');

        // Update computed cells
        if (data.moyenne != null) {
            const mc = document.getElementById(`moy-${inp.dataset.sid}`);
            if (mc) mc.textContent = parseFloat(data.moyenne).toFixed(2);
        }
        if (data.rang != null) {
            const rc = document.getElementById(`rg-${inp.dataset.sid}`);
            if (rc) rc.textContent = data.rang;
        }
        if (data.appreciation) {
            const ac = document.getElementById(`appr-${inp.dataset.sid}`);
            if (ac) ac.innerHTML = `<span class="appr-badge" style="background:${data.appr_color}22;color:${data.appr_color}">${data.appreciation}</span>`;
        }

        setTimeout(() => hideDot(inp), 2500);

    } catch (err) {
        setDot(inp, 'error');
        showGlobalPill('error');
        console.error('[Grade Save]', err);
    }
}

function setDot(inp, state) {
    const dot = document.getElementById(`dot-${inp.dataset.sid}-${inp.dataset.subid}`);
    if (!dot) return;
    dot.style.display = 'inline-block';
    dot.className = `save-dot ${state}`;
}
function hideDot(inp) {
    const dot = document.getElementById(`dot-${inp.dataset.sid}-${inp.dataset.subid}`);
    if (dot) dot.style.display = 'none';
}

// ─── Global save pill ─────────────────────────────────────────────────────────
let globalPillTimer;
function showGlobalPill(state) {
    const pill = document.getElementById('global-save-pill');
    const icon = document.getElementById('gsp-icon');
    const text = document.getElementById('gsp-text');
    if (!pill) return;
    clearTimeout(globalPillTimer);
    pill.style.display = 'inline-flex';
    const map = {
        saving: { icon:'⏳', text:'{{ $isFr ? 'Sauvegarde...' : 'Saving...' }}', cls:'saving' },
        saved:  { icon:'✅', text:'{{ $isFr ? 'Sauvegardé' : 'Saved' }}',       cls:'saved'  },
        error:  { icon:'❌', text:'{{ $isFr ? 'Erreur' : 'Error' }}',           cls:'error'  },
    };
    pill.className = `global-save-pill ${map[state].cls}`;
    icon.textContent = map[state].icon;
    text.textContent = map[state].text;
    if (state !== 'saving') globalPillTimer = setTimeout(() => { pill.style.display = 'none'; }, 3500);
}

// ─── Keyboard: Tab moves to next SAME subject cell ────────────────────────────
window.onGradeKeydown = function (e, inp) {
    if (e.key === 'Enter' || e.key === 'Tab') {
        e.preventDefault();
        const allInputs = [...document.querySelectorAll(
            `.grade-input[data-subid="${inp.dataset.subid}"]:not(.col-disabled)`
        )];
        const idx = allInputs.indexOf(inp);
        const next = allInputs[idx + 1];
        if (next) {
            next.focus();
            next.select();
            // highlight row
            rows.forEach(r => r.classList.remove('highlighted'));
            next.closest('tr')?.classList.add('highlighted');
        }
    }
};

// ─── Quick-Find Filter ────────────────────────────────────────────────────────
window.filterStudents = function (q) {
    q = q.trim().toLowerCase();
    let visible = 0;
    rows.forEach(tr => {
        const s = tr.dataset.search ?? '';
        const match = !q || s.includes(q);
        tr.classList.toggle('hidden-student', !match);
        if (match) { visible++; tr.classList.toggle('highlighted', !!q); }
        else tr.classList.remove('highlighted');
    });
    const cnt = document.getElementById('qf-count');
    if (cnt) cnt.textContent = `${visible} {{ $isFr ? 'élève(s)' : 'student(s)' }}`;
};

// ─── Navigate Between Students (Prof Principal) ───────────────────────────────
window.navigateStudent = function (dir) {
    const visible = rows.filter(r => !r.classList.contains('hidden-student'));
    if (!visible.length) return;
    if (currentNavIdx < 0) currentNavIdx = 0;
    else currentNavIdx = Math.max(0, Math.min(visible.length - 1, currentNavIdx + dir));

    rows.forEach(r => r.classList.remove('highlighted'));
    const target = visible[currentNavIdx];
    if (target) {
        target.classList.add('highlighted');
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const firstInput = target.querySelector('.grade-input:not(.col-disabled)');
        firstInput?.focus();
    }
    const navInfo = document.getElementById('nav-info');
    if (navInfo) navInfo.textContent = `{{ $isFr ? 'Élève' : 'Student' }} ${currentNavIdx + 1} / ${visible.length}`;
    document.getElementById('prev-btn')?.toggleAttribute('disabled', currentNavIdx <= 0);
    document.getElementById('next-btn')?.toggleAttribute('disabled', currentNavIdx >= visible.length - 1);
};

// ─── Send relance notifications (Prof Principal) ──────────────────────────────
window.sendRelance = async function () {
    const btn = document.getElementById('relance-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ $isFr ? 'Envoi...' : 'Sending...' }}';
    try {
        const res = await fetch('{{ route('teacher.bulletin.relance', $classId) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        btn.innerHTML = data.success ? '✅ {{ $isFr ? 'Relances envoyées' : 'Reminders sent' }}' : '❌ Erreur';
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="bell" style="width:14px" class="me-1"></i>{{ $isFr ? 'Relancer les retardataires' : 'Remind late teachers' }}';
            lucide.createIcons();
        }, 3000);
    } catch {
        btn.disabled = false;
    }
};

// ─── Import CSV / Excel ───────────────────────────────────────────────────────
let importRows = [];

window.handleFileDrop = function (e) {
    e.preventDefault();
    document.getElementById('import-drop-zone').classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) readImportFile(file);
};

window.readImportFile = function (file) {
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    if (ext === 'csv') {
        const reader = new FileReader();
        reader.onload = e => parseCSV(e.target.result);
        reader.readAsText(file);
    } else {
        alert('{{ $isFr ? 'Pour les fichiers Excel (.xlsx), utilisez le bouton "Parcourir".' : 'For Excel files, use the Browse button.' }}');
    }
};

function parseCSV(content) {
    const lines = content.trim().split('\n').slice(1); // skip header
    const students = @json($students->mapWithKeys(fn($s) => [$s->matricule => ['id' => $s->id, 'name' => $s->user->name ?? $s->full_name]]));
    importRows = [];
    const errors = [];

    lines.forEach((line, i) => {
        const cols = line.split(',').map(c => c.trim().replace(/"/g, ''));
        const mat  = cols[0];
        const score = parseFloat(cols[1]);
        const found = students[mat];
        const valid = !isNaN(score) && score >= 0 && score <= 20;
        importRows.push({ matricule: mat, score, found: found ?? null, valid: valid && !!found });
        if (!found) errors.push(`Ligne ${i + 2}: Matricule "${mat}" non trouvé`);
        if (isNaN(score)) errors.push(`Ligne ${i + 2}: Note invalide "${cols[1]}"`);
    });

    // Render preview
    const tbody = document.getElementById('preview-tbody');
    tbody.innerHTML = importRows.map(r => `
        <tr class="${r.valid ? '' : 'table-warning'}">
            <td>${r.matricule}</td>
            <td>${isNaN(r.score) ? '?' : r.score}</td>
            <td>${r.found ? r.found.name : '<em class="text-danger">Non trouvé</em>'}</td>
            <td>${r.valid ? '✅ OK' : '⚠️ Ignoré'}</td>
        </tr>`).join('');

    const summary = document.getElementById('import-summary');
    const valid = importRows.filter(r => r.valid).length;
    summary.textContent = `${valid} / ${importRows.length} ${valid === 1 ? 'ligne valide' : 'lignes valides'}`;
    summary.className = `badge ${valid > 0 ? 'bg-success' : 'bg-danger'}`;

    const errEl = document.getElementById('import-errors');
    if (errors.length) {
        errEl.style.display = '';
        errEl.innerHTML = errors.slice(0, 5).join('<br>') + (errors.length > 5 ? `<br>...et ${errors.length - 5} autres` : '');
    } else errEl.style.display = 'none';

    document.getElementById('import-preview').style.display = '';
    document.getElementById('do-import-btn').disabled = valid === 0;
}

window.executeImport = async function () {
    const btn = document.getElementById('do-import-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Import...';

    const payload = importRows.filter(r => r.valid).map(r => ({
        student_id: r.found.id,
        subject_id: MY_SUBJ_ID ?? {{ $subjects->first()?->id ?? 0 }},
        class_id: CLASS_ID,
        term: {{ $term }},
        sequence: {{ $sequence }},
        score: r.score,
    }));

    try {
        const res = await fetch('{{ route('api.v1.teacher.grades.batch-save') }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' },
            body: JSON.stringify({ grades: payload }),
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('importModal'))?.hide();
            location.reload();
        } else {
            alert(data.message ?? '{{ $isFr ? 'Erreur lors de l\'import.' : 'Import error.' }}');
            btn.disabled = false;
            btn.textContent = '{{ $isFr ? 'Réessayer' : 'Retry' }}';
        }
    } catch (err) {
        console.error(err);
        btn.disabled = false;
        btn.textContent = 'Erreur';
    }
};

// ─── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
    // Focus first editable input on load
    const first = document.querySelector('.grade-input.col-active:not(.col-disabled), .grade-input:not(.col-disabled)');
    first?.focus();
});

})();
</script>
@endpush
