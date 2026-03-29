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
// Simple, direct event handlers - no complex initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM Loaded - Setting up Step 4 Modal handlers');
    
    // Get elements
    const modal = document.getElementById('studentModal');
    const addBtn = document.getElementById('addStudentBtn');
    const cancelBtn = document.getElementById('cancelModal');
    const modalClose = document.getElementById('modalClose');
    const modalOverlay = document.getElementById('modalOverlay');
    const saveBtn = document.getElementById('saveStudentBtn');
    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
    
    console.log('Elements found:', { 
        modal: !!modal, 
        addBtn: !!addBtn, 
        cancelBtn: !!cancelBtn,
        modalClose: !!modalClose,
        saveBtn: !!saveBtn
    });
    
    // Helper function to open modal
    function openModal() {
        console.log('📂 Opening modal...');
        if (modal) {
            modal.classList.remove('hidden');
            console.log('  Classes after remove:', modal.className);
            // Focus first input
            setTimeout(() => {
                const input = document.getElementById('fMatricule');
                if (input) input.focus();
            }, 50);
        }
    }
    
    // Helper function to close modal
    function closeModal() {
        console.log('📁 Closing modal...');
        if (modal) {
            modal.classList.add('hidden');
            // Clear form
            ['fMatricule', 'fNom', 'fDob', 'fLieu'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            const sex = document.getElementById('fSex');
            if (sex) sex.value = 'M';
            const error = document.getElementById('modalError');
            if (error) error.style.display = 'none';
        }
    }
    
    // Attach click handlers
    if (addBtn) {
        addBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('🔘 Add button clicked');
            openModal();
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal();
        });
    }
    
    if (modalClose) {
        modalClose.addEventListener('click', function(e) {
            e.preventDefault();
            closeModal();
        });
    }
    
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });
    }
    
    // Save student handler
    if (saveBtn) {
        saveBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            console.log('💾 Save button clicked');
            
            const matricule = (document.getElementById('fMatricule')?.value || '').trim();
            const nom = (document.getElementById('fNom')?.value || '').trim();
            const dob = document.getElementById('fDob')?.value || '';
            const lieu = (document.getElementById('fLieu')?.value || '').trim();
            const sex = document.getElementById('fSex')?.value || 'M';
            
            if (!matricule || !nom) {
                const errorEl = document.getElementById('modalError');
                if (errorEl) {
                    errorEl.textContent = '{{ $isEN ? "Matricule and Name are required" : "Matricule et Nom sont obligatoires" }}';
                    errorEl.style.display = 'block';
                }
                return;
            }
            
            saveBtn.disabled = true;
            saveBtn.textContent = '{{ $isEN ? "Saving..." : "Enregistrement..." }}';
            
            try {
                const response = await fetch('/teacher/bulletin-ng/{{ $config->id }}/students', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        matricule,
                        nom,
                        date_naissance: dob,
                        lieu_naissance: lieu,
                        sexe: sex
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to save student');
                }
                
                // Add student to list
                const list = document.getElementById('studentsList');
                const emptyState = document.getElementById('emptyState');
                
                if (emptyState) emptyState.style.display = 'none';
                
                const html = `
                    <div class="bng-student-card" data-id="${data.student.id}" id="sc-${data.student.id}">
                        <div class="bng-student-card-header">
                            <div class="bng-student-number">1</div>
                            <div class="bng-student-info">
                                <div class="bng-student-name">${nom}</div>
                                <div class="bng-student-meta">${matricule}</div>
                            </div>
                            <div class="bng-student-meta2">
                                <span class="bng-badge bng-badge-${sex === 'M' ? 'info' : 'warning'}">
                                    ${sex === 'M' ? '{{ $isEN ? "Male" : "Masculin" }}' : '{{ $isEN ? "Female" : "Féminin" }}'}
                                </span>
                            </div>
                            <button class="bng-btn-icon bng-btn-icon-danger delete-student-btn" data-id="${data.student.id}">🗑</button>
                        </div>
                        <div class="bng-student-details">
                            <span>${dob ? dob : '-'}</span>
                            <span>${lieu || '-'}</span>
                        </div>
                    </div>`;
                
                if (list) list.insertAdjacentHTML('beforeend', html);
                
                // Update count
                const countBadge = document.getElementById('studentCountBadge');
                if (countBadge) {
                    const num = list?.querySelectorAll('.bng-student-card').length || 1;
                    countBadge.textContent = num + ' {{ $isEN ? "student(s) registered" : "élève(s) enregistré(s)" }}';
                }
                
                // Enable next button
                const nextBtn = document.getElementById('nextBtn');
                if (nextBtn) {
                    nextBtn.style.opacity = '1';
                    nextBtn.style.pointerEvents = 'auto';
                }
                
                closeModal();
                
            } catch (err) {
                console.error('Error:', err);
                const errorEl = document.getElementById('modalError');
                if (errorEl) {
                    errorEl.textContent = err.message;
                    errorEl.style.display = 'block';
                }
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = '{{ $isEN ? "✓ Save Student" : "✓ Enregistrer l\'Élève" }}';
            }
        });
    }
    
    // Delete student handler
    document.getElementById('studentsList')?.addEventListener('click', async function(e) {
        const deleteBtn = e.target.closest('.delete-student-btn');
        if (!deleteBtn) return;
        
        const studentId = deleteBtn.dataset.id;
        if (!confirm('{{ $isEN ? "Delete this student?" : "Supprimer cet élève ?" }}')) return;
        
        try {
            const response = await fetch('/teacher/bulletin-ng/{{ $config->id }}/students/' + studentId, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf }
            });
            
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message);
            
            // Remove from UI
            document.getElementById(`sc-${studentId}`)?.remove();
            
            // Update count
            const list = document.getElementById('studentsList');
            const count = list?.querySelectorAll('.bng-student-card').length || 0;
            const countBadge = document.getElementById('studentCountBadge');
            if (countBadge) {
                countBadge.textContent = count + ' {{ $isEN ? "student(s) registered" : "élève(s) enregistré(s)" }}';
            }
            
            // Show empty state if none left
            if (count === 0) {
                const emptyState = document.getElementById('emptyState');
                if (emptyState) emptyState.style.display = 'block';
                const nextBtn = document.getElementById('nextBtn');
                if (nextBtn) {
                    nextBtn.style.opacity = '0.5';
                    nextBtn.style.pointerEvents = 'none';
                }
            }
        } catch (err) {
            console.error('Error:', err);
            alert(err.message);
        }
    });
    
    console.log('✅ All handlers attached');
});
</script>
@endpush
