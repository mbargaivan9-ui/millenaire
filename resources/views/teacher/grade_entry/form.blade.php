@extends('layouts.app')

@section('title', 'Formulaire de Saisie des Notes')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2">📝 Saisie des Notes</h1>
                    <p class="text-muted">
                        {{ $session->getSessionLabel() }} — {{ $session->config->nom_classe }}
                    </p>
                </div>
                <a href="{{ route('teacher.grades.bulletin_ng.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>

    @if ($mySubjects->isEmpty())
    <div class="alert alert-warning">
        <strong>Aucune matière</strong> — Vous n'avez aucune matière affiliée à cette session.
    </div>
    @else
    <!-- Progress Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Progression de la saisie</h5>
                    <div id="progress-container" class="mt-3">
                        <!-- Loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Entry Form -->
    <div class="row">
        <!-- Subjects Tabs -->
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                @foreach ($mySubjects as $index => $subject)
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link {{ $index === 0 ? 'active' : '' }}"
                        id="subject-{{ $subject->id }}-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#subject-{{ $subject->id }}-pane"
                        type="button"
                        role="tab"
                        aria-controls="subject-{{ $subject->id }}-pane"
                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                        {{ $subject->nom }}
                    </button>
                </li>
                @endforeach
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-3">
                @foreach ($mySubjects as $index => $subject)
                <div
                    class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                    id="subject-{{ $subject->id }}-pane"
                    role="tabpanel"
                    aria-labelledby="subject-{{ $subject->id }}-tab">

                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">{{ $subject->nom }}</h5>
                            @if ($subject->nom_prof)
                            <small class="text-muted">Profeseur: {{ $subject->nom_prof }}</small>
                            @endif
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Étudiant</th>
                                            <th>Matricule</th>
                                            <th style="width: 120px;">
                                                Séquence {{ $session->sequence_number }}
                                            </th>
                                            <th style="width: 100px;">
                                                <small class="text-muted">Actions</small>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($students as $student)
                                        <tr>
                                            <td>
                                                <strong>{{ $student->nom }}</strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $student->matricule }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $note = $gradesBySubject[$subject->id][$student->id] ?? null;
                                                    $noteValue = $note ? $note->note : '';
                                                @endphp
                                                <input
                                                    type="number"
                                                    class="form-control form-control-sm grade-input"
                                                    data-session-id="{{ $session->id }}"
                                                    data-student-id="{{ $student->id }}"
                                                    data-subject-id="{{ $subject->id }}"
                                                    data-sequence="{{ $session->sequence_number }}"
                                                    value="{{ $noteValue }}"
                                                    min="0"
                                                    max="20"
                                                    step="0.5"
                                                    placeholder="0-20">
                                            </td>
                                            <td>
                                                <div class="spinner-border spinner-border-sm d-none save-spinner-{{ $student->id }}"
                                                     role="status">
                                                    <span class="visually-hidden">Enregistrement...</span>
                                                </div>
                                                <small class="text-success save-checkmark-{{ $student->id }} d-none">
                                                    ✓ Sauvegardé
                                                </small>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                Aucun étudiant dans cette classe
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionId = '{{ $session->id }}';

    // Load progress on page load
    loadProgress();

    // Auto-save grades on blur
    document.querySelectorAll('.grade-input').forEach(input => {
        input.addEventListener('blur', function() {
            saveGrade(this);
        });

        // Allow Enter key to save
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                saveGrade(this);
            }
        });
    });

    function saveGrade(input) {
        const sessionId = input.dataset.sessionId;
        const studentId = input.dataset.studentId;
        const subjectId = input.dataset.subjectId;
        const sequence = input.dataset.sequence;
        const noteValue = input.value;

        // Show spinner
        const spinner = document.querySelector(`.save-spinner-${studentId}`);
        const checkmark = document.querySelector(`.save-checkmark-${studentId}`);
        if (spinner) spinner.classList.remove('d-none');
        if (checkmark) checkmark.classList.add('d-none');

        fetch(`/teacher/grades/bulletin-ng/${sessionId}/save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                student_id: studentId,
                subject_id: subjectId,
                sequence_number: sequence,
                note: noteValue,
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide spinner, show checkmark
                if (spinner) spinner.classList.add('d-none');
                if (checkmark) checkmark.classList.remove('d-none');

                // Hide checkmark after 2 seconds
                setTimeout(() => {
                    if (checkmark) checkmark.classList.add('d-none');
                }, 2000);

                // Reload progress
                loadProgress();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la sauvegarde');
        });
    }

    function loadProgress() {
        fetch(`/teacher/grades/bulletin-ng/${sessionId}/progress`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderProgress(data);
                }
            })
            .catch(error => console.error('Error loading progress:', error));
    }

    function renderProgress(data) {
        const container = document.getElementById('progress-container');
        if (!container) return;

        let html = `
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <strong>Progression globale</strong>
                    <strong>${data.overall_completion}%</strong>
                </div>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar"
                         style="width: ${data.overall_completion}%"
                         aria-valuenow="${data.overall_completion}" aria-valuemin="0" aria-valuemax="100">
                        ${data.overall_completion}%
                    </div>
                </div>
            </div>
        `;

        if (data.subjects && data.subjects.length > 0) {
            html += '<div class="row mt-3">';
            data.subjects.forEach(subject => {
                const badgeClass = subject.percentage === 100 ? 'bg-success' : 
                                  subject.percentage >= 50 ? 'bg-info' : 'bg-warning';
                html += `
                    <div class="col-md-6 mb-2">
                        <div class="card-sm p-2">
                            <small class="d-block"><strong>${subject.nom}</strong></small>
                            <small class="text-muted d-block">${subject.grades_entered}/${subject.total_students}</small>
                            <div class="progress mt-1" style="height: 8px;">
                                <div class="progress-bar ${badgeClass}" role="progressbar"
                                     style="width: ${subject.percentage}%"
                                     aria-valuenow="${subject.percentage}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">${subject.percentage}%</small>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        }

        container.innerHTML = html;
    }

    // Reload progress every 10 seconds
    setInterval(loadProgress, 10000);
});
</script>
@endpush

<style>
.grade-input {
    text-align: center;
    font-weight: bold;
    border: 2px solid #ddd;
}

.grade-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.card-sm {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
}
</style>
@endsection
