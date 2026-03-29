@extends('layouts.app')

@section('title', 'Bulletin NG — Matières')

@php $currentStep = 3; $isEN = $config->langue === 'EN'; @endphp

@section('content')
<div class="bng-page">

    <div class="bng-page-header">
        <div class="bng-page-header-inner">
            <div class="bng-page-icon">📚</div>
            <div>
                <h1 class="bng-page-title">
                    {{ $isEN ? 'Step 3 — Subjects' : 'Étape 3 — Matières' }}
                </h1>
                <p class="bng-page-subtitle">{{ $config->nom_classe }} | {{ $config->trimestre_label }}</p>
            </div>
        </div>
    </div>

    @include('teacher.bulletin_ng.partials.wizard_header')

    <div class="bng-card">
        <div class="bng-card-header">
            <div class="bng-card-header-icon">📚</div>
            <div>
                <div class="bng-card-title">{{ $isEN ? 'Subjects Configuration' : 'Paramétrage des Matières' }}</div>
                <div class="bng-card-subtitle" id="subjectCount">
                    {{ $subjects->count() }} {{ $isEN ? 'subject(s) configured' : 'matière(s) configurée(s)' }}
                </div>
            </div>
        </div>

        <div class="bng-card-body">
            <form action="{{ route('teacher.bulletin_ng.store-subjects', $config->id) }}"
                  method="POST" id="subjectsForm">
                @csrf

                {{-- Nombre de matières --}}
                <div class="bng-form-field" style="max-width: 220px; margin-bottom: 24px;">
                    <label class="bng-label">{{ $isEN ? 'Number of Subjects' : 'Nombre de Matières' }}</label>
                    <div style="display:flex; gap: 10px; align-items: center;">
                        <input type="number" id="nbSubjects" min="1" max="30"
                               class="bng-input" style="width: 100px;"
                               value="{{ $subjects->count() ?: 1 }}">
                        <button type="button" class="bng-btn bng-btn-ghost" id="applyNb">
                            {{ $isEN ? 'Apply' : 'Appliquer' }}
                        </button>
                    </div>
                </div>

                {{-- Table des matières --}}
                <div class="bng-table-responsive">
                    <table class="bng-table" id="subjectsTable">
                        <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>{{ $isEN ? 'Subject Name *' : 'Nom de la Matière *' }}</th>
                            <th style="width: 120px;">{{ $isEN ? 'Coeff. *' : 'Coeff. *' }}</th>
                            <th>{{ $isEN ? 'Teacher Name' : 'Nom du Professeur' }}</th>
                            <th style="width: 60px;"></th>
                        </tr>
                        </thead>
                        <tbody id="subjectsBody">
                        @if($subjects->count())
                            @foreach($subjects as $i => $sub)
                                @include('teacher.bulletin_ng.partials.subject_row', [
                                    'i'   => $i,
                                    'sub' => $sub,
                                    'isEN' => $isEN,
                                ])
                            @endforeach
                        @else
                            @include('teacher.bulletin_ng.partials.subject_row', ['i' => 0, 'sub' => null, 'isEN' => $isEN])
                        @endif
                        </tbody>
                    </table>
                </div>

                <div class="bng-form-actions" style="margin-top: 24px;">
                    <button type="button" class="bng-btn bng-btn-ghost" id="addSubjectBtn">
                        ➕ {{ $isEN ? 'Add Subject' : 'Ajouter une Matière' }}
                    </button>
                    <div style="display: flex; gap: 10px; margin-left: auto;">
                        <a href="{{ route('teacher.bulletin_ng.step2') }}?config_id={{ $config->id }}&langue={{ $config->langue }}"
                           class="bng-btn bng-btn-secondary">← {{ $isEN ? 'Back' : 'Retour' }}</a>
                        <button type="submit" class="bng-btn bng-btn-primary">
                            {{ $isEN ? 'Save & Continue →' : 'Enregistrer & Continuer →' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- Template ligne (JS) --}}
<template id="subjectRowTpl">
    <tr class="bng-subject-row" data-index="__IDX__">
        <td><span class="bng-badge bng-badge-primary">__NUM__</span></td>
        <td>
            <input type="text" name="subjects[__IDX__][nom]" class="bng-input bng-input-sm"
                   placeholder="{{ $isEN ? 'Subject name...' : 'Nom de la matière...' }}" required>
        </td>
        <td>
            <input type="number" name="subjects[__IDX__][coefficient]" class="bng-input bng-input-sm"
                   min="0.5" max="20" step="0.5" value="1" required style="text-align:center;">
        </td>
        <td>
            <input type="text" name="subjects[__IDX__][nom_prof]" class="bng-input bng-input-sm"
                   placeholder="{{ $isEN ? 'Teacher name...' : 'Nom du professeur...' }}">
        </td>
        <td>
            <button type="button" class="bng-btn-icon bng-btn-icon-danger remove-row-btn" title="Supprimer">✕</button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
// Initialize table management
const tbody    = document.getElementById('subjectsBody');
const tpl      = document.getElementById('subjectRowTpl').innerHTML;
const addBtn   = document.getElementById('addSubjectBtn');
const applyBtn = document.getElementById('applyNb');
const nbInput  = document.getElementById('nbSubjects');

function rowCount() { return tbody.querySelectorAll('.bng-subject-row').length; }

function buildRow(idx) {
    return tpl.replace(/__IDX__/g, idx).replace(/__NUM__/g, idx + 1);
}

function reindex() {
    tbody.querySelectorAll('.bng-subject-row').forEach((row, i) => {
        row.dataset.index = i;
        row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
        });
        row.querySelector('.bng-badge').textContent = i + 1;
    });
    document.getElementById('subjectCount').textContent =
        rowCount() + ' {{ $isEN ? "subject(s) configured" : "matière(s) configurée(s)" }}';
}

function addRow() {
    const idx = rowCount();
    tbody.insertAdjacentHTML('beforeend', buildRow(idx));
    tbody.lastElementChild.querySelector('input[type=text]').focus();
    document.getElementById('subjectCount').textContent =
        rowCount() + ' {{ $isEN ? "subject(s)" : "matière(s)" }}';
}

addBtn.addEventListener('click', addRow);

applyBtn.addEventListener('click', () => {
    const target = parseInt(nbInput.value) || 1;
    const current = rowCount();
    if (target > current) {
        for (let i = current; i < target; i++) addRow();
    } else if (target < current) {
        const rows = tbody.querySelectorAll('.bng-subject-row');
        for (let i = rows.length - 1; i >= target; i--) rows[i].remove();
        reindex();
    }
});

tbody.addEventListener('click', e => {
    if (e.target.classList.contains('remove-row-btn')) {
        if (rowCount() > 1) {
            e.target.closest('tr').remove();
            reindex();
        }
    }
});

// Handle form submission via AJAX - MUST attach BEFORE ANY REDIRECT
const form = document.getElementById('subjectsForm');
if (form) {
    form.addEventListener('submit', function(e) {
        console.log('📤 Form submitted - intercepting with AJAX...');
        e.preventDefault();
        e.stopPropagation();

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '{{ $isEN ? "Saving..." : "Enregistrement..." }}';

        console.log('🔄 AJAX POST to:', form.action);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('📥 Response:', data);
            if (data.success) {
                console.log('✅ Success! Redirecting to step4...');
                setTimeout(() => {
                    window.location.href = '/teacher/bulletin-ng/{{ $config->id }}/step4';
                }, 100);
            } else {
                alert('{{ $isEN ? "Error" : "Erreur" }}: ' + (data.message || '{{ $isEN ? "An error occurred" : "Une erreur est survenue" }}'));
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            alert('{{ $isEN ? "Error sending form" : "Erreur lors de l\'envoi du formulaire" }}: ' + error);
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });
    console.log('✅ Form event listener attached successfully');
} else {
    console.error('❌ Form #subjectsForm not found in DOM');
}
</script>
@endpush

@endsection
