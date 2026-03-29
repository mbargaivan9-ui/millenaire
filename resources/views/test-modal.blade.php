@extends('layouts.app')

@section('title', 'Test Modal Step 4')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bulletin_ng.css') }}">
@endpush

@section('content')
<div class="bng-page">
    <div class="bng-page-header">
        <div class="bng-page-header-inner">
            <div class="bng-page-icon">🧪</div>
            <div>
                <h1 class="bng-page-title">Test Modal Step 4</h1>
                <p class="bng-page-subtitle">Diagnostic complet du modal d'ajout d'élève</p>
            </div>
        </div>
    </div>

    <div class="bng-card">
        <div class="bng-card-header">
            <div class="bng-card-header-icon">✅</div>
            <div>
                <div class="bng-card-title">Éléments HTML</div>
                <div class="bng-card-subtitle" id="htmlStatus">Vérification...</div>
            </div>
        </div>
        <div class="bng-card-body">
            <div id="htmlChecks">Vérification...</div>
        </div>
    </div>

    <div class="bng-card" style="margin-top: 20px;">
        <div class="bng-card-header">
            <div class="bng-card-header-icon">🎨</div>
            <div>
                <div class="bng-card-title">CSS</div>
                <div class="bng-card-subtitle" id="cssStatus">Vérification...</div>
            </div>
        </div>
        <div class="bng-card-body">
            <div id="cssChecks">Vérification...</div>
        </div>
    </div>

    <div class="bng-card" style="margin-top: 20px;">
        <div class="bng-card-header">
            <div class="bng-card-header-icon">⚙️</div>
            <div>
                <div class="bng-card-title">JavaScript Events</div>
                <div class="bng-card-subtitle" id="jsStatus">Vérification...</div>
            </div>
        </div>
        <div class="bng-card-body">
            <div id="jsChecks">Vérification...</div>
        </div>
    </div>

    <div class="bng-card" style="margin-top: 20px;">
        <div class="bng-card-header">
            <div class="bng-card-header-icon">🔨</div>
            <div>
                <div class="bng-card-title">Test Interactif</div>
                <div class="bng-card-subtitle">Vérifiez que tout fonctionne</div>
            </div>
        </div>
        <div class="bng-card-body">
            <button class="bng-btn bng-btn-primary" id="testAddBtn">
                ➕ Ajouter un Élève
            </button>
            
            <div id="studentTestList" style="margin-top: 20px;">
                <!-- Students will be added here -->
            </div>
        </div>
    </div>
</div>

{{-- Copier le modal du step 4 --}}
<div id="studentModal" class="bng-modal hidden">
    <div class="bng-modal-overlay" id="modalOverlay"></div>
    <div class="bng-modal-content">
        <div class="bng-modal-header">
            <div class="bng-modal-title">
                ➕ Ajouter un Élève
            </div>
            <button class="bng-modal-close" id="modalClose">✕</button>
        </div>
        <div class="bng-modal-body">
            <div class="bng-form-grid">
                <div class="bng-form-field">
                    <label class="bng-label">Matricule *</label>
                    <input type="text" id="fMatricule" class="bng-input" placeholder="ex: s260245">
                </div>
                <div class="bng-form-field bng-full-width">
                    <label class="bng-label">Nom et Prénom *</label>
                    <input type="text" id="fNom" class="bng-input" placeholder="Nom Prénom">
                </div>
                <div class="bng-form-field">
                    <label class="bng-label">Date de Naissance</label>
                    <input type="date" id="fDob" class="bng-input">
                </div>
                <div class="bng-form-field">
                    <label class="bng-label">Lieu de Naissance</label>
                    <input type="text" id="fLieu" class="bng-input" placeholder="Ville">
                </div>
                <div class="bng-form-field">
                    <label class="bng-label">Sexe *</label>
                    <select id="fSex" class="bng-select">
                        <option value="M">Masculin</option>
                        <option value="F">Féminin</option>
                    </select>
                </div>
            </div>
            <div id="modalError" class="bng-alert bng-alert-danger" style="display:none;"></div>
        </div>
        <div class="bng-modal-footer">
            <button class="bng-btn bng-btn-secondary" id="cancelModal">
                Annuler
            </button>
            <button class="bng-btn bng-btn-primary" id="saveStudentBtn">
                ✓ Enregistrer l'Élève
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Diagnostic Tests
window.testResults = {
    html: [],
    css: [],
    js: []
};

// Test 1: HTML Elements
document.addEventListener('DOMContentLoaded', function() {
    console.log('🧪 Starting diagnostic tests...');
    
    const elements = [
        { id: 'studentModal', name: 'Modal Container' },
        { id: 'modalOverlay', name: 'Modal Overlay' },
        { id: 'modalClose', name: 'Modal Close Button' },
        { id: 'addStudentBtn', name: 'Add Student Button' },
        { id: 'testAddBtn', name: 'Test Add Button' },
        { id: 'cancelModal', name: 'Cancel Button' },
        { id: 'saveStudentBtn', name: 'Save Button' },
        { id: 'fMatricule', name: 'Matricule Input' },
        { id: 'fNom', name: 'Nom Input' },
        { id: 'fDob', name: 'DOB Input' },
        { id: 'fLieu', name: 'Lieu Input' },
        { id: 'fSex', name: 'Sex Select' },
        { id: 'modalError', name: 'Error Display' }
    ];
    
    const htmlChecks = document.getElementById('htmlChecks');
    let htmlPassCount = 0;
    
    elements.forEach(el => {
        const elem = document.getElementById(el.id);
        const exists = !!elem;
        if (exists) htmlPassCount++;
        
        const resultEl = document.createElement('div');
        resultEl.className = 'bng-test-item ' + (exists ? 'bng-test-pass' : 'bng-test-fail');
        resultEl.textContent = (exists ? '✅' : '❌') + ' ' + el.name + ' (#' + el.id + ')';
        htmlChecks.appendChild(resultEl);
    });
    
    document.getElementById('htmlStatus').textContent = htmlPassCount + '/' + elements.length + ' éléments trouvés';
    
    // Test 2: CSS
    const modal = document.getElementById('studentModal');
    const cssChecks = document.getElementById('cssChecks');
    
    const computedStyle = window.getComputedStyle(modal);
    const checks = [
        {
            name: 'Modal has hidden class',
            pass: modal.classList.contains('hidden')
        },
        {
            name: 'Modal visibility is hidden',
            pass: computedStyle.visibility === 'hidden'
        },
        {
            name: 'Modal opacity is 0',
            pass: computedStyle.opacity === '0'
        },
        {
            name: 'Modal position is fixed',
            pass: computedStyle.position === 'fixed'
        },
        {
            name: 'Modal z-index is set',
            pass: computedStyle.zIndex > 0
        }
    ];
    
    let cssPassCount = 0;
    checks.forEach(check => {
        if (check.pass) cssPassCount++;
        const resultEl = document.createElement('div');
        resultEl.className = 'bng-test-item ' + (check.pass ? 'bng-test-pass' : 'bng-test-fail');
        resultEl.textContent = (check.pass ? '✅' : '❌') + ' ' + check.name;
        cssChecks.appendChild(resultEl);
    });
    
    document.getElementById('cssStatus').textContent = cssPassCount + '/' + checks.length + ' CSS propriétés correctes';
    
    // Test 3: JavaScript Event Handlers
    const jsChecks = document.getElementById('jsChecks');
    
    // Helper function to check if modal works
    function openModal() {
        modal.classList.remove('hidden');
    }
    
    function closeModal() {
        modal.classList.add('hidden');
        // Clear form
        ['fMatricule', 'fNom', 'fDob', 'fLieu'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    }
    
    const jsTests = [
        {
            name: 'Modal can be opened',
            test: () => {
                openModal();
                return window.getComputedStyle(modal).visibility === 'visible';
            }
        },
        {
            name: 'Modal can be closed',
            test: () => {
                closeModal();
                return window.getComputedStyle(modal).visibility === 'hidden';
            }
        },
        {
            name: 'Click handler can be attached',
            test: () => {
                const btn = document.getElementById('testAddBtn');
                return !!btn;
            }
        }
    ];
    
    let jsPassCount = 0;
    jsTests.forEach(test => {
        try {
            const pass = test.test();
            if (pass) jsPassCount++;
            const resultEl = document.createElement('div');
            resultEl.className = 'bng-test-item ' + (pass ? 'bng-test-pass' : 'bng-test-fail');
            resultEl.textContent = (pass ? '✅' : '❌') + ' ' + test.name;
            jsChecks.appendChild(resultEl);
        } catch (err) {
            const resultEl = document.createElement('div');
            resultEl.className = 'bng-test-item bng-test-fail';
            resultEl.textContent = '❌ ' + test.name + ' - ' + err.message;
            jsChecks.appendChild(resultEl);
        }
    });
    
    document.getElementById('jsStatus').textContent = jsPassCount + '/' + jsTests.length + ' tests JavaScript réussis';
    
    // Setup interactive test
    const testBtn = document.getElementById('testAddBtn');
    const cancelBtn = document.getElementById('cancelModal');
    const modalCloseBtn = document.getElementById('modalClose');
    const saveBtn = document.getElementById('saveStudentBtn');
    const modalOverlay = document.getElementById('modalOverlay');
    
    if (testBtn) {
        testBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal();
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    }
    
    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    }
    
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });
    }
    
    if (saveBtn) {
        saveBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const matricule = document.getElementById('fMatricule').value.trim();
            const nom = document.getElementById('fNom').value.trim();
            const dob = document.getElementById('fDob').value;
            const lieu = document.getElementById('fLieu').value.trim();
            const sex = document.getElementById('fSex').value;
            
            if (!matricule || !nom) {
                const errorEl = document.getElementById('modalError');
                errorEl.textContent = 'Matricule et Nom sont obligatoires';
                errorEl.style.display = 'block';
                return;
            }
            
            // Simulate adding student
            const list = document.getElementById('studentTestList');
            const html = `
                <div class="bng-student-card" style="margin-top: 10px;">
                    <div class="bng-student-card-header">
                        <div style="flex: 1;">
                            <div style="font-weight: bold;">${nom}</div>
                            <div style="font-size: 0.85em; color: #666;">${matricule}</div>
                        </div>
                        <div style="padding: 4px 8px; background: #dbeafe; border-radius: 4px; font-size: 0.8em;">
                            ${sex === 'M' ? 'Masculin' : 'Féminin'}
                        </div>
                    </div>
                </div>`;
            
            list.insertAdjacentHTML('beforeend', html);
            closeModal();
        });
    }
    
    console.log('✅ Diagnostic tests completed');
});
</script>

<style>
.bng-test-item {
    padding: 8px 12px;
    margin: 4px 0;
    border-left: 4px solid #999;
    background: #f5f5f5;
    border-radius: 4px;
    font-size: 0.9em;
}

.bng-test-pass {
    border-left-color: #10b981;
    color: #10b981;
}

.bng-test-fail {
    border-left-color: #ef4444;
    color: #ef4444;
}
</style>

@endpush

@endsection
