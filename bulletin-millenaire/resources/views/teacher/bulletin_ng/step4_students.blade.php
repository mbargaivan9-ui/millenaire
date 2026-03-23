@extends('layouts.app')

@section('title', 'Bulletin NG — Élèves')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bulletin_ng.css') }}">
@endpush

@php $currentStep = 4; $isEN = $config->langue === 'EN'; @endphp

@section('content')
<div class="bng-page">

    <div class="bng-page-header">
        <div class="bng-page-header-inner">
            <div class="bng-page-icon">👨‍🎓</div>
            <div>
                <h1 class="bng-page-title">{{ $isEN ? 'Step 4 — Students' : 'Étape 4 — Élèves' }}</h1>
                <p class="bng-page-subtitle">{{ $config->nom_classe }} | {{ $config->trimestre_label }}</p>
            </div>
        </div>
    </div>

    @include('teacher.bulletin_ng.partials.wizard_header')

    <div class="bng-card">
        <div class="bng-card-header">
            <div class="bng-card-header-icon">👨‍🎓</div>
            <div>
                <div class="bng-card-title">{{ $isEN ? 'Student Records' : 'Fiche des Élèves' }}</div>
                <div class="bng-card-subtitle" id="studentCountBadge">
                    {{ $students->count() }} {{ $isEN ? 'student(s) registered' : 'élève(s) enregistré(s)' }}
                </div>
            </div>
            <div class="bng-card-header-actions" style="margin-left: auto;">
                <button class="bng-btn bng-btn-primary" id="addStudentBtn">
                    ➕ {{ $isEN ? 'Add Student' : 'Ajouter un Élève' }}
                </button>
            </div>
        </div>

        <div class="bng-card-body">

            {{-- Liste des élèves --}}
            <div id="studentsList">
                @forelse($students as $idx => $student)
                    @include('teacher.bulletin_ng.partials.student_card', [
                        'student' => $student, 'idx' => $idx, 'isEN' => $isEN,
                    ])
                @empty
                    <div class="bng-empty-state" id="emptyState">
                        <div class="bng-empty-icon">👨‍🎓</div>
                        <div class="bng-empty-text">
                            {{ $isEN ? 'No students yet. Click "Add Student" to begin.' : 'Aucun élève. Cliquez sur "Ajouter un Élève" pour commencer.' }}
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="bng-form-actions" style="margin-top: 24px;">
                <a href="{{ route('teacher.bulletin_ng.step3', $config->id) }}" class="bng-btn bng-btn-secondary">
                    ← {{ $isEN ? 'Back' : 'Retour' }}
                </a>
                <a href="{{ route('teacher.bulletin_ng.step5', $config->id) }}"
                   class="bng-btn bng-btn-primary" id="nextBtn"
                   {{ $students->count() === 0 ? 'style=opacity:.5;pointer-events:none' : '' }}>
                    {{ $isEN ? 'Next: Grade Entry →' : 'Suivant : Saisie des Notes →' }}
                </a>
            </div>
        </div>
    </div>

</div>

{{-- Modal : Ajouter un élève --}}
<div id="studentModal" class="bng-modal hidden">
    <div class="bng-modal-overlay" id="modalOverlay"></div>
    <div class="bng-modal-content">
        <div class="bng-modal-header">
            <div class="bng-modal-title">
                ➕ {{ $isEN ? 'Add Student' : 'Ajouter un Élève' }}
            </div>
            <button class="bng-modal-close" id="modalClose">✕</button>
        </div>
        <div class="bng-modal-body">
            <div class="bng-form-grid">
                <div class="bng-form-field">
                    <label class="bng-label">{{ $isEN ? 'ID / Matricule *' : 'Matricule *' }}</label>
                    <input type="text" id="fMatricule" class="bng-input" placeholder="ex: s260245">
                </div>
                <div class="bng-form-field bng-full-width">
                    <label class="bng-label">{{ $isEN ? 'Full Name *' : 'Nom et Prénom *' }}</label>
                    <input type="text" id="fNom" class="bng-input" placeholder="{{ $isEN ? 'Last name, First name' : 'Nom Prénom' }}">
                </div>
                <div class="bng-form-field">
                    <label class="bng-label">{{ $isEN ? 'Date of Birth' : 'Date de Naissance' }}</label>
                    <input type="date" id="fDob" class="bng-input">
                </div>
                <div class="bng-form-field">
                    <label class="bng-label">{{ $isEN ? 'Place of Birth' : 'Lieu de Naissance' }}</label>
                    <input type="text" id="fLieu" class="bng-input" placeholder="{{ $isEN ? 'City/Town' : 'Ville' }}">
                </div>
                <div class="bng-form-field">
                    <label class="bng-label">{{ $isEN ? 'Gender *' : 'Sexe *' }}</label>
                    <select id="fSex" class="bng-select">
                        <option value="M">{{ $isEN ? 'Male' : 'Masculin' }}</option>
                        <option value="F">{{ $isEN ? 'Female' : 'Féminin' }}</option>
                    </select>
                </div>
            </div>
            <div id="modalError" class="bng-alert bng-alert-danger" style="display:none;"></div>
        </div>
        <div class="bng-modal-footer">
            <button class="bng-btn bng-btn-secondary" id="cancelModal">
                {{ $isEN ? 'Cancel' : 'Annuler' }}
            </button>
            <button class="bng-btn bng-btn-primary" id="saveStudentBtn">
                ✓ {{ $isEN ? 'Save Student' : 'Enregistrer l\'Élève' }}
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Store config data for safe JS access
window.__bulletinConfig = {
    configId: {{ $config->id }},
    isEN: {{ $isEN ? 'true' : 'false' }},
    studentCount: {{ $students->count() }}
};
</script>

<script>
// Ensure DOM is ready before initializing
function initStep4Modal() {
    const configId  = window.__bulletinConfig.configId;
    const isEN      = window.__bulletinConfig.isEN;
    const csrf      = document.querySelector('meta[name=csrf-token]').content;
    const modal     = document.getElementById('studentModal');
    const list      = document.getElementById('studentsList');
    const emptyState= document.getElementById('emptyState');
    let count       = window.__bulletinConfig.studentCount;

    // Verify elements exist
    if (!modal || !list) {
        console.error('❌ Modal or list element not found');
        return;
    }

    // Open / close modal using the 'hidden' class from bulletin_ng.css
    function openModal() {
        console.log('📂 Opening modal');
        modal.classList.remove('hidden');
        document.getElementById('fMatricule').focus();
    }
    
    function closeModal() {
        console.log('📁 Closing modal');
        modal.classList.add('hidden');
        clearModal();
    }
    function clearModal() {
        ['fMatricule','fNom','fDob','fLieu'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('fSex').value = 'M';
        document.getElementById('modalError').style.display = 'none';
    }

    // Add event listeners - with null checks
    const addStudentBtn = document.getElementById('addStudentBtn');
    if (addStudentBtn) addStudentBtn.addEventListener('click', openModal);
    
    const modalClose = document.getElementById('modalClose');
    if (modalClose) modalClose.addEventListener('click', closeModal);
    
    const cancelModal = document.getElementById('cancelModal');
    if (cancelModal) cancelModal.addEventListener('click', closeModal);
    
    const modalOverlay = document.getElementById('modalOverlay');
    if (modalOverlay) modalOverlay.addEventListener('click', closeModal);

    // Save student via AJAX
    const saveStudentBtn = document.getElementById('saveStudentBtn');
    if (saveStudentBtn) {
        saveStudentBtn.addEventListener('click', async function () {
            const matricule = document.getElementById('fMatricule').value.trim();
            const nom       = document.getElementById('fNom').value.trim();
            const dob       = document.getElementById('fDob').value;
            const lieu      = document.getElementById('fLieu').value.trim();
            const sex       = document.getElementById('fSex').value;

            if (!matricule || !nom) {
                const err = document.getElementById('modalError');
                err.textContent = isEN ? 'ID and Full Name are required.' : 'Matricule et Nom sont obligatoires.';
                err.style.display = 'block';
                return;
            }

            this.disabled = true;
            this.textContent = isEN ? 'Saving...' : 'Enregistrement...';

            try {
                const res = await fetch(`/teacher/bulletin-ng/${configId}/students`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ matricule, nom, date_naissance: dob, lieu_naissance: lieu, sexe: sex }),
                });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Erreur');

                count++;
                // Inject new card
                if (emptyState) emptyState.style.display = 'none';
                list.insertAdjacentHTML('beforeend', buildStudentCard(data.student, count - 1));
                document.getElementById('studentCountBadge').textContent =
                    count + ' ' + (isEN ? 'student(s) registered' : 'élève(s) enregistré(s)');
                document.getElementById('nextBtn').style.cssText = '';
                closeModal();
            } catch (e) {
                document.getElementById('modalError').textContent = e.message;
                document.getElementById('modalError').style.display = 'block';
            } finally {
                this.disabled = false;
                this.textContent = isEN ? '✓ Save Student' : "✓ Enregistrer l'Élève";
            }
        });
    }

    // Build card HTML
    function buildStudentCard(s, idx) {
        const dob = s.date_naissance ? new Date(s.date_naissance).toLocaleDateString('fr-FR') : '-';
        return `
        <div class="bng-student-card" data-id="${s.id}" id="sc-${s.id}">
            <div class="bng-student-card-header">
                <div class="bng-student-number">${idx + 1}</div>
                <div class="bng-student-info">
                    <div class="bng-student-name">${s.nom}</div>
                    <div class="bng-student-meta">${s.matricule}</div>
                </div>
                <div class="bng-student-meta2">
                    <span class="bng-badge bng-badge-${s.sexe === 'M' ? 'info' : 'warning'}">${s.sexe === 'M' ? (isEN ? 'Male' : 'Masculin') : (isEN ? 'Female' : 'Féminin')}</span>
                </div>
                <button class="bng-btn-icon bng-btn-icon-danger delete-student-btn" data-id="${s.id}">🗑</button>
            </div>
            <div class="bng-student-details">
                <span>${isEN ? 'DOB' : 'Naissance'}: ${dob}</span>
                <span>${isEN ? 'Place' : 'Lieu'}: ${s.lieu_naissance || '-'}</span>
            </div>
        </div>`;
    }

    // Delete student
    if (list) {
        list.addEventListener('click', async function (e) {
            const btn = e.target.closest('.delete-student-btn');
            if (!btn) return;
            const id = btn.dataset.id;
            if (!confirm(isEN ? 'Delete this student?' : 'Supprimer cet élève ?')) return;

            const res = await fetch(`/teacher/bulletin-ng/${configId}/students/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf },
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById(`sc-${id}`).remove();
                count--;
                document.getElementById('studentCountBadge').textContent =
                    count + ' ' + (isEN ? 'student(s) registered' : 'élève(s) enregistré(s)');
                if (count === 0) {
                    document.getElementById('nextBtn').style.opacity = '.5';
                    document.getElementById('nextBtn').style.pointerEvents = 'none';
                    if (emptyState) emptyState.style.display = 'block';
                }
            }
        });
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStep4Modal);
} else {
    initStep4Modal();
}
</script>
@endpush
