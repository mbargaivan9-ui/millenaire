@extends('layouts.app')

@section('title', 'Éditeur de Modèle - ' . $template->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $template->name }}</h1>
                    <p class="text-sm text-gray-500 mt-1">Classe: {{ $template->classroom->name }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="saveDraft()" class="btn-secondary">
                        <span class="icon">💾</span> Brouillon
                    </button>
                    <button onclick="validateTemplate()" class="btn-primary">
                        <span class="icon">✓</span> Valider
                    </button>
                    <button onclick="publishTemplate()" class="btn-success" id="publishBtn" disabled>
                        <span class="icon">→</span> Publier
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Editor Grid -->
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="grid grid-cols-12 gap-6">
            <!-- Left Sidebar - Properties -->
            <div class="col-span-3 space-y-6">
                <!-- Template Status -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-900 mb-4">État</h3>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Statut:</span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $template->is_validated ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $template->is_validated ? 'Publié' : 'Brouillon' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Confiance OCR:</span>
                            <span class="font-mono text-gray-900">{{ $template->ocr_confidence ?? 'N/A' }}%</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Créé par:</span>
                            <span class="text-gray-900">{{ $template->creator->name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Subjects Panel -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-900 mb-4">Matières</h3>
                    <div id="subjectsList" class="space-y-2 mb-4" data-subjects="{{ json_encode($subjectAssignments) }}">
                        @forelse($subjectAssignments as $assignment)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded cursor-move subject-item" 
                             data-subject-id="{{ $assignment->subject_id }}">
                            <div>
                                <p class="font-sm text-gray-900">{{ $assignment->subject->name }}</p>
                                <p class="text-xs text-gray-500">Coef: {{ $assignment->coefficient }}</p>
                            </div>
                            <button onclick="removeSubject({{ $assignment->subject_id }})" class="text-red-500 hover:text-red-700">
                                ✕
                            </button>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 italic">Aucune matière assignée</p>
                        @endforelse
                    </div>

                    <button onclick="showAddSubjectModal()" class="w-full btn-outline">
                        + Ajouter matière
                    </button>
                </div>

                <!-- Template Settings -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-900 mb-4">Configuration</h3>
                    <form id="settingsForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du modèle</label>
                            <input type="text" id="templateName" value="{{ $template->name }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Échelle d'appréciation</label>
                            <select id="appreciationScale" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="standard">Standard (5 tiers)</option>
                                <option value="numeric">Numérique (0-20)</option>
                                <option value="custom">Personnalisée</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Méthode de calcul</label>
                            <select id="calculationMethod" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="weighted">Moyenne pondérée</option>
                                <option value="simple">Moyenne simple</option>
                                <option value="custom">Personnalisée</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Center - Canvas -->
            <div class="col-span-6">
                <div class="bg-white rounded-lg shadow sticky top-20">
                    <!-- Toolbar -->
                    <div class="border-b border-gray-200 p-4 flex items-center gap-2">
                        <button onclick="zoomOut()" class="btn-sm btn-secondary">−</button>
                        <span id="zoomLevel" class="text-sm text-gray-600 min-w-12">100%</span>
                        <button onclick="zoomIn()" class="btn-sm btn-secondary">+</button>
                        <div class="flex-1"></div>
                        <button onclick="toggleGrid()" class="btn-sm btn-secondary" title="Grille">⊞</button>
                        <button onclick="downloadPreview()" class="btn-sm btn-secondary" title="Télécharger">⬇</button>
                    </div>

                    <!-- Canvas -->
                    <div id="canvas" class="overflow-auto bg-gray-50" style="height: 600px;">
                        <div id="previewContainer" class="inline-block bg-white p-8 m-4 shadow-md" 
                             style="width: 210mm; transform: scale(1);">
                            <div id="templatePreview" class="text-gray-400 text-center py-32">
                                Aperçu du modèle
                            </div>
                        </div>
                    </div>

                    <!-- Canvas Status -->
                    <div class="border-t border-gray-200 p-3 bg-gray-50 text-xs text-gray-600">
                        <span id="canvasStatus">Prêt</span>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar - Elements -->
            <div class="col-span-3 space-y-6">
                <!-- Template Structure Inspector -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-900 mb-4">Structure</h3>
                    <div id="structureTree" class="space-y-2 text-sm font-mono">
                        <details class="cursor-pointer">
                            <summary class="text-gray-900 hover:text-blue-600">📄 En-tête</summary>
                            <div class="ml-4 mt-2 text-gray-600">
                                <p>• Nom école</p>
                                <p>• Année scolaire</p>
                                <p>• Période</p>
                            </div>
                        </details>
                        <details class="cursor-pointer">
                            <summary class="text-gray-900 hover:text-blue-600">👤 Infos étudiant</summary>
                            <div class="ml-4 mt-2 text-gray-600">
                                <p>• Nom complet</p>
                                <p>• Matricule</p>
                                <p>• Classe</p>
                            </div>
                        </details>
                        <details class="cursor-pointer">
                            <summary class="text-gray-900 hover:text-blue-600">📚 Matières & Notes</summary>
                            <div class="ml-4 mt-2 text-gray-600">
                                <p>• Nom matière</p>
                                <p>• Note classe</p>
                                <p>• Note composition</p>
                                <p>• Moyenne</p>
                            </div>
                        </details>
                        <details class="cursor-pointer">
                            <summary class="text-gray-900 hover:text-blue-600">🎯 Calculs</summary>
                            <div class="ml-4 mt-2 text-gray-600">
                                <p>• Moyennes par matière</p>
                                <p>• Moyenne générale</p>
                                <p>• Rang de classe</p>
                                <p>• Appréciation</p>
                            </div>
                        </details>
                        <details class="cursor-pointer">
                            <summary class="text-gray-900 hover:text-blue-600">✍️ Signatures</summary>
                            <div class="ml-4 mt-2 text-gray-600">
                                <p>• Parent</p>
                                <p>• Professeur principal</p>
                                <p>• Directeur</p>
                            </div>
                        </details>
                    </div>
                </div>

                <!-- Validation Messages -->
                <div id="validationPanel" class="bg-white rounded-lg shadow p-4 hidden">
                    <h3 class="font-semibold text-gray-900 mb-4">Validation</h3>
                    <div id="validationMessages" class="space-y-2 text-sm">
                        <!-- Messages dynamiques -->
                    </div>
                </div>

                <!-- Help -->
                <div class="bg-blue-50 rounded-lg border border-blue-200 p-4">
                    <h4 class="font-semibold text-blue-900 mb-2">Conseils</h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>✓ Glissez les matières pour les réorganiser</li>
                        <li>✓ Validez avant de publier</li>
                        <li>✓ Les coefficients affectent la moyenne globale</li>
                        <li>✓ Minimum 2 matières requises</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Subject Modal -->
<div id="addSubjectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Ajouter une matière</h2>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Matière</label>
                <select id="subjectSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">-- Sélectionner --</option>
                    @foreach($classroomSubjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Enseignant</label>
                <select id="teacherSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">-- Sélectionner --</option>
                    @foreach($template->classroom->teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Coefficient</label>
                <input type="number" id="coefficientInput" value="1" min="0.5" max="10" step="0.5"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button onclick="closeAddSubjectModal()" class="flex-1 btn-outline">Annuler</button>
            <button onclick="addSubject()" class="flex-1 btn-primary">Ajouter</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentTemplate = @json($template);
    let templateStructure = @json($templateStructure);
    let zoom = 100;

    // Save draft
    async function saveDraft() {
        try {
            setCanvasStatus('Sauvegarde en cours...');
            
            const response = await fetch(`/prof-principal/templates/{{ $template->id }}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    template_json: JSON.stringify(templateStructure),
                    name: document.getElementById('templateName').value,
                    html_template: generateHTMLTemplate(),
                }),
            });

            if (response.ok) {
                showNotification('Brouillon sauvegardé avec succès', 'success');
                setCanvasStatus('Sauvegardé');
            } else {
                const error = await response.json();
                showNotification(error.message, 'error');
            }
        } catch (error) {
            showNotification('Erreur: ' + error.message, 'error');
        }
    }

    // Validate template
    async function validateTemplate() {
        try {
            setCanvasStatus('Validation en cours...');
            
            const response = await fetch(`/prof-principal/templates/{{ $template->id }}/validate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    template_json: JSON.stringify(templateStructure),
                }),
            });

            const data = await response.json();

            if (response.ok) {
                showValidationPanel(data.warnings || [], 'success');
                document.getElementById('publishBtn').disabled = false;
                showNotification('Modèle validé avec succès!', 'success');
                setCanvasStatus('Validé ✓');
            } else {
                showValidationPanel(data.errors || [], 'error');
                showNotification(data.message, 'error');
            }
        } catch (error) {
            showNotification('Erreur: ' + error.message, 'error');
        }
    }

    // Publish template
    async function publishTemplate() {
        if (!confirm('Êtes-vous sûr? La génération des bulletins commencera immédiatement.')) {
            return;
        }

        try {
            setCanvasStatus('Publication en cours...');
            
            const response = await fetch(`/prof-principal/templates/{{ $template->id }}/publish`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            const data = await response.json();

            if (response.ok) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                showNotification(data.message, 'error');
            }
        } catch (error) {
            showNotification('Erreur: ' + error.message, 'error');
        }
    }

    // Add subject
    async function addSubject() {
        const subjectId = document.getElementById('subjectSelect').value;
        const teacherId = document.getElementById('teacherSelect').value;
        const coefficient = document.getElementById('coefficientInput').value;

        if (!subjectId || !teacherId) {
            showNotification('Veuillez remplir tous les champs', 'error');
            return;
        }

        try {
            const response = await fetch(`/prof-principal/templates/{{ $template->id }}/assign-subjects`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    assignments: [{
                        subject_id: parseInt(subjectId),
                        teacher_id: parseInt(teacherId),
                        coefficient: parseFloat(coefficient),
                    }],
                }),
            });

            if (response.ok) {
                showNotification('Matière ajoutée', 'success');
                closeAddSubjectModal();
                location.reload();
            } else {
                const error = await response.json();
                showNotification(error.message, 'error');
            }
        } catch (error) {
            showNotification('Erreur: ' + error.message, 'error');
        }
    }

    // Remove subject
    async function removeSubject(subjectId) {
        // Implementation for removing subject
    }

    // Zoom controls
    function zoomIn() {
        zoom = Math.min(zoom + 10, 200);
        updateZoom();
    }

    function zoomOut() {
        zoom = Math.max(zoom - 10, 50);
        updateZoom();
    }

    function updateZoom() {
        document.getElementById('zoomLevel').textContent = zoom + '%';
        document.getElementById('previewContainer').style.transform = `scale(${zoom / 100})`;
    }

    function toggleGrid() {
        // Toggle grid visibility
    }

    function downloadPreview() {
        // Download preview as image
    }

    // Modals
    function showAddSubjectModal() {
        document.getElementById('addSubjectModal').classList.remove('hidden');
    }

    function closeAddSubjectModal() {
        document.getElementById('addSubjectModal').classList.add('hidden');
    }

    // Helpers
    function setCanvasStatus(message) {
        document.getElementById('canvasStatus').textContent = message;
    }

    function showValidationPanel(messages, type) {
        const panel = document.getElementById('validationPanel');
        const msgContainer = document.getElementById('validationMessages');
        msgContainer.innerHTML = messages.map(msg => `
            <div class="p-2 rounded ${type === 'error' ? 'bg-red-50 text-red-800' : 'bg-yellow-50 text-yellow-800'}">
                ${type === 'error' ? '✕' : '⚠'} ${msg}
            </div>
        `).join('');
        panel.classList.remove('hidden');
    }

    function generateHTMLTemplate() {
        // Generate HTML from current template structure
        return '<div>Template HTML</div>';
    }

    function showNotification(message, type) {
        // Show toast notification
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white z-50`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 4000);
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        updateZoom();
    });
</script>
@endpush

@push('styles')
<style>
    .btn-primary, .btn-secondary, .btn-success, .btn-outline, .btn-sm {
        @apply px-4 py-2 rounded-lg font-medium transition-colors;
    }
    
    .btn-primary {
        @apply bg-blue-600 text-white hover:bg-blue-700;
    }
    
    .btn-secondary {
        @apply bg-gray-200 text-gray-900 hover:bg-gray-300;
    }
    
    .btn-success {
        @apply bg-green-600 text-white hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed;
    }
    
    .btn-outline {
        @apply border border-gray-300 text-gray-900 hover:bg-gray-50;
    }
    
    .btn-sm {
        @apply px-3 py-1 text-sm;
    }

    .icon {
        @apply mr-1;
    }

    #previewContainer {
        transition: transform 0.2s ease-out;
    }
</style>
@endpush
@endsection
