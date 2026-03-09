@extends('layouts.app')

@section('title', "Éditer Structure — {$structure->name}")

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-edit text-blue-600 mr-3"></i>
            Éditer Structure
        </h1>
        <p class="text-gray-600 mt-2">{{ $structure->name }} — {{ $structure->classe->name }}</p>
    </div>

    <form method="POST" action="{{ route('teacher.bulletin-structure-ocr.update', $structure) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Informations de base -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Informations Générales</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nom de la Structure</label>
                    <input type="text" name="name" value="{{ old('name', $structure->name) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Description optionnelle...">{{ old('description', $structure->description) }}</textarea>
                </div>

                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_verified" value="1" {{ $structure->is_verified ? 'checked' : '' }} class="w-4 h-4">
                        <span class="font-semibold text-gray-700">Marquée comme vérifiée</span>
                    </label>
                    
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $structure->is_active ? 'checked' : '' }} class="w-4 h-4">
                        <span class="font-semibold text-gray-700">Activer cette structure</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Matières & Coefficients -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Matières & Coefficients</h3>
            
            <div id="subjectsTable" class="space-y-3 mb-4">
                @forelse ($structure->getSubjects() as $subject)
                    <div class="flex gap-3 items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Matière</label>
                            <input type="text" name="subjects[]" value="{{ $subject }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="w-32">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Coefficient</label>
                            <input type="number" name="coefficients[{{ $subject }}]" 
                                   value="{{ $structure->getCoefficients()[$subject] ?? 1 }}"
                                   min="0.1" max="10" step="0.1" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="button" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg remove-subject">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endforelse
            </div>

            <button type="button" id="addSubjectBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-plus mr-2"></i> Ajouter Matière
            </button>

            @error('subjects')
                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Formules de Calcul -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Formules de Calcul</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Formule Moyenne</label>
                    <textarea name="mean_formula" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm"
                              placeholder="Ex: (note_francais * 2 + note_maths * 3) / 5">{{ old('mean_formula', $structure->getCalculationRules()['formulas']['moyenne'] ?? '') }}</textarea>
                    <p class="text-gray-500 text-xs mt-1">Laissez vide pour moyenne arithmétique standard</p>

                    @error('mean_formula')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Méthode d'Arrondi</label>
                    <select name="rounding" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="round" {{ (old('rounding', $structure->getCalculationRules()['rounding'] ?? 'round')) == 'round' ? 'selected' : '' }}>Normal (0.5 = arrondi supérieur)</option>
                        <option value="ceil" {{ (old('rounding', $structure->getCalculationRules()['rounding'] ?? 'round')) == 'ceil' ? 'selected' : '' }}>Plafond (arrondi toujours supérieur)</option>
                        <option value="floor" {{ (old('rounding', $structure->getCalculationRules()['rounding'] ?? 'round')) == 'floor' ? 'selected' : '' }}>Plancher (arrondi toujours inférieur)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Appréciations -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Appréciations</h3>
            
            <div id="appreciationsContainer" class="space-y-2 mb-4">
                @forelse ($structure->getStructure()['appreciation_rules'] ?? [] as $appreciation)
                    <div class="flex gap-2">
                        <input type="text" name="appreciation_rules[]" value="{{ $appreciation }}" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button type="button" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg remove-appreciation">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>

            <button type="button" id="addAppreciationBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-plus mr-2"></i> Ajouter Appréciation
            </button>

            @error('appreciation_rules')
                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Boutons -->
        <div class="flex gap-4">
            <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i> Sauvegarder Modifications
            </button>
            <a href="{{ route('teacher.bulletin-structure-ocr.show', $structure) }}" 
               class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400 transition text-center">
                <i class="fas fa-times mr-2"></i> Annuler
            </a>
        </div>
    </form>
</div>

<script>
// Add subject
document.getElementById('addSubjectBtn')?.addEventListener('click', () => {
    const container = document.getElementById('subjectsTable');
    const div = document.createElement('div');
    div.className = 'flex gap-3 items-end';
    div.innerHTML = `
        <div class="flex-1">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Matière</label>
            <input type="text" name="subjects[]" placeholder="Nouvelle matière" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="w-32">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Coefficient</label>
            <input type="number" name="coefficients[new_{{ Date.now() }}]" value="1" min="0.1" max="10" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>
        <button type="button" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg remove-subject">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
    addRemoveHandler(div.querySelector('.remove-subject'));
});

// Remove handlers
function addRemoveHandler(btn) {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        btn.closest('div').remove();
    });
}

document.querySelectorAll('.remove-subject').forEach(addRemoveHandler);

// Add appreciation
document.getElementById('addAppreciationBtn')?.addEventListener('click', () => {
    const container = document.getElementById('appreciationsContainer');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <input type="text" name="appreciation_rules[]" placeholder="Nouvelle appréciation" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
        <button type="button" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg remove-appreciation">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
    addApprecHandler(div.querySelector('.remove-appreciation'));
});

function addApprecHandler(btn) {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        btn.closest('div').remove();
    });
}

document.querySelectorAll('.remove-appreciation').forEach(addApprecHandler);
</script>
@endsection
