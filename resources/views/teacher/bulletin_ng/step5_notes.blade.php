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
                                    $noteVal = is_array($notes) ? ($notes[$noteKey]?->note ?? null) : $notes->get($studentId)?->firstWhere('ng_subject_id', $sub->id)?->note;
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
                                {{ $studentRank ? "{$studentRank}/{$students->count()}" : '—' }}
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
    configId: {!! json_encode($config->id) !!},
    sessionId: {!! json_encode($session->id ?? null) !!},
    isEN: {{ $isEN ? 'true' : 'false' }},
    locked: {{ $config->notes_verrouillee ?? false ? 'true' : 'false' }},
    totalStudents: {{ $students->count() }}
};
</script>

<script>
(function () {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStep5);
    } else {
        initStep5();
    }

    function initStep5() {
        const configId = window.__bulletinNotesConfig.configId;
        const csrf     = document.querySelector('meta[name=csrf-token]').content;
        const isEN     = window.__bulletinNotesConfig.isEN;
        const locked   = window.__bulletinNotesConfig.locked;
        const totalStudents = window.__bulletinNotesConfig.totalStudents;

        // Debounce auto-save - REDUCED to 300ms from 600ms
        let saveTimer = null;
        const SAVE_DELAY = 300;  // 300ms instead of 600ms
        
        // Log all input elements on page load to verify IDs
        console.log('%c🔍 PAGE LOADED - VERIFY INPUT IDs', 'background:blue; color:white; padding:5px; font-weight:bold');
        const inputs = document.querySelectorAll('.bng-note-input');
        console.log(`✅ Found ${inputs.length} input fields`);
        
        // Create a mapping of IDs to check for duplicates or issues
        const idMap = {};
        inputs.forEach((inp, idx) => {
            const key = `${inp.dataset.student}_${inp.dataset.subject}`;
            const dataLog = {
                index: idx,
                student: inp.dataset.student,
                subject: inp.dataset.subject,
                currentValue: inp.value,
                noteKey: key
            };
            console.log(`  Input ${idx + 1}:`, dataLog);
            idMap[key] = (idMap[key] || 0) + 1;
        });
        
        // Check for duplicate IDs
        const duplicates = Object.entries(idMap).filter(([k, v]) => v > 1);
        if (duplicates.length > 0) {
            console.error('❌ DUPLICATE IDs FOUND:', duplicates);
        } else {
            console.log('✅ All input IDs are unique');
        }

        // Attach event listeners to all inputs
        console.log('%c📝 ATTACHING EVENT LISTENERS', 'background:green; color:white; padding:5px; font-weight:bold');
        document.querySelectorAll('.bng-note-input').forEach((input, idx) => {
            input.addEventListener('input', function () {
                let val = this.value;
                const studentId = this.dataset.student;
                const subjectId = this.dataset.subject;
                
                // Don't process empty values
                if (val === '') {
                    this.style.borderColor = '';
                    this.className = 'bng-note-input';
                    return;
                }
                
                // Parse the value
                let num = parseFloat(val);
                
                // Validate: must be between 0 and 20
                if (isNaN(num) || num < 0 || num > 20) {
                    // Clamp to valid range
                    if (num < 0) {
                        num = 0;
                        this.value = '0';
                    } else if (num > 20) {
                        num = 20;
                        this.value = '20';
                    } else {
                        // NaN or other invalid
                        this.value = '';
                        this.style.borderColor = '';
                        this.className = 'bng-note-input';
                        console.warn('❌ Invalid value (non-numeric):', val);
                        return;
                    }
                    this.style.borderColor = '#fbbf24';
                    this.title = isEN ? 'Auto-corrected to ' + num : 'Auto-corrigée à ' + num;
                    console.warn('⚠️  Value clamped:', { original: val, corrected: num });
                } else {
                    // Valid value
                    this.style.borderColor = '';
                    this.title = '';
                }
                
                console.log(`🖊️  Input change at index ${idx}:`, {
                    studentId,
                    subjectId,
                    value: this.value,
                    parsed: num,
                    delay: SAVE_DELAY + 'ms'
                });
                
                // Update style based on pass/fail
                this.className = 'bng-note-input' + (num < 10 ? ' note-fail' : ' note-pass');
                
                // Clear existing timeout and set new one
                clearTimeout(saveTimer);
                saveTimer = setTimeout(() => {
                    console.log('%c💾 SAVE TRIGGERED (debounce complete)', 'background:orange; color:white; padding:3px');
                    saveNote(this, studentId, subjectId, num);
                }, SAVE_DELAY);
            });
        });
        
        console.log(`✅ Event listeners attached to ${inputs.length} inputs`);

        async function saveNote(inputElement, studentId, subjectId, note) {
            const note_value = inputElement.value === '' ? null : parseFloat(inputElement.value);

            console.log('%c📤 SENDING SAVE REQUEST', 'background:purple; color:white; padding:3px', {
                studentId,
                subjectId,
                note: note_value,
                noteKey: `${studentId}_${subjectId}`
            });

            try {
                const requestBody = {
                    ng_student_id: studentId,
                    ng_subject_id: subjectId,
                    note: note_value
                };
                
                console.log('📦 Request body:', JSON.stringify(requestBody, null, 2));

                const res = await fetch(`/teacher/bulletin-ng/${configId}/save-note`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': csrf 
                    },
                    body: JSON.stringify(requestBody),
                });
                
                console.log(`📬 Response received (status: ${res.status})`);
                const data = await res.json();
                
                console.log('%c✅ SAVE RESPONSE', 'background:cyan; color:black; padding:3px', {
                    success: data.success,
                    note: data.note,
                    statsAvg: data.stats?.avg,
                    statsAvailable: !!data.stats,
                    studentAvgsKeys: data.stats?.avgs ? Object.keys(data.stats.avgs).slice(0, 3) : 'N/A'
                });
                
                if (!data.success) {
                    console.error('❌ Save was not successful:', data);
                    return;
                }

                if (!data.stats) {
                    console.error('❌ No stats in response!', data);
                    return;
                }

                // Mettre à jour stats
                updateStats(data.stats);
                // Mettre à jour la ligne de l'élève
                updateStudentRow(studentId, data.stats);
                
                console.log('✅ Page updated with new stats');
            } catch (e) { 
                console.error('%c❌ FETCH ERROR', 'background:red; color:white; padding:3px', e);
            }
        }

        function updateStats(stats) {
            if (!stats) {
                console.warn('⚠️ No stats provided to updateStats');
                return;
            }
            
            console.log('%c📊 UPDATING STATS BAR', 'background:#FF6B6B; color:white; padding:3px', stats);
            
            const updates = {
                'statAvg': stats.avg?.toFixed(2) || '0.00',
                'statPct': (stats.pct || 0) + '%',
                'statMax': stats.max?.toFixed(2) || '0.00',
                'statMin': stats.min?.toFixed(2) || '0.00',
                'statPassing': (stats.passing || 0) + '/' + totalStudents,
            };
            
            for (const [id, text] of Object.entries(updates)) {
                const el = document.getElementById(id);
                if (el) {
                    el.textContent = text;
                    console.log(`  ✅ #${id} = "${text}"`);
                } else {
                    console.warn(`  ❌ #${id} NOT FOUND!`);
                }
            }
        }

        function updateStudentRow(studentId, stats) {
            if (!stats || !stats.avgs) {
                console.warn('⚠️ No student stats provided, studentId:', studentId);
                console.log('Stats structure:', stats);
                return;
            }
            
            console.log('%c👨‍🎓 UPDATING STUDENT ROW', 'background:#4ECDC4; color:white; padding:3px', {
                studentId,
                avgValue: stats.avgs[studentId],
                rankValue: stats.ranks ? stats.ranks[studentId] : 'N/A'
            });
            
            const avg  = (stats.avgs && stats.avgs[studentId]) ? stats.avgs[studentId] : 0;
            const rank = (stats.ranks && stats.ranks[studentId]) ? stats.ranks[studentId] : null;

            const avgEl  = document.getElementById('avg-' + studentId);
            const rankEl = document.getElementById('rank-' + studentId);
            const appEl  = document.getElementById('app-' + studentId);

            // Update average
            if (avgEl) {
                const newText = avg > 0 ? parseFloat(avg).toFixed(2) : '—';
                avgEl.textContent = newText;
                avgEl.className   = 'bng-avg-badge ' + (avg >= 10 ? 'avg-pass' : (avg > 0 ? 'avg-fail' : ''));
                console.log(`  ✅ avg-${studentId} = "${newText}"`);
            } else {
                console.warn(`  ❌ avg-${studentId} NOT FOUND!`);
            }
            
            // Update rank
            if (rankEl) {
                const newText = rank ? rank + '/' + totalStudents : '—';
                rankEl.textContent = newText;
                console.log(`  ✅ rank-${studentId} = "${newText}"`);
            } else {
                console.warn(`  ❌ rank-${studentId} NOT FOUND!`);
            }
            
            // Update appreciation
            if (appEl && avg > 0) {
                const labels = isEN
                    ? ['Fail', 'Pass', 'Fairly Good', 'Good', 'Excellent']
                    : ['Échec', 'Passable', 'Assez Bien', 'Bien', 'Excellent'];
                const cls    = avg >= 15 ? 'good' : (avg >= 10 ? 'ok' : 'bad');
                const label  = avg < 10 ? labels[0] : avg < 12 ? labels[1] : avg < 15 ? labels[2] : avg < 17 ? labels[3] : labels[4];
                appEl.innerHTML = `<span class="bng-app-badge app-${cls}">${label}</span>`;
                console.log(`  ✅ app-${studentId} = "${label}"`);
            } else if (appEl && avg === 0) {
                appEl.innerHTML = '<span style="color: #94a3b8;">—</span>';
            }
        }

        // Lock notes
        async function lockNotes() {
            if (!confirm(isEN
                ? 'Lock all grades? This action cannot be undone.'
                : 'Verrouiller toutes les notes ? Cette action est irréversible.')) return;

            console.log('%c🔒 LOCKING NOTES', 'background:red; color:white; padding:5px; font-weight:bold');

            const sessionId = window.__bulletinNotesConfig.sessionId;
            if (!sessionId) {
                alert(isEN ? 'Error: No session found' : 'Erreur: Pas de session trouvée');
                return;
            }

            try {
                const res = await fetch(`/teacher/bulletin-ng/${sessionId}/lock`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf },
                });
                const data = await res.json();
                
                console.log('🔒 Lock response:', data);
                
                if (data.success) {
                    console.log('✅ Notes locked successfully! Redirecting to Step 6...');
                    const step6Url = `{{ route('teacher.bulletin_ng.step6', $config->id) }}`;
                    console.log('📍 Redirect URL:', step6Url);
                    window.location.href = step6Url;
                } else {
                    console.error('❌ Lock failed:', data);
                    alert(isEN ? 'Failed to lock grades' : 'Erreur lors du verrouillage');
                }
            } catch (e) {
                console.error('%c❌ LOCK ERROR', 'background:red; color:white; padding:3px', e);
                alert(isEN ? 'Error: ' + e.message : 'Erreur: ' + e.message);
            }
        }

        // Attach lock button listeners
        const lockBtn1 = document.getElementById('lockNotesBtn');
        const lockBtn2 = document.getElementById('lockNotesBtn2');
        
        if (lockBtn1) {
            lockBtn1.addEventListener('click', lockNotes);
            console.log('✅ Lock button 1 listener attached');
        } else {
            console.warn('❌ Lock button 1 not found');
        }
        
        if (lockBtn2) {
            lockBtn2.addEventListener('click', lockNotes);
            console.log('✅ Lock button 2 listener attached');
        } else {
            console.warn('❌ Lock button 2 not found');
        }
    }
})();
</script>
@endpush
