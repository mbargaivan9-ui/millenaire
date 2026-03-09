@extends('layouts.app')

@section('title', "Structure — {$structure->name}")

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $structure->name }}</h1>
            <p class="text-gray-600 mt-1">{{ $structure->classe->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('teacher.bulletin-structure-ocr.edit', $structure) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-edit mr-2"></i> Éditer
            </a>
            <a href="{{ route('teacher.bulletin-structure-ocr.index') }}" 
               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                <i class="fas fa-arrow-left mr-2"></i> Retour
            </a>
        </div>
    </div>

    <!-- Informations Générales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm mb-1">Classe</p>
            <p class="text-lg font-semibold">{{ $structure->classe->name }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm mb-1">Confiance OCR</p>
            <div class="flex items-center gap-2">
                <div class="w-20 bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $structure->ocr_confidence }}%"></div>
                </div>
                <span class="font-semibold {{ $structure->ocr_confidence >= 80 ? 'text-green-600' : ($structure->ocr_confidence >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $structure->ocr_confidence }}%
                </span>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm mb-1">À Vérifier</p>
            <div class="flex gap-1">
                @if (!$structure->is_verified)
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded text-sm font-semibold">
                        <i class="fas fa-hourglass-half mr-1"></i> En Attente
                    </span>
                @else
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded text-sm font-semibold">
                        <i class="fas fa-check mr-1"></i> Vérifiée
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Onglets -->
    <div class="flex gap-2 mb-6 border-b border-gray-200">
        <button type="button" data-tab="overview" class="tab-btn active px-4 py-3 font-semibold text-gray-700 border-b-2 border-blue-600">
            <i class="fas fa-info-circle mr-2"></i> Aperçu
        </button>
        <button type="button" data-tab="subjects" class="tab-btn px-4 py-3 font-semibold text-gray-700 border-b-2 border-transparent hover:border-gray-300">
            <i class="fas fa-book mr-2"></i> Matières
        </button>
        <button type="button" data-tab="formulas" class="tab-btn px-4 py-3 font-semibold text-gray-700 border-b-2 border-transparent hover:border-gray-300">
            <i class="fas fa-code mr-2"></i> Formules
        </button>
        <button type="button" data-tab="source" class="tab-btn px-4 py-3 font-semibold text-gray-700 border-b-2 border-transparent hover:border-gray-300">
            <i class="fas fa-image mr-2"></i> Source
        </button>
    </div>

    <!-- Tab: Overview -->
    <div id="overview-tab" class="tab-content bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">À Propos</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-gray-500 text-sm">Créé par</p>
                <p class="font-semibold">{{ $structure->creator->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Créé à</p>
                <p class="font-semibold">{{ $structure->created_at?->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Dernière modification</p>
                <p class="font-semibold">{{ $structure->updated_at?->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Modifié par</p>
                <p class="font-semibold">{{ $structure->updater->name ?? 'N/A' }}</p>
            </div>
        </div>
        
        @if ($structure->description)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-gray-500 text-sm">Description</p>
                <p class="text-gray-700">{{ $structure->description }}</p>
            </div>
        @endif
    </div>

    <!-- Tab: Subjects -->
    <div id="subjects-tab" class="tab-content hidden bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Matières Détectées</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($structure->getSubjects() as $subject)
                <div class="border border-gray-200 rounded-lg p-4">
                    <p class="font-semibold text-gray-900">{{ $subject }}</p>
                    <p class="text-gray-600 text-sm">
                        Coefficient: <strong>{{ $structure->getCoefficients()[$subject] ?? 1 }}</strong>
                    </p>
                </div>
            @empty
                <p class="text-gray-500 italic">Aucune matière détectée</p>
            @endforelse
        </div>
    </div>

    <!-- Tab: Formulas -->
    <div id="formulas-tab" class="tab-content hidden bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Formules de Calcul</h3>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Formule Moyenne</label>
                <div class="bg-gray-50 p-3 rounded font-mono text-sm border border-gray-200">
                    {{ $structure->getCalculationRules()['formulas']['moyenne'] ?? 'Moyenne arithmétique standard' }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Arrondi</label>
                <div class="bg-gray-50 p-3 rounded font-mono text-sm border border-gray-200">
                    {{ $structure->getCalculationRules()['rounding'] ?? 'round' }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Appréciations</label>
                <div class="space-y-2">
                    @forelse ($structure->getStructure()['appreciation_rules'] ?? [] as $rule)
                        <div class="bg-blue-50 p-2 rounded text-sm border border-blue-200">
                            {{ $rule }}
                        </div>
                    @empty
                        <p class="text-gray-500 italic">Aucune appréciation détectée</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Source Image -->
    <div id="source-tab" class="tab-content hidden bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Image Source</h3>
        
        @if ($structure->source_image_path && Storage::exists($structure->source_image_path))
            <div class="max-w-2xl mx-auto">
                <img src="{{ Storage::url($structure->source_image_path) }}" alt="Source" class="w-full border border-gray-200 rounded-lg">
                <p class="text-gray-500 text-sm mt-2">{{ $structure->source_image_path }}</p>
            </div>
        @else
            <p class="text-gray-500 italic">Pas d'image source disponible</p>
        @endif
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabName = btn.dataset.tab;
        
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-b-2 border-blue-600');
            b.classList.add('border-b-2 border-transparent');
        });
        document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
        
        btn.classList.remove('border-b-2 border-transparent');
        btn.classList.add('border-b-2 border-blue-600');
        document.getElementById(tabName + '-tab').classList.remove('hidden');
    });
});
</script>
@endsection
