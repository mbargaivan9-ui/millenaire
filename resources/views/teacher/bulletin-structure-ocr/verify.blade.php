@extends('layouts.app')

@section('title', "Vérifier Structure OCR — {$classe->name}")

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-check-circle text-green-500 mr-3"></i>
            Vérifier Structure Détectée
        </h1>
        <p class="text-gray-600 mt-2">Confiance OCR: <span class="font-bold text-green-600">{{ $confidence }}%</span></p>
    </div>

    <!-- Alertes -->
    @if ($confidence < 75)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-yellow-900 mb-2">
                <i class="fas fa-exclamation-triangle mr-2"></i> Confiance faible
            </h3>
            <p class="text-yellow-800 text-sm">Vérifiez attentivement les données détectées ci-dessous</p>
        </div>
    @endif

    <!-- Onglets -->
    <div class="flex gap-2 mb-6 border-b border-gray-200">
        <button type="button" data-tab="subjects" class="tab-btn active px-4 py-3 font-semibold text-gray-700 border-b-2 border-blue-600">
            <i class="fas fa-book mr-2"></i> Matières
        </button>
        <button type="button" data-tab="coefficients" class="tab-btn px-4 py-3 font-semibold text-gray-700 border-b-2 border-transparent hover:border-gray-300">
            <i class="fas fa-calculator mr-2"></i> Coefficients
        </button>
        <button type="button" data-tab="formulas" class="tab-btn px-4 py-3 font-semibold text-gray-700 border-b-2 border-transparent hover:border-gray-300">
            <i class="fas fa-code mr-2"></i> Formules
        </button>
        <button type="button" data-tab="appreciation" class="tab-btn px-4 py-3 font-semibold text-gray-700 border-b-2 border-transparent hover:border-gray-300">
            <i class="fas fa-star mr-2"></i> Appréciations
        </button>
    </div>

    <form action="{{ route('teacher.bulletin-structure-ocr.save', $classe) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Tab: Matières -->
        <div id="subjects-tab" class="tab-content bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900">Matières Détectées</h3>
            
            <div class="space-y-3" id="subjectsContainer">
                @forelse ($structure['subjects'] as $index => $subject)
                    <div class="flex gap-2">
                        <input type="text" name="subjects[]" value="{{ $subject }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="button" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg remove-subject">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @empty
                    <p class="text-gray-500 italic">Aucune matière détectée. Ajoutez-en manuellement.</p>
                @endforelse
            </div>

            <button type="button" id="addSubjectBtn" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-plus mr-2"></i> Ajouter matière
            </button>

            @if ($errors->has('subjects'))
                <p class="text-red-600 text-sm mt-2">{{ $errors->first('subjects') }}</p>
            @endif
        </div>

        <!-- Tab: Coefficients -->
        <div id="coefficients-tab" class="tab-content hidden bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900">Coefficients</h3>
            
            <div class="space-y-3" id="coefficientsTable">
                @forelse ($structure['subjects'] as $subject)
                    <div class="flex gap-2 items-center">
                        <label class="flex-1 font-semibold text-gray-700">{{ $subject }}</label>
                        <input type="number" name="coefficients[{{ $subject }}]" 
                               value="{{ $structure['coefficients'][$subject] ?? 1 }}"
                               min="0.1" max="10" step="0.1" 
                               class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                @endforelse
            </div>

            @if ($errors->has('coefficients'))
                <p class="text-red-600 text-sm mt-2">{{ $errors->first('coefficients') }}</p>
            @endif
        </div>

        <!-- Tab: Formules -->
        <div id="formulas-tab" class="tab-content hidden bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900">Formules de Calcul</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block font-semibold text-gray-700 mb-2">Formule Moyenne</label>
                    <textarea name="mean_formula" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm"
                              placeholder="Ex: (note_francais * 2 + note_maths * 3) / 5">{{ $rules['formulas']['moyenne'] ?? '' }}</textarea>
                    <p class="text-gray-500 text-xs mt-1">Laissez vide pour moyenne arithmétique standard</p>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-2">Arrondi</label>
                    <select name="rounding" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="round" {{ ($rules['rounding'] ?? 'round') == 'round' ? 'selected' : '' }}>Normal (0.5 = arrondi supérieur)</option>
                        <option value="ceil" {{ ($rules['rounding'] ?? 'round') == 'ceil' ? 'selected' : '' }}>Plafond (arrondi toujours supérieur)</option>
                        <option value="floor" {{ ($rules['rounding'] ?? 'round') == 'floor' ? 'selected' : '' }}>Plancher (arrondi toujours inférieur)</option>
                    </select>
                </div>
            </div>

            @if ($errors->has('mean_formula'))
                <p class="text-red-600 text-sm mt-2">{{ $errors->first('mean_formula') }}</p>
            @endif
        </div>

        <!-- Tab: Appréciations -->
        <div id="appreciation-tab" class="tab-content hidden bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900">Appréciations Détectées</h3>
            
            <div class="space-y-2" id="appreciationsContainer">
                @forelse ($structure['appreciation_rules'] as $appreciation)
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox" name="appreciation_rules[]" value="{{ $appreciation }}" checked class="w-4 h-4">
                        <span class="font-semibold">{{ $appreciation }}</span>
                    </label>
                @empty
                    <p class="text-gray-500 italic">Aucune appréciation détectée</p>
                @endforelse
            </div>

            @if ($errors->has('appreciation_rules'))
                <p class="text-red-600 text-sm mt-2">{{ $errors->first('appreciation_rules') }}</p>
            @endif
        </div>

        <!-- Infos sur la classe -->
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-600">
                <strong>Classe:</strong> {{ $classe->name }} <br>
                <strong>Méthode OCR:</strong> {{ $ocrResult['method'] ?? 'ocr.space' }} <br>
                <strong>Confiance:</strong> {{ $confidence }}%
            </p>
        </div>

        <!-- Boutons -->
        <div class="flex gap-4">
            <button type="submit" class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                <i class="fas fa-check mr-2"></i> Sauvegarder Structure
            </button>
            <a href="{{ route('teacher.bulletin-structure-ocr.create', $classe) }}" 
               class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400 transition text-center">
                Recommencer
            </a>
        </div>
    </form>
</div>

<script>
// Tabs
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabName = btn.dataset.tab;
        
        // Deactivate all
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-b-2 border-blue-600 text-blue-600');
            b.classList.add('border-b-2 border-transparent text-gray-700');
        });
        document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
        
        // Activate selected
        btn.classList.remove('border-b-2 border-transparent');
        btn.classList.add('border-b-2 border-blue-600');
        document.getElementById(tabName + '-tab').classList.remove('hidden');
    });
});

// Add subject
document.getElementById('addSubjectBtn')?.addEventListener('click', () => {
    const container = document.getElementById('subjectsContainer');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <input type="text" name="subjects[]" placeholder="Nouvelle matière" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
        <button type="button" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg remove-subject">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
    addRemoveHandler(div.querySelector('.remove-subject'));
});

// Remove subject
function addRemoveHandler(btn) {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        btn.closest('div').remove();
    });
}

document.querySelectorAll('.remove-subject').forEach(addRemoveHandler);
</script>
@endsection
