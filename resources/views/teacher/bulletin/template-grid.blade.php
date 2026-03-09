@extends('layouts.app')

@section('title', "Grille Template Bulletin — {$classe->name} — Millénaire")

@section('content')
<style>
    /* ─── Layout Principal ─────────────────────────────────────────────── */
    .template-grid-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8ecef 100%);
        min-height: 100vh;
        padding: 20px;
    }

    .template-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .template-header h1 {
        font-size: 2rem;
        font-weight: 800;
        margin: 0 0 10px 0;
    }

    .template-header .header-meta {
        display: flex;
        gap: 20px;
        align-items: center;
        flex-wrap: wrap;
        font-size: 0.95rem;
    }

    .header-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 16px;
        border-radius: 8px;
    }

    /* ─── Contrôles Supérieurs ─────────────────────────────────────────── */
    .template-controls {
        background: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .control-group {
        display: flex;
        flex-direction: column;
    }

    .control-group label {
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 6px;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .control-group select {
        padding: 10px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .control-group select:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .control-buttons {
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #2563eb;
        color: white;
    }

    .btn-primary:hover {
        background: #1d4ed8;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 2px solid #e5e7eb;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    /* ─── Tableau Excel-like ─────────────────────────────────────────────── */
    .grid-wrapper {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .grid-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .grid-table thead {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .grid-table th {
        padding: 14px 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #0f172a;
        white-space: nowrap;
        min-width: 120px;
    }

    .grid-table th:first-child {
        position: sticky;
        left: 0;
        z-index: 101;
        background: #1e293b;
        min-width: 200px;
    }

    .grid-table th.student-name {
        text-align: left;
        position: sticky;
        left: 200px;
        z-index: 101;
        background: #1e293b;
        min-width: 200px;
    }

    .grid-table tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: background 0.15s;
    }

    .grid-table tbody tr:hover {
        background: #f8fafc;
    }

    .grid-table td {
        padding: 12px;
    }

    /* ─── Colonnes Gelées ───────────────────────────────────────────────── */
    .student-col {
        position: sticky;
        left: 0;
        z-index: 50;
        background: white;
        font-weight: 600;
        border-right: 2px solid #cbd5e1;
    }

    .grid-table tbody tr:hover .student-col {
        background: #f8fafc;
    }

    .student-name-col {
        position: sticky;
        left: 200px;
        z-index: 49;
        background: white;
    }

    .grid-table tbody tr:hover .student-name-col {
        background: #f8fafc;
    }

    /* ─── Cellules Matières ───────────────────────────────────────────── */
    .subject-cell {
        text-align: center;
        padding: 0 !important;
        min-width: 140px;
    }

    .subject-cell.editable {
        background: #f0fdf4; /* Vert léger */
        border-left: 3px solid #22c55e;
    }

    .subject-cell.editable:hover {
        background: #dcfce7;
    }

    .subject-cell.non-editable {
        background: #f3f4f6;
        color: #9ca3af;
    }

    .subject-cell input {
        width: 100%;
        padding: 10px;
        border: none;
        background: transparent;
        text-align: center;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.15s;
    }

    .subject-cell input:focus {
        outline: none;
        background: white;
        box-shadow: inset 0 0 0 2px #2563eb;
    }

    .subject-cell.non-editable input {
        cursor: not-allowed;
        color: #9ca3af;
    }

    /* ─── Info Subject (header) ─────────────────────────────────────────── */
    .subject-header-info {
        font-size: 0.8rem;
        font-weight: 500;
        line-height: 1.3;
        word-wrap: break-word;
    }

    .subject-name {
        color: white;
        font-weight: 700;
    }

    .subject-teacher {
        opacity: 0.85;
        font-size: 0.75rem;
    }

    /* ─── Status Badge ──────────────────────────────────────────────────── */
    .subject-status {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-top: 4px;
    }

    .status-principal {
        background: #fbbf24;
        color: #78350f;
    }

    /* ─── Stats Footer ──────────────────────────────────────────────────– */
    .grid-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #2563eb;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: #1e293b;
    }

    /* ─── Locked State ─────────────────────────────────────────────────── */
    .locked-overlay {
        position: sticky;
        top: 0;
        background: #fef2f2;
        border-left: 4px solid #ef4444;
        padding: 16px;
        margin-bottom: 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .locked-icon {
        font-size: 1.5rem;
    }

    .locked-text {
        flex: 1;
    }

    .locked-text strong {
        color: #dc2626;
    }

    /* ─── Scrollbar Styling ────────────────────────────────────────────– */
    .grid-wrapper {
        overflow-x: auto;
    }

    .grid-wrapper::-webkit-scrollbar {
        height: 8px;
    }

    .grid-wrapper::-webkit-scrollbar-track {
        background: #f3f4f6;
    }

    .grid-wrapper::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }

    .grid-wrapper::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* ─── Loading & Empty States ───────────────────────────────────────– */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .empty-state-icon {
        font-size: 3rem;
        margin-bottom: 16px;
    }

    .empty-state-text {
        font-size: 1.1rem;
        margin-bottom: 8px;
        font-weight: 600;
    }

    /* ─── Navigation Pagination ────────────────────────────────────────– */
    .grid-pagination {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .btn-nav {
        padding: 8px 16px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-nav:hover:not(:disabled) {
        border-color: #2563eb;
        color: #2563eb;
    }

    .btn-nav:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ─── Input Feedback ────────────────────────────────────────────────– */
    .input-saving {
        opacity: 0.6;
        background: #fef3c7 !important;
    }

    .input-saved {
        animation: pulse 0.5s ease-in-out;
    }

    @keyframes pulse {
        0%, 100% {
            background: white;
        }
        50% {
            background: #dbeafe;
        }
    }

    /* ─── Responsiveness ────────────────────────────────────────────────– */
    @media (max-width: 1024px) {
        .template-controls {
            grid-template-columns: 1fr;
        }

        .grid-table th {
            padding: 10px 8px;
            font-size: 0.8rem;
        }

        .grid-table td {
            padding: 8px;
        }

        .subject-cell {
            min-width: 100px;
        }
    }
</style>

<div class="template-grid-container">
    <!-- Header -->
    <div class="template-header">
        <h1>
            <i class="fas fa-table"></i>
            Grille Template du Bulletin
        </h1>
        <p style="margin: 0; opacity: 0.9; font-size: 1.1rem;">Class {{ $classe->name }}</p>
        <div class="header-meta">
            <div class="header-meta-item">
                <i class="fas fa-graduation-cap"></i>
                <span>{{ $classe->students()->where('is_active', true)->count() }} élèves</span>
            </div>
            <div class="header-meta-item">
                <i class="fas fa-book"></i>
                <span>{{ $subjects->count() }} matières</span>
            </div>
            <div class="header-meta-item">
                <i class="fas fa-crown" style="color: #fbbf24;"></i>
                <span>Prof Principal: {{ $currentTeacher->user->name }}</span>
            </div>
        </div>
    </div>

    <!-- Locked Notice -->
    @if($isLocked)
    <div class="locked-overlay">
        <div class="locked-icon">
            <i class="fas fa-lock"></i>
        </div>
        <div class="locked-text">
            <strong>La classe est verrouillée</strong> pour le trimestre {{ $term }}.
            Les modifications sont limitées (droits Prof Principal seulement).
        </div>
    </div>
    @endif

    <!-- Controls -->
    <div class="template-controls">
        <div class="control-group">
            <label for="select-term">Trimestre</label>
            <select id="select-term" onchange="updateGrid()">
                <option value="1" {{ $term == 1 ? 'selected' : '' }}>Trimestre 1</option>
                <option value="2" {{ $term == 2 ? 'selected' : '' }}>Trimestre 2</option>
                <option value="3" {{ $term == 3 ? 'selected' : '' }}>Trimestre 3</option>
            </select>
        </div>

        <div class="control-group">
            <label for="select-sequence">Séquence</label>
            <select id="select-sequence" onchange="updateGrid()">
                <option value="1" {{ $sequence == 1 ? 'selected' : '' }}>Séquence 1</option>
                <option value="2" {{ $sequence == 2 ? 'selected' : '' }}>Séquence 2</option>
                <option value="3" {{ $sequence == 2 ? 'selected' : '' }}>Séquence 3</option>
                <option value="4" {{ $sequence == 2 ? 'selected' : '' }}>Séquence 4</option>
                <option value="5" {{ $sequence == 2 ? 'selected' : '' }}>Séquence 5</option>
                <option value="6" {{ $sequence == 2 ? 'selected' : '' }}>Séquence 6</option>
            </select>
        </div>

        <div class="control-group">
            <label for="select-year">Année Scolaire</label>
            <select id="select-year" onchange="updateGrid()">
                <option value="{{ $academicYear }}">{{ $academicYear }}</option>
            </select>
        </div>

        <div class="control-buttons">
            <button class="btn btn-primary" onclick="printGrid()">
                <i class="fas fa-print"></i> Imprimer
            </button>
            <button class="btn btn-secondary" onclick="exportToCSV()">
                <i class="fas fa-download"></i> Exporter CSV
            </button>
        </div>
    </div>

    <!-- Grid Table -->
    <div class="grid-wrapper">
        <table class="grid-table" id="mainGrid">
            <thead>
                <tr>
                    <th style="position: sticky; left: 0; z-index: 102;">
                        #
                    </th>
                    <th style="position: sticky; left: 50px; z-index: 102;">
                        Élève
                    </th>
                    <th style="position: sticky; left: 250px; z-index: 102;">
                        Matricule
                    </th>
                    @forelse($subjects as $subject)
                    @php
                        $cst = $subject->classSubjectTeachers->first();
                        $isPrincipalSubject = $cst && $cst->teacher_id === $currentTeacher->id;
                    @endphp
                    <th class="subject-cell">
                        <div class="subject-header-info">
                            <div class="subject-name">{{ $subject->name }}</div>
                            <div class="subject-teacher">
                                <i class="fas fa-user"></i> {{ $cst->teacher->user->name ?? 'N/A' }}
                            </div>
                            @if($isPrincipalSubject)
                                <div class="subject-status status-principal">
                                    <i class="fas fa-crown"></i> Ma matière
                                </div>
                            @endif
                        </div>
                    </th>
                    @empty
                    <th colspan="5" style="text-align: center; padding: 30px;">
                        <div class="empty-state">
                            <div class="empty-state-icon">📚</div>
                            <div class="empty-state-text">Aucune matière assignée</div>
                        </div>
                    </th>
                    @endforelse
                </tr>
            </thead>
            <tbody>
                @forelse($gridData as $index => $row)
                <tr data-student-id="{{ $row['id'] }}">
                    <td class="student-col">
                        {{ $index + 1 }}
                    </td>
                    <td class="student-col" style="left: 50px; width: 200px;">
                        <strong>{{ $row['name'] }}</strong>
                    </td>
                    <td class="student-col" style="left: 250px;">
                        {{ $row['matricule'] ?? '—' }}
                    </td>

                    @forelse($subjects as $subject)
                    @php
                        $subjectData = $row['subjects'][$subject->id] ?? null;
                        $cst = $subject->classSubjectTeachers->first();
                        $isPrincipalSubject = $cst && $cst->teacher_id === $currentTeacher->id;
                    @endphp
                    <td class="subject-cell {{ $isPrincipalSubject ? 'editable' : 'non-editable' }}"
                        data-subject-id="{{ $subject->id }}"
                        data-student-id="{{ $row['id'] }}">
                        @if($subjectData)
                            @if($isPrincipalSubject || !$isLocked)
                                <input 
                                    type="number"
                                    min="0"
                                    max="20"
                                    step="0.5"
                                    value="{{ $subjectData['score'] ?? '' }}"
                                    class="grade-input"
                                    data-student-id="{{ $row['id'] }}"
                                    data-subject-id="{{ $subject->id }}"
                                    data-cst-id="{{ $cst->id }}"
                                    placeholder="—"
                                    {{ $isLocked && !$isPrincipalSubject ? 'disabled' : '' }}
                                    onblur="saveGrade(event)">
                            @else
                                <div style="padding: 10px; color: #9ca3af;">
                                    {{ $subjectData['score'] ?? '—' }}
                                </div>
                            @endif
                        @endif
                    </td>
                    @empty
                    @endforelse
                </tr>
                @empty
                <tr>
                    <td colspan="100" style="text-align: center; padding: 40px;">
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <div class="empty-state-text">Pas d'élèves dans cette classe</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Statistics -->
    <div class="grid-stats">
        <div class="stat-card">
            <div class="stat-label">Élèves Traités</div>
            <div class="stat-value" id="stat-students">
                {{ count($gridData) }}
            </div>
        </div>
        <div class="stat-card" style="border-left-color: #10b981;">
            <div class="stat-label">Notes Saisies</div>
            <div class="stat-value" id="stat-grades">0</div>
        </div>
        <div class="stat-card" style="border-left-color: #f59e0b;">
            <div class="stat-label">Moyenne Classe</div>
            <div class="stat-value" id="stat-class-avg">—</div>
        </div>
        <div class="stat-card" style="border-left-color: #8b5cf6;">
            <div class="stat-label">Complétion %</div>
            <div class="stat-value" id="stat-completion">0%</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

/**
 * Sauvegarde une note au serveur
 */
async function saveGrade(event) {
    const input = event.target;
    const studentId = input.dataset.studentId;
    const subjectId = input.dataset.subjectId;
    const cstId = input.dataset.cstId;
    const score = input.value;

    if (!score && score !== '0') {
        return; // Skip empty values
    }

    const term = parseInt(document.getElementById('select-term').value);
    const sequence = parseInt(document.getElementById('select-sequence').value);
    const academicYear = document.getElementById('select-year').value;

    // Visual feedback
    input.classList.add('input-saving');

    try {
        const response = await fetch('{{ route("teacher.bulletin.save") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN,
            },
            body: JSON.stringify({
                student_id: studentId,
                class_subject_teacher_id: cstId,
                term: term,
                sequence: sequence,
                academic_year: academicYear,
                score: parseFloat(score) || null,
            }),
        });

        const data = await response.json();

        if (data.success) {
            input.classList.remove('input-saving');
            input.classList.add('input-saved');
            updateStats();
            setTimeout(() => input.classList.remove('input-saved'), 500);
        } else {
            alert('Erreur: ' + (data.error || 'Impossible de sauvegarder'));
            input.classList.remove('input-saving');
        }
    } catch (error) {
        console.error('Error saving grade:', error);
        alert('Erreur de sauvegarde. Vérifiez votre connexion.');
        input.classList.remove('input-saving');
    }
}

/**
 * Met à jour les statistiques
 */
function updateStats() {
    // Count filled grades
    const filledGrades = document.querySelectorAll('.grade-input').length;
    const totalPossible = document.querySelectorAll('.grade-input').length;
    const filledCount = Array.from(document.querySelectorAll('.grade-input')).
        filter(input => input.value).length;

    document.getElementById('stat-grades').textContent = filledCount;
    const percentage = totalPossible > 0 ? 
        Math.round((filledCount / totalPossible) * 100) : 0;
    document.getElementById('stat-completion').textContent = percentage + '%';

    // Calculate class average
    const allGrades = Array.from(document.querySelectorAll('.grade-input'))
        .map(input => parseFloat(input.value))
        .filter(val => !isNaN(val));

    if (allGrades.length > 0) {
        const avg = (allGrades.reduce((a, b) => a + b) / allGrades.length).toFixed(2);
        document.getElementById('stat-class-avg').textContent = avg + '/20';
    }
}

/**
 * Recharge la grille avec les paramètres différents
 */
function updateGrid() {
    const term = document.getElementById('select-term').value;
    const sequence = document.getElementById('select-sequence').value;
    const year = document.getElementById('select-year').value;

    const url = new URL(window.location);
    url.searchParams.set('term', term);
    url.searchParams.set('sequence', sequence);
    url.searchParams.set('academic_year', year);

    window.location.href = url.toString();
}

/**
 * Imprime la grille
 */
function printGrid() {
    window.print();
}

/**
 * Exporte les données en CSV
 */
function exportToCSV() {
    const table = document.getElementById('mainGrid');
    let csv = [];

    // Headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push('"' + th.innerText.replace(/"/g, '""') + '"');
    });
    csv.push(headers.join(','));

    // Rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            const input = td.querySelector('input');
            const value = input ? input.value : td.innerText;
            row.push('"' + value.replace(/"/g, '""') + '"');
        });
        csv.push(row.join(','));
    });

    // Download
    const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.href = url;
    link.download = `bulletin_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    updateStats();
});
</script>
@endpush

@endsection
