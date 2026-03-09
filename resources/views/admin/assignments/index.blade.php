{{--
    |--------------------------------------------------------------------------
    | admin/assignments/index.blade.php — Affectations Drag & Drop
    |--------------------------------------------------------------------------
    | Phase 3 — Section 4.2 — Panneau Affectations Admin
    | Drag & Drop SortableJS pour affecter les professeurs aux classes
    --}}

@extends('layouts.app')

@section('title', 'Gestion des Affectations')

@push('styles')
<style>
/* ─── Assignments Layout ──────────────────────────────── */
.assignments-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
@media (max-width:991px) { .assignments-wrapper { grid-template-columns: 1fr; } }

/* Droppable Zones */
.droppable-zone {
    min-height: 80px;
    background: var(--primary-bg);
    border: 2px dashed var(--border);
    border-radius: var(--radius-md);
    padding: .75rem;
    transition: all .2s ease;
}
.droppable-zone.drag-over {
    border-color: var(--primary);
    background: var(--primary-hover);
    box-shadow: 0 0 0 3px rgba(13,148,136,.15);
}

/* Draggable Teacher Cards */
.teacher-draggable {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: .6rem .9rem;
    margin-bottom: .5rem;
    cursor: grab;
    display: flex; align-items: center; gap: .75rem;
    transition: all .2s ease;
    user-select: none;
}
.teacher-draggable:hover { border-color: var(--primary); box-shadow: var(--shadow-md); }
.teacher-draggable.sortable-chosen { opacity: .7; cursor: grabbing; }
.teacher-draggable.sortable-ghost { opacity: .3; border: 2px dashed var(--primary); }

.teacher-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; color: white; font-size: .85rem; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
}

/* Class Cards */
.class-assignment-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
    margin-bottom: 1rem;
    transition: box-shadow .2s ease;
}
.class-assignment-card:hover { box-shadow: var(--shadow-md); }

.class-card-header {
    padding: .8rem 1rem;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white;
    display: flex; align-items: center; justify-content: space-between;
}
.class-card-body { padding: .75rem; }

/* Subject Grid */
.subject-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
@media (max-width:767px) { .subject-grid { grid-template-columns: 1fr; } }

.subject-row {
    background: var(--surface-2);
    border-radius: var(--radius);
    padding: .5rem .75rem;
    display: flex; align-items: center; justify-content: space-between;
    gap: .5rem;
}
.subject-name { font-size: .82rem; font-weight: 600; color: var(--text-primary); }
.subject-teacher-select { font-size: .78rem; border: 1px solid var(--border); border-radius: 6px; padding: .2rem .4rem; }

/* History Table */
.history-badge {
    font-size: .7rem; padding: .25rem .6rem; border-radius: 20px;
    background: var(--primary-bg); color: var(--primary);
    font-weight: 600;
}

/* Save indicator */
.save-indicator {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .82rem; padding: .3rem .7rem; border-radius: 20px;
}
.save-indicator.saving  { background: #fffbeb; color: #d97706; }
.save-indicator.saved   { background: #ecfdf5; color: #059669; }
.save-indicator.error   { background: #fef2f2; color: #dc2626; }
</style>
@endpush

@section('content')

<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,var(--primary),var(--primary-light))">
            <i data-lucide="git-branch"></i>
        </div>
        <div>
            <h1 class="page-title">{{ app()->getLocale() === 'fr' ? 'Gestion des Affectations' : 'Assignment Management' }}</h1>
            <p class="page-subtitle text-muted">
                {{ app()->getLocale() === 'fr' ? 'Glisser-déposer pour affecter les professeurs aux classes' : 'Drag and drop to assign teachers to classes' }}
            </p>
        </div>
        <div class="ms-auto d-flex gap-2">
            <span id="save-indicator" class="save-indicator" style="display:none!important"></span>
            <a href="{{ route('admin.assignments.history') }}" class="btn btn-light">
                <i data-lucide="clock" style="width:16px" class="me-1"></i>
                {{ app()->getLocale() === 'fr' ? 'Historique' : 'History' }}
            </a>
        </div>
    </div>
</div>

{{-- Filtres Card --}}
<div class="card mb-20">
    <div class="card-header">
        <i data-lucide="filter" style="width:16px;height:16px"></i>
        <span>{{ app()->getLocale() === 'fr' ? 'Filtres' : 'Filters' }}</span>
    </div>
    <div class="card-body">
        <form method="GET" class="search-filters">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;align-items:flex-end">
                <div>
                    <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Professeur' : 'Teacher' }}</label>
                    <input type="text" class="form-control" name="search_teacher" 
                           placeholder="{{ app()->getLocale() === 'fr' ? 'Nom ou email...' : 'Name or email...' }}" 
                           value="{{ request('search_teacher') }}">
                </div>
                <div>
                    <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Classe' : 'Class' }}</label>
                    <select class="form-control" name="filter_class">
                        <option value="">{{ app()->getLocale() === 'fr' ? 'Toutes les classes' : 'All classes' }}</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('filter_class') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Matière' : 'Subject' }}</label>
                    <select class="form-control" name="filter_subject">
                        <option value="">{{ app()->getLocale() === 'fr' ? 'Toutes les matières' : 'All subjects' }}</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('filter_subject') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Statut' : 'Status' }}</label>
                    <select class="form-control" name="filter_status">
                        <option value="">{{ app()->getLocale() === 'fr' ? 'Tous les statuts' : 'All statuses' }}</option>
                        <option value="active" {{ request('filter_status') === 'active' ? 'selected' : '' }}>{{ app()->getLocale() === 'fr' ? 'Actif' : 'Active' }}</option>
                        <option value="inactive" {{ request('filter_status') === 'inactive' ? 'selected' : '' }}>{{ app()->getLocale() === 'fr' ? 'Inactif' : 'Inactive' }}</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i data-lucide="search" style="width:13px;height:13px"></i>
                        {{ app()->getLocale() === 'fr' ? 'Filtrer' : 'Filter' }}
                    </button>
                </div>
                <div>
                    <a href="{{ route('admin.assignments.index') }}" class="btn btn-outline w-100">
                        <i data-lucide="rotate-ccw" style="width:13px;height:13px"></i>
                        {{ app()->getLocale() === 'fr' ? 'Réinitialiser' : 'Reset' }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ─── Tabs ─────────────────────────────────────────────────────────────── --}}
<div x-data="{ tab: 'principals' }">
    <div class="tab-nav d-flex gap-2 mb-4">
        <button class="btn" :class="tab==='principals' ? 'btn-primary' : 'btn-light'" @click="tab='principals'">
            <i data-lucide="star" style="width:16px" class="me-1"></i>
            {{ app()->getLocale() === 'fr' ? 'Professeurs Principaux' : 'Head Teachers' }}
        </button>
        <button class="btn" :class="tab==='subjects' ? 'btn-primary' : 'btn-light'" @click="tab='subjects'">
            <i data-lucide="book-open" style="width:16px" class="me-1"></i>
            {{ app()->getLocale() === 'fr' ? 'Matières & Enseignants' : 'Subjects & Teachers' }}
        </button>
        <button class="btn" :class="tab==='advanced' ? 'btn-primary' : 'btn-light'" @click="tab='advanced'">
            <i data-lucide="layers" style="width:16px" class="me-1"></i>
            {{ app()->getLocale() === 'fr' ? 'Affectations Avancées' : 'Advanced Assignments' }}
        </button>
    </div>

    {{-- ════════════ TAB: PROFESSEURS PRINCIPAUX ════════════ --}}
    <div x-show="tab === 'principals'">
        <div class="assignments-wrapper">

            {{-- Colonne Gauche: Liste des Enseignants --}}
            <div>
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i data-lucide="users" style="width:16px" class="me-2"></i>
                            {{ app()->getLocale() === 'fr' ? 'Enseignants Disponibles' : 'Available Teachers' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        {{-- Search --}}
                        <div class="mb-3">
                            <input type="search" id="teacher-search" class="form-control form-control-sm"
                                   placeholder="{{ app()->getLocale() === 'fr' ? 'Rechercher un enseignant...' : 'Search teacher...' }}"
                                   oninput="filterTeachers(this.value)">
                        </div>

                        <div id="teachers-list" class="droppable-zone" style="min-height:300px">
                            @foreach($teachers as $teacher)
                            <div class="teacher-draggable"
                                 data-teacher-id="{{ $teacher->id }}"
                                 data-teacher-name="{{ $teacher->user->display_name ?? $teacher->user->name }}"
                                 draggable="true">
                                <div class="teacher-avatar">
                                    {{ strtoupper(substr($teacher->user->name ?? 'T', 0, 1)) }}
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate" style="font-size:.85rem">
                                        {{ $teacher->user->display_name ?? $teacher->user->name }}
                                    </div>
                                    <div class="text-muted" style="font-size:.75rem">
                                        {{ $teacher->subjects->pluck('name')->take(2)->implode(', ') ?: '—' }}
                                    </div>
                                </div>
                                @if($teacher->is_prof_principal)
                                <span class="badge" style="background:var(--primary);font-size:.65rem;white-space:nowrap">
                                    <i data-lucide="star" style="width:10px"></i>
                                    {{ $teacher->headClass?->name }}
                                </span>
                                @endif
                                <i data-lucide="grip-vertical" style="width:16px;color:var(--text-muted)"></i>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Colonne Droite: Classes --}}
            <div>
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i data-lucide="building-2" style="width:16px" class="me-2"></i>
                            {{ app()->getLocale() === 'fr' ? 'Classes — Glisser un enseignant ici' : 'Classes — Drop a teacher here' }}
                        </h6>
                    </div>
                    <div class="card-body" style="max-height:600px;overflow-y:auto">
                        @foreach($classes as $class)
                        <div class="class-assignment-card">
                            <div class="class-card-header">
                                <div>
                                    <strong>{{ $class->name }}</strong>
                                    @if($class->section)
                                    <span class="badge ms-2" style="background:rgba(255,255,255,.2);font-size:.7rem">
                                        {{ $class->section === 'francophone' ? '🇫🇷 FR' : '🇬🇧 EN' }}
                                    </span>
                                    @endif
                                </div>
                                <div style="font-size:.75rem;opacity:.85">
                                    {{ $class->students_count ?? $class->students->count() }} {{ app()->getLocale() === 'fr' ? 'élèves' : 'students' }}
                                </div>
                            </div>
                            <div class="class-card-body">
                                <div class="droppable-zone class-drop-zone"
                                     data-class-id="{{ $class->id }}"
                                     data-class-name="{{ $class->name }}"
                                     ondragover="onDragOver(event)"
                                     ondragleave="onDragLeave(event)"
                                     ondrop="onDrop(event, {{ $class->id }})">
                                    @if($class->headTeacher)
                                    <div class="teacher-draggable" style="cursor:default">
                                        <div class="teacher-avatar" style="background:linear-gradient(135deg,#10b981,#059669)">
                                            {{ strtoupper(substr($class->headTeacher->user->name ?? 'P', 0, 1)) }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold" style="font-size:.82rem">
                                                {{ $class->headTeacher->user->display_name ?? $class->headTeacher->user->name }}
                                            </div>
                                            <div style="font-size:.72rem;color:var(--success)">
                                                <i data-lucide="check-circle" style="width:12px"></i>
                                                Prof. Principal
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-light"
                                                onclick="removePrincipal({{ $class->id }})"
                                                title="Retirer">
                                            <i data-lucide="x" style="width:12px"></i>
                                        </button>
                                    </div>
                                    @else
                                    <div class="text-center py-2" style="color:var(--text-muted);font-size:.8rem">
                                        <i data-lucide="arrow-down-circle" style="width:20px;opacity:.5"></i>
                                        <p class="mb-0 mt-1">{{ app()->getLocale() === 'fr' ? 'Déposer un enseignant ici' : 'Drop a teacher here' }}</p>
                                    </div>
                                    @endif
                                </div>
                                <div id="class-{{ $class->id }}-assignments" style="margin-top:.75rem">
                                    <!-- Additional assignments will be loaded here -->
                                </div>
                                <button class="btn btn-outline-primary btn-sm w-100 mt-2" 
                                        onclick="showClassTeachersModal({{ $class->id }}, '{{ $class->name }}')"
                                        title="{{ app()->getLocale() === 'fr' ? 'Voir tous les professeurs assignés' : 'View all assigned teachers' }}">
                                    <i data-lucide="users" style="width:12px" class="me-1"></i>
                                    {{ app()->getLocale() === 'fr' ? 'Voir Affectations' : 'View Assignments' }}
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ════════════ TAB: MATIÈRES & ENSEIGNANTS ════════════ --}}
    <div x-show="tab === 'subjects'">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-3">
                <h6 class="card-title mb-0">
                    <i data-lucide="book-open" style="width:16px" class="me-2"></i>
                    {{ app()->getLocale() === 'fr' ? 'Grille d\'Affectation Matières / Enseignants / Classes' : 'Subject / Teacher / Class Assignment Grid' }}
                </h6>
                <div class="ms-auto">
                    <select id="class-filter" class="form-select form-select-sm" onchange="loadClassSubjects(this.value)" style="width:200px">
                        <option value="">{{ app()->getLocale() === 'fr' ? 'Sélectionner une classe' : 'Select a class' }}</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div id="subject-assignment-grid">
                    <div class="text-center py-5 text-muted">
                        <i data-lucide="book-open" style="width:40px;opacity:.3"></i>
                        <p class="mt-2">{{ app()->getLocale() === 'fr' ? 'Sélectionner une classe pour voir la grille d\'affectation' : 'Select a class to see the assignment grid' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════ TAB: AFFECTATIONS AVANCÉES ════════════ --}}
    <div x-show="tab === 'advanced'">
        <div class="row">
            {{-- Colonne Gauche: Sélection Professeur --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i data-lucide="user" style="width:16px" class="me-2"></i>
                            {{ app()->getLocale() === 'fr' ? 'Étape 1: Sélectionner Professeur' : 'Step 1: Select Teacher' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Professeur' : 'Teacher' }}</label>
                            <select id="advanced-teacher" class="form-select" onchange="onAdvancedTeacherChange()">
                                <option value="">{{ app()->getLocale() === 'fr' ? 'Sélectionner un professeur...' : 'Select a teacher...' }}</option>
                                @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->user->display_name ?? $teacher->user->name }} ({{ $teacher->subjects->count() }} matières)
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="advanced-teacher-info" class="alert alert-info p-2" style="display:none;font-size:.85rem">
                            <!-- Teacher info will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            {{-- Colonne Milieu: Sélection Classes & Matières --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i data-lucide="book-open" style="width:16px" class="me-2"></i>
                            {{ app()->getLocale() === 'fr' ? 'Étape 2: Classes & Matières' : 'Step 2: Classes & Subjects' }}
                        </h6>
                    </div>
                    <div class="card-body" style="max-height:400px;overflow-y:auto">
                        <div class="mb-3">
                            <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Classes (sélect. multiple)' : 'Classes (multi-select)' }}</label>
                            <select id="advanced-classes" class="form-select" multiple size="5" onchange="onAdvancedSelectionChange()">
                                <option value="">{{ app()->getLocale() === 'fr' ? '-- Sélectionner des classes --' : '-- Select classes --' }}</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">
                                {{ app()->getLocale() === 'fr' ? 'Ctrl+Clic pour sélection multiple' : 'Ctrl+Click for multi-select' }}
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Matières (sélect. multiple)' : 'Subjects (multi-select)' }}</label>
                            <select id="advanced-subjects" class="form-select" multiple size="5" onchange="onAdvancedSelectionChange()">
                                <option value="">{{ app()->getLocale() === 'fr' ? '-- Sélectionner des matières --' : '-- Select subjects --' }}</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">
                                {{ app()->getLocale() === 'fr' ? 'Ctrl+Clic pour sélection multiple' : 'Ctrl+Click for multi-select' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Colonne Droite: Résumé & Actions --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i data-lucide="check-circle" style="width:16px" class="me-2"></i>
                            {{ app()->getLocale() === 'fr' ? 'Étape 3: Résumé' : 'Step 3: Summary' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="summary-container" style="background:var(--surface-2);padding:1rem;border-radius:var(--radius);margin-bottom:1rem;min-height:200px">
                            <div id="advanced-summary" style="font-size:.85rem">
                                <div class="text-center text-muted py-4">
                                    <i data-lucide="inbox" style="width:32px;opacity:.3"></i>
                                    <p class="mt-2">{{ app()->getLocale() === 'fr' ? 'Sélectionner un professeur et des classes/matières' : 'Select a teacher and classes/subjects' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="advanced-room" placeholder="{{ app()->getLocale() === 'fr' ? 'Salle (optionnel)' : 'Room (optional)' }}">
                        </div>

                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="advanced-schedule" placeholder="{{ app()->getLocale() === 'fr' ? 'Horaire (optionnel)' : 'Schedule (optional)' }}">
                        </div>

                        <button id="advanced-submit-btn" class="btn btn-success w-100" onclick="submitAdvancedAssignments()" disabled>
                            <i data-lucide="plus" style="width:14px" class="me-1"></i>
                            {{ app()->getLocale() === 'fr' ? 'Créer Affectations' : 'Create Assignments' }}
                        </button>

                        <div id="advanced-result" style="margin-top:1rem;display:none">
                            <!-- Result message will appear here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ─── Historique --}}
@if($history->isNotEmpty())
<div class="card mt-4">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i data-lucide="clock" style="width:16px" class="me-2"></i>
            {{ app()->getLocale() === 'fr' ? 'Historique Récent des Affectations' : 'Recent Assignment History' }}
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ app()->getLocale() === 'fr' ? 'Date' : 'Date' }}</th>
                        <th>{{ app()->getLocale() === 'fr' ? 'Classe' : 'Class' }}</th>
                        <th>{{ app()->getLocale() === 'fr' ? 'Ancien Prof Principal' : 'Old Head Teacher' }}</th>
                        <th>{{ app()->getLocale() === 'fr' ? 'Nouveau Prof Principal' : 'New Head Teacher' }}</th>
                        <th>{{ app()->getLocale() === 'fr' ? 'Modifié par' : 'Changed by' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $h)
                    <tr>
                        <td style="font-size:.82rem;color:var(--text-muted)">{{ $h->changed_at?->format('d/m/Y H:i') }}</td>
                        <td><span class="history-badge">{{ $h->class?->name }}</span></td>
                        <td>{{ $h->oldTeacher?->user?->name ?? '—' }}</td>
                        <td class="fw-semibold" style="color:var(--primary)">{{ $h->newTeacher?->user?->name }}</td>
                        <td style="font-size:.82rem">{{ $h->changedBy?->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// ─── Drag & Drop State ────────────────────────────────────────────────────────
let draggedTeacherId   = null;
let draggedTeacherName = null;

// ─── Init SortableJS ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('teachers-list');
    if (list && typeof Sortable !== 'undefined') {
        Sortable.create(list, {
            group: { name: 'teachers', pull: 'clone', put: false },
            sort: false,
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onStart: (evt) => {
                draggedTeacherId   = evt.item.dataset.teacherId;
                draggedTeacherName = evt.item.dataset.teacherName;
            },
        });
    }

    // Also init native drag events for drop zones
    document.querySelectorAll('.teacher-draggable[draggable]').forEach(el => {
        el.addEventListener('dragstart', (e) => {
            draggedTeacherId   = el.dataset.teacherId;
            draggedTeacherName = el.dataset.teacherName;
            e.dataTransfer.effectAllowed = 'copy';
        });
    });

    lucide.createIcons();
});

// ─── Drag Over / Leave / Drop ────────────────────────────────────────────────
function onDragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over');
}
function onDragLeave(e) {
    e.currentTarget.classList.remove('drag-over');
}
function onDrop(e, classId) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    if (!draggedTeacherId) return;
    assignPrincipal(classId, draggedTeacherId, draggedTeacherName, e.currentTarget);
}

// ─── API: Set Principal ───────────────────────────────────────────────────────
async function assignPrincipal(classId, teacherId, teacherName, dropZone) {
    showSaveIndicator('saving');
    try {
        const res = await fetch('/admin/assignments/set-principal', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ teacher_id: teacherId, class_id: classId }),
        });
        const data = await res.json();
        if (data.success) {
            showSaveIndicator('saved');
            // Update the drop zone UI
            dropZone.innerHTML = `
                <div class="teacher-draggable" style="cursor:default">
                    <div class="teacher-avatar" style="background:linear-gradient(135deg,#10b981,#059669)">
                        ${teacherName.charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold" style="font-size:.82rem">${teacherName}</div>
                        <div style="font-size:.72rem;color:var(--success)">
                            ✓ Prof. Principal
                        </div>
                    </div>
                    <button class="btn btn-sm btn-light" onclick="removePrincipal(${classId})" title="Retirer">
                        <i data-lucide="x" style="width:12px"></i>
                    </button>
                </div>`;
            lucide.createIcons();
            showToastMessage(data.message, 'success');
        } else {
            showSaveIndicator('error');
            showToastMessage(data.message || 'Erreur', 'error');
        }
    } catch(err) {
        showSaveIndicator('error');
        showToastMessage('Erreur de connexion', 'error');
    }
}

// ─── Remove Principal ─────────────────────────────────────────────────────────
async function removePrincipal(classId) {
    if (!confirm('Retirer le professeur principal de cette classe ?')) return;
    // Post with teacher_id = null (removes principal)
    const res = await fetch('/admin/assignments/set-principal', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ teacher_id: null, class_id: classId, remove: true }),
    });
    const data = await res.json();
    if (data.success) location.reload();
}

// ─── Filter teachers ──────────────────────────────────────────────────────────
function filterTeachers(query) {
    document.querySelectorAll('.teacher-draggable').forEach(el => {
        const name = el.dataset.teacherName?.toLowerCase() ?? '';
        el.style.display = name.includes(query.toLowerCase()) ? '' : 'none';
    });
}

// ─── Load Class Subjects grid ─────────────────────────────────────────────────
async function loadClassSubjects(classId) {
    if (!classId) return;
    const grid = document.getElementById('subject-assignment-grid');
    grid.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

    const res = await fetch(`/admin/assignments/grid/${classId}`);
    const data = await res.json();

    const subjects = @json($subjects);
    const teachers = @json($teachers->map(fn($t) => ['id' => $t->id, 'name' => $t->user->display_name ?? $t->user->name]));

    grid.innerHTML = `
        <div class="subject-grid">
            ${subjects.map(s => {
                const current = data.assignments?.find(a => a.subject_id == s.id);
                return `
                <div class="subject-row">
                    <span class="subject-name">${s.name}</span>
                    <select class="subject-teacher-select" onchange="saveSubjectAssignment(${classId}, ${s.id}, this.value)">
                        <option value="">— Non assigné —</option>
                        ${teachers.map(t => `<option value="${t.id}" ${current?.teacher_id == t.id ? 'selected' : ''}>${t.name}</option>`).join('')}
                    </select>
                </div>`;
            }).join('')}
        </div>
        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-outline-primary btn-sm" onclick="exportGrid(${classId})">
                <i data-lucide="download" style="width:14px" class="me-1"></i>
                Export PDF/Excel
            </button>
        </div>`;
    lucide.createIcons();
}

async function saveSubjectAssignment(classId, subjectId, teacherId) {
    showSaveIndicator('saving');
    const res = await fetch('/admin/assignments/assign-subject', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ class_id: classId, subject_id: subjectId, teacher_id: teacherId }),
    });
    const data = await res.json();
    showSaveIndicator(data.success ? 'saved' : 'error');
}

// ─── Advanced Assignments Functions ──────────────────────────────────────────
const advancedState = {
    selectedTeacher: null,
    selectedClasses: [],
    selectedSubjects: [],
};

function onAdvancedTeacherChange() {
    advancedState.selectedTeacher = parseInt(document.getElementById('advanced-teacher').value) || null;
    updateAdvancedSummary();
}

function onAdvancedSelectionChange() {
    const classSelect = document.getElementById('advanced-classes');
    const subjectSelect = document.getElementById('advanced-subjects');
    
    advancedState.selectedClasses = Array.from(classSelect.selectedOptions).map(o => o.value);
    advancedState.selectedSubjects = Array.from(subjectSelect.selectedOptions).map(o => o.value);
    
    updateAdvancedSummary();
    updateSubmitButton();
}

function updateAdvancedSummary() {
    const summary = document.getElementById('advanced-summary');
    const teachers = @json($teachers->map(fn($t) => ['id' => $t->id, 'name' => $t->user->display_name ?? $t->user->name]));
    const classes = @json($classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name]));
    const subjects = @json($subjects);
    
    if (!advancedState.selectedTeacher) {
        summary.innerHTML = `
            <div class="text-center text-muted py-4">
                <i data-lucide="inbox" style="width:32px;opacity:.3"></i>
                <p class="mt-2">{{ app()->getLocale() === 'fr' ? 'Sélectionner un professeur et des classes/matières' : 'Select a teacher and classes/subjects' }}</p>
            </div>`;
        return;
    }
    
    const teacherName = teachers.find(t => t.id == advancedState.selectedTeacher)?.name || 'Inconnu';
    const classNames = classes.filter(c => advancedState.selectedClasses.includes(c.id.toString())).map(c => c.name);
    const subjectNames = subjects.filter(s => advancedState.selectedSubjects.includes(s.id.toString())).map(s => s.name);
    
    let html = `
        <div style="font-size:.85rem">
            <div class="mb-2">
                <strong>{{ app()->getLocale() === 'fr' ? 'Professeur:' : 'Teacher:' }}</strong><br>
                ${teacherName}
            </div>`;
    
    if (classNames.length > 0) {
        html += `
            <div class="mb-2">
                <strong>{{ app()->getLocale() === 'fr' ? 'Classes:' : 'Classes:' }}</strong><br>
                ${classNames.join(', ')}
            </div>`;
    }
    
    if (subjectNames.length > 0) {
        html += `
            <div class="mb-2">
                <strong>{{ app()->getLocale() === 'fr' ? 'Matières:' : 'Subjects:' }}</strong><br>
                ${subjectNames.join(', ')}
            </div>`;
    }
    
    const totalAssignments = classNames.length * subjectNames.length;
    if (totalAssignments > 0) {
        html += `
            <div style="background:rgba(16,185,129,.1);padding:.5rem;border-radius:.25rem;margin-top:.5rem">
                <strong style="color:var(--success)">${totalAssignments}</strong> {{ app()->getLocale() === 'fr' ? 'affectation(s) seront créées' : 'assignment(s) will be created' }}
            </div>`;
    }
    
    html += `</div>`;
    summary.innerHTML = html;
}

function updateSubmitButton() {
    const btn = document.getElementById('advanced-submit-btn');
    const canSubmit = advancedState.selectedTeacher && advancedState.selectedClasses.length > 0 && advancedState.selectedSubjects.length > 0;
    btn.disabled = !canSubmit;
}

async function submitAdvancedAssignments() {
    if (!advancedState.selectedTeacher || advancedState.selectedClasses.length === 0 || advancedState.selectedSubjects.length === 0) {
        alert('{{ app()->getLocale() === 'fr' ? 'Veuillez sélectionner un professeur, au moins une classe et au moins une matière' : 'Please select a teacher, at least one class and at least one subject' }}');
        return;
    }
    
    showSaveIndicator('saving');
    const resultDiv = document.getElementById('advanced-result');
    
    try {
        const res = await fetch('/admin/api/assignments/add-multiple', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                teacher_id: advancedState.selectedTeacher,
                class_ids: advancedState.selectedClasses,
                subject_ids: advancedState.selectedSubjects,
                room: document.getElementById('advanced-room').value || null,
                schedule: document.getElementById('advanced-schedule').value || null,
            }),
        });
        
        const data = await res.json();
        
        if (data.success) {
            showSaveIndicator('saved');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `
                <div class="alert alert-success mb-0">
                    <i data-lucide="check-circle" style="width:16px" class="me-1"></i>
                    <strong>${data.message}</strong>
                    ${data.has_errors ? `<br><small>${data.errors.join('<br>')}</small>` : ''}
                </div>`;
            lucide.createIcons();
            
            // Reset form after 2 seconds
            setTimeout(() => {
                document.getElementById('advanced-teacher').value = '';
                document.getElementById('advanced-classes').selectedIndex = -1;
                document.getElementById('advanced-subjects').selectedIndex = -1;
                advancedState.selectedTeacher = null;
                advancedState.selectedClasses = [];
                advancedState.selectedSubjects = [];
                updateAdvancedSummary();
                updateSubmitButton();
                resultDiv.style.display = 'none';
            }, 3000);
        } else {
            showSaveIndicator('error');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `<div class="alert alert-danger mb-0"><strong>✗</strong> ${data.message}</div>`;
        }
    } catch(err) {
        showSaveIndicator('error');
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = `<div class="alert alert-danger mb-0"><strong>✗</strong> {{ app()->getLocale() === 'fr' ? 'Erreur de connexion' : 'Connection error' }}</div>`;
    }
}

// ─── Show Class Teachers Modal ────────────────────────────────────────────────
async function showClassTeachersModal(classId, className) {
    try {
        const res = await fetch(`/admin/api/assignments/class/${classId}/teachers`);
        const data = await res.json();
        
        if (data.success && data.data.length > 0) {
            let html = `
                <div class="alert alert-info mb-3">
                    <strong>${className}:</strong> ${data.total} professeur(s) assigné(s)
                </div>
                <div style="max-height:400px;overflow-y:auto">`;
            
            data.data.forEach(teacher => {
                html += `
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong style="font-size:.9rem">${teacher.name}</strong><br>
                                    <small class="text-muted">${teacher.email}</small><br>
                                    <small>${teacher.subjects.join(', ')}</small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeTeacherFromClass(${teacher.id}, ${classId})">
                                    <i data-lucide="trash-2" style="width:12px"></i>
                                </button>
                            </div>
                        </div>
                    </div>`;
            });
            
            html += `</div>`;
            
            // Show modal or alert
            alert(className + ': ' + data.data.map(t => t.name).join(', '));
        } else {
            alert('{{ app()->getLocale() === 'fr' ? 'Aucun professeur assigné' : 'No teachers assigned' }}');
        }
    } catch(err) {
        alert('{{ app()->getLocale() === 'fr' ? 'Erreur' : 'Error' }}');
    }
}

async function removeTeacherFromClass(teacherId, classId) {
    if (!confirm('{{ app()->getLocale() === 'fr' ? 'Retirer ce professeur ?' : 'Remove this teacher?' }}')) return;
    
    showSaveIndicator('saving');
    try {
        const res = await fetch('/admin/api/assignments/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ teacher_id: teacherId, class_id: classId }),
        });
        
        const data = await res.json();
        if (data.success) {
            showSaveIndicator('saved');
            setTimeout(() => location.reload(), 1500);
        } else {
            showSaveIndicator('error');
        }
    } catch(err) {
        showSaveIndicator('error');
    }
}

// ─── Save Indicator ───────────────────────────────────────────────────────────
function showSaveIndicator(state) {
    const el = document.getElementById('save-indicator');
    const map = {
        saving: { class: 'saving', text: '⏳ Sauvegarde...' },
        saved:  { class: 'saved',  text: '✅ Sauvegardé' },
        error:  { class: 'error',  text: '❌ Erreur' },
    };
    el.className = `save-indicator ${map[state].class}`;
    el.textContent = map[state].text;
    el.style.display = 'inline-flex';
    if (state !== 'saving') setTimeout(() => { el.style.display = 'none'; }, 3000);
}

function showToastMessage(msg, type) {
    console.log(`[${type}] ${msg}`);
}
</script>
@endpush

