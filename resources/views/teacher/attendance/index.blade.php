{{--
    |--------------------------------------------------------------------------
    | teacher/attendance/index.blade.php — Interface d'Appel
    |--------------------------------------------------------------------------
    | Phase 3 — Appel en classe avec AJAX batch-save
    | Présent / Absent / Retard / Excusé — notification parent temps-réel
    --}}

@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Appel / Présences' : 'Roll Call / Attendance');
@endphp

@section('title', $pageTitle)

@push('styles')
<style>
/* ─── Status toggle buttons ──────────────────────────────────────────────── */
.status-toggle { display: inline-flex; border-radius: 8px; overflow: hidden; border: 1.5px solid var(--border); }
.status-btn {
    padding: .3rem .7rem; font-size: .75rem; font-weight: 700;
    cursor: pointer; border: none; background: var(--surface-2);
    color: var(--text-secondary); transition: all .15s ease;
    white-space: nowrap;
}
.status-btn:hover { background: var(--border); }
.status-btn.active-present  { background: #ecfdf5; color: #059669; }
.status-btn.active-absent   { background: #fef2f2; color: #dc2626; }
.status-btn.active-late     { background: #fffbeb; color: #d97706; }
.status-btn.active-excused  { background: #eff6ff; color: #2563eb; }

/* ─── Student row ────────────────────────────────────────────────────────── */
.student-row {
    display: flex; align-items: center; gap: 1rem;
    padding: .75rem 1rem; border-bottom: 1px solid var(--border-light);
    transition: background .1s ease;
}
.student-row:hover { background: var(--surface-2); }
.student-row:last-child { border-bottom: none; }
.student-num { width: 28px; text-align: center; font-size: .75rem; font-weight: 700; color: var(--text-muted); flex-shrink: 0; }
.student-avatar {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff; font-weight: 700; font-size: .8rem;
    display: flex; align-items: center; justify-content: center;
}
.student-name { flex: 1; font-weight: 600; font-size: .88rem; min-width: 0; }
.student-matric { font-size: .72rem; color: var(--text-muted); }

/* ─── Stats strip ────────────────────────────────────────────────────────── */
.stat-strip { display: flex; gap: 1rem; padding: 1rem; background: var(--surface-2); border-bottom: 1px solid var(--border); flex-wrap: wrap; }
.stat-strip-item { display: flex; align-items: center; gap: .5rem; font-size: .82rem; font-weight: 700; }

/* ─── Save indicator ─────────────────────────────────────────────────────── */
.save-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .3rem .85rem; border-radius: 20px; font-size: .78rem; font-weight: 700;
    transition: all .3s ease;
}
.save-pill.idle    { background: var(--surface-2); color: var(--text-muted); }
.save-pill.saving  { background: #fffbeb; color: #d97706; }
.save-pill.saved   { background: #ecfdf5; color: #059669; }
.save-pill.error   { background: #fef2f2; color: #dc2626; }
</style>
@endpush

@section('content')
@php
    $isFr = app()->getLocale() === 'fr';
    $statusLabels = [
        'present' => $isFr ? 'Présent' : 'Present',
        'absent'  => $isFr ? 'Absent'  : 'Absent',
        'late'    => $isFr ? 'Retard'  : 'Late',
        'excused' => $isFr ? 'Excusé'  : 'Excused',
    ];
    $statusColors = ['present' => '#059669', 'absent' => '#dc2626', 'late' => '#d97706', 'excused' => '#2563eb'];
@endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
                <i data-lucide="calendar-check"></i>
            </div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Appel / Présences' : 'Roll Call / Attendance' }}</h1>
                <p class="page-subtitle text-muted">{{ now()->locale($isFr ? 'fr' : 'en')->isoFormat('dddd D MMMM YYYY') }}</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="save-pill idle" id="save-pill">
                <i data-lucide="circle" style="width:10px"></i>
                {{ $isFr ? 'Non sauvegardé' : 'Unsaved' }}
            </span>
            <button class="btn btn-primary" id="btn-save-all" onclick="saveAttendance()" disabled>
                <i data-lucide="save" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Enregistrer l\'appel' : 'Save roll call' }}
            </button>
        </div>
    </div>
</div>

{{-- ─── Filters ─────────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
            <div style="min-width:160px">
                <label class="form-label">{{ $isFr ? 'Classe' : 'Class' }}</label>
                <select name="class_id" class="form-select" onchange="this.form.submit()">
                    @foreach($myClasses as $c)
                    <option value="{{ $c->id }}" {{ ($class?->id == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:150px">
                <label class="form-label">{{ $isFr ? 'Date' : 'Date' }}</label>
                <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()" max="{{ today()->toDateString() }}">
            </div>
            <div class="ms-auto d-flex gap-2">
                <button type="button" class="btn btn-light btn-sm" onclick="setAll('present')">
                    ✅ {{ $isFr ? 'Tous présents' : 'All present' }}
                </button>
                <button type="button" class="btn btn-light btn-sm" onclick="setAll('absent')">
                    ❌ {{ $isFr ? 'Tous absents' : 'All absent' }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Stats strip ──────────────────────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="stat-strip">
        <div class="stat-strip-item">
            <span style="width:10px;height:10px;border-radius:50%;background:#059669;flex-shrink:0"></span>
            <span id="count-present">0</span> {{ $isFr ? 'présents' : 'present' }}
        </div>
        <div class="stat-strip-item">
            <span style="width:10px;height:10px;border-radius:50%;background:#dc2626;flex-shrink:0"></span>
            <span id="count-absent">0</span> {{ $isFr ? 'absents' : 'absent' }}
        </div>
        <div class="stat-strip-item">
            <span style="width:10px;height:10px;border-radius:50%;background:#d97706;flex-shrink:0"></span>
            <span id="count-late">0</span> {{ $isFr ? 'retards' : 'late' }}
        </div>
        <div class="stat-strip-item">
            <span style="width:10px;height:10px;border-radius:50%;background:#2563eb;flex-shrink:0"></span>
            <span id="count-excused">0</span> {{ $isFr ? 'excusés' : 'excused' }}
        </div>
        <div class="stat-strip-item ms-auto" style="color:var(--text-muted);font-weight:600">
            <span id="count-unmarked">{{ $students->count() }}</span> {{ $isFr ? 'non marqués' : 'unmarked' }}
        </div>
    </div>

    {{-- Students list --}}
    <div id="students-list">
        @forelse($students as $i => $student)
        @php $existing = $todayAbsences[$student->id] ?? null; @endphp
        <div class="student-row" data-student="{{ $student->id }}">
            <div class="student-num">{{ $i + 1 }}</div>
            <div class="student-avatar">{{ strtoupper(substr($student->user->name ?? 'E', 0, 1)) }}</div>
            <div class="flex-grow-1 min-w-0">
                <div class="student-name text-truncate">{{ $student->user->display_name ?? $student->user->name }}</div>
                <div class="student-matric">{{ $student->matricule }}</div>
            </div>
            <div class="status-toggle">
                @foreach(['present','absent','late','excused'] as $status)
                <button type="button"
                        class="status-btn {{ $existing === $status ? 'active-'.$status : '' }}"
                        data-status="{{ $status }}"
                        data-student="{{ $student->id }}"
                        onclick="setStatus({{ $student->id }}, '{{ $status }}', this)">
                    @if($status === 'present') {{ $isFr ? 'P' : 'P' }}
                    @elseif($status === 'absent') {{ $isFr ? 'A' : 'A' }}
                    @elseif($status === 'late') {{ $isFr ? 'R' : 'L' }}
                    @else {{ $isFr ? 'E' : 'E' }}
                    @endif
                </button>
                @endforeach
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i data-lucide="users" style="width:32px;opacity:.3;display:block;margin:0 auto .75rem"></i>
            {{ $isFr ? 'Aucun élève dans cette classe.' : 'No students in this class.' }}
        </div>
        @endforelse
    </div>
</div>

{{-- Already saved notice --}}
@if($todayAbsences->isNotEmpty())
<div class="alert alert-info">
    <i data-lucide="info" style="width:16px" class="me-2"></i>
    {{ $isFr ? 'Un appel a déjà été effectué pour cette date. Vous pouvez le modifier.' : 'A roll call was already taken for this date. You can modify it.' }}
</div>
@endif

@endsection

@push('scripts')
<script>
(function () {
'use strict';

const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const CLASS_ID = {{ $class?->id ?? 'null' }};
const DATE     = '{{ $date }}';
const isFr     = {{ $isFr ? 'true' : 'false' }};
let statuses   = {};
let dirty      = false;

// Pre-populate existing absences
@foreach($todayAbsences as $studentId => $status)
statuses[{{ $studentId }}] = '{{ $status }}';
@endforeach

// ─── Set status for one student ───────────────────────────────────────────────
window.setStatus = function(studentId, status, btn) {
    // Clear sibling active classes
    btn.closest('.status-toggle').querySelectorAll('.status-btn').forEach(b => {
        b.className = b.className.replace(/active-\w+/g, '').trim();
    });
    btn.classList.add(`active-${status}`);
    statuses[studentId] = status;
    dirty = true;
    document.getElementById('btn-save-all').disabled = false;
    updateCounts();
    setSavePill('idle');
};

// ─── Set all students to one status ──────────────────────────────────────────
window.setAll = function(status) {
    document.querySelectorAll('.student-row').forEach(row => {
        const sid = parseInt(row.dataset.student);
        const btn = row.querySelector(`[data-status="${status}"]`);
        if (btn) {
            row.querySelectorAll('.status-btn').forEach(b => b.className = b.className.replace(/active-\w+/g, '').trim());
            btn.classList.add(`active-${status}`);
            statuses[sid] = status;
        }
    });
    dirty = true;
    document.getElementById('btn-save-all').disabled = false;
    updateCounts();
    setSavePill('idle');
};

// ─── Update counters ──────────────────────────────────────────────────────────
function updateCounts() {
    const counts = { present: 0, absent: 0, late: 0, excused: 0 };
    Object.values(statuses).forEach(s => { if (counts[s] !== undefined) counts[s]++; });
    const total    = document.querySelectorAll('.student-row').length;
    const marked   = Object.keys(statuses).length;
    document.getElementById('count-present').textContent  = counts.present;
    document.getElementById('count-absent').textContent   = counts.absent;
    document.getElementById('count-late').textContent     = counts.late;
    document.getElementById('count-excused').textContent  = counts.excused;
    document.getElementById('count-unmarked').textContent = total - marked;
}

// ─── Save pill ────────────────────────────────────────────────────────────────
function setSavePill(state) {
    const pill = document.getElementById('save-pill');
    const labels = {
        idle:   isFr ? 'Modifications en attente' : 'Unsaved changes',
        saving: isFr ? 'Enregistrement...'        : 'Saving...',
        saved:  isFr ? 'Appel enregistré ✓'      : 'Roll call saved ✓',
        error:  isFr ? 'Erreur d\'enregistrement' : 'Save error',
    };
    pill.className = `save-pill ${state}`;
    pill.innerHTML = `<i data-lucide="circle" style="width:10px"></i> ${labels[state]}`;
    if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [pill] });
}

// ─── Save to server ───────────────────────────────────────────────────────────
window.saveAttendance = async function() {
    if (!CLASS_ID || Object.keys(statuses).length === 0) return;

    const btn = document.getElementById('btn-save-all');
    btn.disabled = true;
    setSavePill('saving');

    try {
        const res  = await fetch('/teacher/attendance', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ class_id: CLASS_ID, date: DATE, statuses }),
        });
        const data = await res.json();

        if (data.success) {
            setSavePill('saved');
            dirty = false;
        } else {
            setSavePill('error');
            btn.disabled = false;
        }
    } catch {
        setSavePill('error');
        btn.disabled = false;
    }
};

// Warn before leaving with unsaved changes
window.addEventListener('beforeunload', e => {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
});

updateCounts();
})();
</script>
@endpush
