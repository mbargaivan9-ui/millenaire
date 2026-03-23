@extends('layouts.app')

@section('title', 'Bulletin NG — Saisie des Notes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bulletin_ng.css') }}">
@endpush

@php $currentStep = 5; $isEN = $config->langue === 'EN'; @endphp

@section('content')
<div class="bng-page bng-page-wide">

    <div class="bng-page-header">
        <div class="bng-page-header-inner">
            <div class="bng-page-icon">✏️</div>
            <div>
                <h1 class="bng-page-title">{{ $isEN ? 'Step 5 — Grade Entry' : 'Étape 5 — Saisie des Notes' }}</h1>
                <p class="bng-page-subtitle">{{ $config->nom_classe }} | {{ $config->trimestre_label }}</p>
            </div>
        </div>
        @if(!$config->notes_verrouillee)
            <button class="bng-btn bng-btn-danger" id="lockNotesBtn">
                🔒 {{ $isEN ? 'Lock Grades' : 'Verrouiller les Notes' }}
            </button>
        @else
            <span class="bng-badge bng-badge-danger" style="font-size: 13px; padding: 8px 16px;">
                🔒 {{ $isEN ? 'Grades Locked' : 'Notes Verrouillées' }}
            </span>
        @endif
    </div>

    @include('teacher.bulletin_ng.partials.wizard_header')

    {{-- Statistiques temps réel --}}
    <div class="bng-stats-bar">
        <div class="bng-stat-item">
            <div class="bng-stat-value" id="statAvg">{{ number_format($stats['avg'], 2) }}</div>
            <div class="bng-stat-label">{{ $isEN ? 'Class Avg' : 'Moy. Classe' }}</div>
        </div>
        <div class="bng-stat-item bng-stat-success">
            <div class="bng-stat-value" id="statPct">{{ $stats['pct'] }}%</div>
            <div class="bng-stat-label">{{ $isEN ? '% Success' : '% Réussite' }}</div>
        </div>
        <div class="bng-stat-item bng-stat-primary">
            <div class="bng-stat-value" id="statMax">{{ number_format($stats['max'], 2) }}</div>
            <div class="bng-stat-label">{{ $isEN ? 'Highest' : 'Max' }}</div>
        </div>
        <div class="bng-stat-item bng-stat-warning">
            <div class="bng-stat-value" id="statMin">{{ number_format($stats['min'], 2) }}</div>
            <div class="bng-stat-label">{{ $isEN ? 'Lowest' : 'Min' }}</div>
        </div>
        <div class="bng-stat-item">
            <div class="bng-stat-value" id="statPassing">{{ $stats['passing'] }}/{{ $students->count() }}</div>
            <div class="bng-stat-label">{{ $isEN ? 'Passing' : 'Au-dessus 10' }}</div>
        </div>
    </div>

    <div class="bng-card" style="overflow: visible;">
        <div class="bng-card-body" style="padding: 0;">
            <div class="bng-table-responsive bng-table-notes-wrapper">
                <table class="bng-table bng-table-notes" id="notesTable">
                    <thead>
                    <tr>
                        <th class="bng-col-student sticky-col">{{ $isEN ? 'Student' : 'Élève' }}</th>
                        @foreach($subjects as $sub)
                            <th class="bng-col-note" title="{{ $sub->nom_prof ?? '' }}">
                                <div class="bng-subject-header">
                                    <span class="bng-subject-name">{{ $sub->nom }}</span>
                                    <span class="bng-subject-coef">Coef {{ $sub->coefficient }}</span>
                                    @if($sub->nom_prof)
                                        <span class="bng-subject-prof">{{ $sub->nom_prof }}</span>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                        <th class="bng-col-avg">{{ $isEN ? 'Avg' : 'Moy' }}</th>
                        <th class="bng-col-rank">{{ $isEN ? 'Rank' : 'Rang' }}</th>
                        <th class="bng-col-app">{{ $isEN ? 'Appreciation' : 'Appréciation' }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $si => $student)
                        @php
                            $studentAvg  = $stats['avgs'][$student->id] ?? 0;
                            $studentRank = $stats['ranks'][$student->id] ?? '-';
                        @endphp
                        <tr class="bng-note-row" data-student-id="{{ $student->id }}"
                            style="background: {{ $si % 2 === 0 ? '#fff' : '#f8fffe' }}">
                            <td class="bng-col-student sticky-col">
                                <div class="bng-student-cell">
                                    <span class="bng-student-num">{{ $si + 1 }}</span>
                                    <div>
                                        <div class="bng-student-cellname">{{ $student->nom }}</div>
                                        <div class="bng-student-cellmat">{{ $student->matricule }}</div>
                                    </div>
                                </div>
                            </td>
                            @foreach($subjects as $sub)
                                @php
                                    $noteKey = "{$student->id}_{$sub->id}";
                                    $noteVal = $notes->get($noteKey)?->note;
                                @endphp
                                <td class="bng-col-note">
                                    <input type="number"
                                           class="bng-note-input {{ is_null($noteVal) ? '' : ($noteVal < 10 ? 'note-fail' : 'note-pass') }}"
                                           data-student="{{ $student->id }}"
                                           data-subject="{{ $sub->id }}"
                                           value="{{ is_null($noteVal) ? '' : $noteVal }}"
                                           min="0" max="20" step="0.25"
                                           {{ $config->notes_verrouillee ? 'disabled' : '' }}
                                           placeholder="—">
                                </td>
                            @endforeach
                            <td class="bng-col-avg">
                                <span class="bng-avg-badge {{ $studentAvg >= 10 ? 'avg-pass' : ($studentAvg > 0 ? 'avg-fail' : '') }}"
                                      id="avg-{{ $student->id }}">
                                    {{ $studentAvg > 0 ? number_format($studentAvg, 2) : '—' }}
                                </span>
                            </td>
                            <td class="bng-col-rank" id="rank-{{ $student->id }}">
                                {{ $studentRank !== '-' ? "{$studentRank}/{$students->count()}" : '—' }}
                            </td>
                            <td class="bng-col-app" id="app-{{ $student->id }}">
                                @if($studentAvg > 0)
                                    @php
                                        $app = match(true) {
                                            $studentAvg < 10  => ($isEN ? 'Fail' : 'Échec'),
                                            $studentAvg < 12  => ($isEN ? 'Pass' : 'Passable'),
                                            $studentAvg < 15  => ($isEN ? 'Fairly Good' : 'Assez Bien'),
                                            $studentAvg < 17  => ($isEN ? 'Good' : 'Bien'),
                                            default           => 'Excellent',
                                        };
                                    @endphp
                                    <span class="bng-app-badge app-{{ $studentAvg >= 15 ? 'good' : ($studentAvg >= 10 ? 'ok' : 'bad') }}">
                                        {{ $app }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8;">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Actions footer --}}
    <div class="bng-form-actions" style="margin-top: 20px;">
        <a href="{{ route('teacher.bulletin_ng.step4', $config->id) }}" class="bng-btn bng-btn-secondary">
            ← {{ $isEN ? 'Back' : 'Retour' }}
        </a>
        @if(!$config->notes_verrouillee)
            <button class="bng-btn bng-btn-danger" id="lockNotesBtn2">
                🔒 {{ $isEN ? 'End Grade Entry & Lock' : 'Terminer la Saisie & Verrouiller' }}
            </button>
        @else
            <a href="{{ route('teacher.bulletin_ng.step6', $config->id) }}" class="bng-btn bng-btn-primary">
                {{ $isEN ? 'Next: Conduct →' : 'Suivant : Conduite →' }}
            </a>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
// Store config data for safe JS access
window.__bulletinNotesConfig = {
    configId: {{ $config->id }},
    isEN: {{ $isEN ? 'true' : 'false' }},
    locked: {{ $config->notes_verrouillee ?? false ? 'true' : 'false' }},
    totalStudents: {{ $students->count() }}
};
</script>

<script>
(function () {
    const configId = window.__bulletinNotesConfig.configId;
    const csrf     = document.querySelector('meta[name=csrf-token]').content;
    const isEN     = window.__bulletinNotesConfig.isEN;
    const locked   = window.__bulletinNotesConfig.locked;
    const totalStudents = window.__bulletinNotesConfig.totalStudents;

    // Debounce auto-save
    let saveTimer = null;

    document.querySelectorAll('.bng-note-input').forEach(input => {
        input.addEventListener('input', function () {
            const val = this.value;
            const num = parseFloat(val);
            if (val !== '' && (isNaN(num) || num < 0 || num > 20)) {
                this.style.borderColor = '#ef4444';
                return;
            }
            this.style.borderColor = '';
            this.className = 'bng-note-input' + (val === '' ? '' : (num < 10 ? ' note-fail' : ' note-pass'));
            clearTimeout(saveTimer);
            saveTimer = setTimeout(() => saveNote(this), 600);
        });
    });

    async function saveNote(input) {
        const studentId = input.dataset.student;
        const subjectId = input.dataset.subject;
        const note      = input.value === '' ? null : parseFloat(input.value);

        try {
            const res = await fetch(`/teacher/bulletin-ng/${configId}/save-note`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ ng_student_id: studentId, ng_subject_id: subjectId, note }),
            });
            const data = await res.json();
            if (!data.success) return;

            // Mettre à jour stats
            updateStats(data.stats);
            // Mettre à jour la ligne de l'élève
            updateStudentRow(studentId, data.stats);
        } catch (e) { console.error(e); }
    }

    function updateStats(stats) {
        document.getElementById('statAvg').textContent     = parseFloat(stats.avg).toFixed(2);
        document.getElementById('statPct').textContent     = stats.pct + '%';
        document.getElementById('statMax').textContent     = parseFloat(stats.max).toFixed(2);
        document.getElementById('statMin').textContent     = parseFloat(stats.min).toFixed(2);
        document.getElementById('statPassing').textContent = stats.passing + '/' + totalStudents;
    }

    function updateStudentRow(studentId, stats) {
        const avg  = stats.avgs?.[studentId] || 0;
        const rank = stats.ranks?.[studentId];

        const avgEl  = document.getElementById('avg-' + studentId);
        const rankEl = document.getElementById('rank-' + studentId);
        const appEl  = document.getElementById('app-' + studentId);

        if (avgEl) {
            avgEl.textContent = avg > 0 ? avg.toFixed(2) : '—';
            avgEl.className   = 'bng-avg-badge ' + (avg >= 10 ? 'avg-pass' : (avg > 0 ? 'avg-fail' : ''));
        }
        if (rankEl) rankEl.textContent = rank ? rank + '/' + totalStudents : '—';
        if (appEl && avg > 0) {
            const labels = isEN
                ? ['Fail', 'Pass', 'Fairly Good', 'Good', 'Excellent']
                : ['Échec', 'Passable', 'Assez Bien', 'Bien', 'Excellent'];
            const cls    = avg >= 15 ? 'good' : (avg >= 10 ? 'ok' : 'bad');
            const label  = avg < 10 ? labels[0] : avg < 12 ? labels[1] : avg < 15 ? labels[2] : avg < 17 ? labels[3] : labels[4];
            appEl.innerHTML = `<span class="bng-app-badge app-${cls}">${label}</span>`;
        }
    }

    // Lock notes
    async function lockNotes() {
        if (!confirm(isEN
            ? 'Lock all grades? This action cannot be undone.'
            : 'Verrouiller toutes les notes ? Cette action est irréversible.')) return;

        const res = await fetch(`/teacher/bulletin-ng/${configId}/verrouiller`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = `{{ route('teacher.bulletin_ng.step6', $config->id) }}`;
        }
    }

    document.getElementById('lockNotesBtn')?.addEventListener('click', lockNotes);
    document.getElementById('lockNotesBtn2')?.addEventListener('click', lockNotes);
})();
</script>
@endpush
