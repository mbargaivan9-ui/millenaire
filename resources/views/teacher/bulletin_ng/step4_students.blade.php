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
// Configuration globale
window.Step4Modal = {
    config: {
        configId: {{ $config->id }},
        isEN: {{ $isEN ? 'true' : 'false' }},
        studentCount: {{ $students->count() }},
        csrf: ''
    },
    initialized: false,
    
    init: function() {
        if (this.initialized) return;
        
        try {
            // Get CSRF token
            const csrfMeta = document.querySelector('meta[name=csrf-token]');
            if (!csrfMeta) {
                throw new Error('CSRF token not found');
            }
            this.config.csrf = csrfMeta.content;
            
            // Get key elements
            const modal = document.getElementById('studentModal');
            const addBtn = document.getElementById('addStudentBtn');
            const list = document.getElementById('studentsList');
            
            if (!modal) throw new Error('Modal element not found');
            if (!addBtn) throw new Error('Add button not found');
            if (!list) throw new Error('Students list not found');
            
            // Setup event listeners
            this.setupListeners();
            this.initialized = true;
            console.log('✅ Step 4 Modal initialized successfully');
            
        } catch (err) {
            console.error('❌ Error initializing Step 4 Modal:', err.message);
            console.error('Stack:', err.stack);
        }
    },
    
    setupListeners: function() {
        const self = this;
        const modal = document.getElementById('studentModal');
        const addBtn = document.getElementById('addStudentBtn');
        const modalClose = document.getElementById('modalClose');
        const cancelBtn = document.getElementById('cancelModal');
        const modalOverlay = document.getElementById('modalOverlay');
        const saveBtn = document.getElementById('saveStudentBtn');
        const list = document.getElementById('studentsList');
        
        // Open modal
        if (addBtn) {
            addBtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.openModal();
            });
        }
        
        // Close modal
        if (modalClose) {
            modalClose.addEventListener('click', function(e) {
                e.preventDefault();
                self.closeModal();
            });
        }
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.closeModal();
            });
        }
        
        if (modalOverlay) {
            modalOverlay.addEventListener('click', function(e) {
                if (e.target === modalOverlay) {
                    self.closeModal();
                }
            });
        }
        
        // Save student
        if (saveBtn) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.saveStudent(this);
            });
        }
        
        // Delete student
        if (list) {
            list.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.delete-student-btn');
                if (deleteBtn) {
                    e.preventDefault();
                    self.deleteStudent(deleteBtn);
                }
            });
        }
    },
    
    openModal: function() {
        const modal = document.getElementById('studentModal');
        if (modal) {
            console.log('📂 Opening modal');
            modal.classList.remove('hidden');
            // Give a moment for the DOM to update, then focus
            setTimeout(() => {
                const input = document.getElementById('fMatricule');
                if (input) input.focus();
            }, 50);
        }
    },
    
    closeModal: function() {
        const modal = document.getElementById('studentModal');
        if (modal) {
            console.log('📁 Closing modal');
            modal.classList.add('hidden');
            this.clearForm();
        }
    },
    
    clearForm: function() {
        const fields = ['fMatricule', 'fNom', 'fDob', 'fLieu'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        
        const sex = document.getElementById('fSex');
        if (sex) sex.value = 'M';
        
        const error = document.getElementById('modalError');
        if (error) error.style.display = 'none';
    },
    
    saveStudent: async function(btn) {
        try {
            const matricule = this.getFieldValue('fMatricule').trim();
            const nom = this.getFieldValue('fNom').trim();
            const dob = this.getFieldValue('fDob');
            const lieu = this.getFieldValue('fLieu').trim();
            const sex = this.getFieldValue('fSex');
            
            if (!matricule || !nom) {
                this.showError(
                    this.config.isEN ? 'ID and Full Name are required.' : 'Matricule et Nom sont obligatoires.'
                );
                return;
            }
            
            // Disable button during save
            btn.disabled = true;
            const originalText = btn.textContent;
            btn.textContent = this.config.isEN ? 'Saving...' : 'Enregistrement...';
            
            // Send request
            const response = await fetch(
                `/teacher/bulletin-ng/${this.config.configId}/students`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrf
                    },
                    body: JSON.stringify({
                        matricule,
                        nom,
                        date_naissance: dob,
                        lieu_naissance: lieu,
                        sexe: sex
                    })
                }
            );
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to save student');
            }
            
            // Success - update UI
            this.addStudentToList(data.student);
            this.closeModal();
            
        } catch (err) {
            console.error('❌ Error saving student:', err);
            this.showError(err.message || 'An error occurred');
        } finally {
            btn.disabled = false;
            btn.textContent = this.config.isEN ? '✓ Save Student' : "✓ Enregistrer l'Élève";
        }
    },
    
    addStudentToList: function(student) {
        const list = document.getElementById('studentsList');
        const emptyState = document.getElementById('emptyState');
        const countBadge = document.getElementById('studentCountBadge');
        const nextBtn = document.getElementById('nextBtn');
        
        if (!list) return;
        
        // Hide empty state
        if (emptyState) emptyState.style.display = 'none';
        
        // Add card
        const count = this.config.studentCount++;
        const html = this.buildStudentCard(student, count);
        list.insertAdjacentHTML('beforeend', html);
        
        // Update count
        if (countBadge) {
            countBadge.textContent = this.config.studentCount + ' ' + 
                (this.config.isEN ? 'student(s) registered' : 'élève(s) enregistré(s)');
        }
        
        // Enable next button
        if (nextBtn) {
            nextBtn.style.opacity = '1';
            nextBtn.style.pointerEvents = 'auto';
        }
        
        console.log('✅ Student added. Total:', this.config.studentCount);
    },
    
    buildStudentCard: function(student, idx) {
        const dob = student.date_naissance 
            ? new Date(student.date_naissance).toLocaleDateString('fr-FR') 
            : '-';
        const genderLabel = student.sexe === 'M' 
            ? (this.config.isEN ? 'Male' : 'Masculin')
            : (this.config.isEN ? 'Female' : 'Féminin');
        const genderClass = student.sexe === 'M' ? 'info' : 'warning';
        
        return `
            <div class="bng-student-card" data-id="${student.id}" id="sc-${student.id}">
                <div class="bng-student-card-header">
                    <div class="bng-student-number">${idx + 1}</div>
                    <div class="bng-student-info">
                        <div class="bng-student-name">${this.escapeHtml(student.nom)}</div>
                        <div class="bng-student-meta">${this.escapeHtml(student.matricule)}</div>
                    </div>
                    <div class="bng-student-meta2">
                        <span class="bng-badge bng-badge-${genderClass}">${genderLabel}</span>
                    </div>
                    <button class="bng-btn-icon bng-btn-icon-danger delete-student-btn" data-id="${student.id}">🗑</button>
                </div>
                <div class="bng-student-details">
                    <span>${this.config.isEN ? 'DOB' : 'Naissance'}: ${dob}</span>
                    <span>${this.config.isEN ? 'Place' : 'Lieu'}: ${this.escapeHtml(student.lieu_naissance || '-')}</span>
                </div>
            </div>`;
    },
    
    deleteStudent: async function(btn) {
        const studentId = btn.dataset.id;
        const card = document.getElementById(`sc-${studentId}`);
        
        if (!confirm(this.config.isEN ? 'Delete this student?' : 'Supprimer cet élève ?')) {
            return;
        }
        
        try {
            const response = await fetch(
                `/teacher/bulletin-ng/${this.config.configId}/students/${studentId}`,
                {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.config.csrf
                    }
                }
            );
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to delete student');
            }
            
            // Remove from UI
            if (card) card.remove();
            
            this.config.studentCount--;
            
            // Update count
            const countBadge = document.getElementById('studentCountBadge');
            if (countBadge) {
                countBadge.textContent = this.config.studentCount + ' ' + 
                    (this.config.isEN ? 'student(s) registered' : 'élève(s) enregistré(s)');
            }
            
            // Show empty state if no students
            if (this.config.studentCount === 0) {
                const emptyState = document.getElementById('emptyState');
                if (emptyState) emptyState.style.display = 'block';
                
                const nextBtn = document.getElementById('nextBtn');
                if (nextBtn) {
                    nextBtn.style.opacity = '0.5';
                    nextBtn.style.pointerEvents = 'none';
                }
            }
            
            console.log('✅ Student deleted. Total:', this.config.studentCount);
            
        } catch (err) {
            console.error('❌ Error deleting student:', err);
            alert(this.config.isEN ? 'Failed to delete student' : 'Échec de la suppression');
        }
    },
    
    getFieldValue: function(id) {
        const el = document.getElementById(id);
        return el ? el.value : '';
    },
    
    showError: function(message) {
        const error = document.getElementById('modalError');
        if (error) {
            error.textContent = message;
            error.style.display = 'block';
        }
    },
    
    escapeHtml: function(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 DOM Content Loaded - Initializing Step 4 Modal');
    window.Step4Modal.init();
});

// Also try immediate initialization in case DOM is already loaded
if (document.readyState === 'interactive' || document.readyState === 'complete') {
    console.log('⚡ DOM already loaded - Initializing Step 4 Modal immediately');
    // Use setTimeout to ensure all DOM elements are ready
    setTimeout(() => {
        window.Step4Modal.init();
    }, 0);
}
</script>
@endpush
