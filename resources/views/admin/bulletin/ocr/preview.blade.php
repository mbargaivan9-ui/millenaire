@extends('layouts.app')

@section('title', "Aperçu - Structure Bulletin")

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.bulletin.review', $structure) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
            ← Retour à la vérification
        </a>
    </div>

    <h1 class="text-3xl font-bold text-gray-800 mb-2">Aperçu de la Structure</h1>
    <p class="text-gray-600 mb-8">
        Semestre {{ $structure->metadata['term'] ?? 'N/A' }} - {{ $structure->metadata['academic_year'] ?? 'N/A' }} - 
        <span class="font-semibold">{{ $structure->classe->name }}</span>
    </p>

    {{-- Structure Info --}}
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">📋 Configuration de la Structure</h2>

        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded">
                <p class="text-xs text-gray-600 mb-1">Matières</p>
                <p class="text-2xl font-bold text-blue-600">{{ $structure->fields()->where('field_type', 'subject')->count() }}</p>
            </div>

            <div class="bg-purple-50 p-4 rounded">
                <p class="text-xs text-gray-600 mb-1">Calculs</p>
                <p class="text-2xl font-bold text-purple-600">{{ $structure->fields()->whereNotNull('calculation_formula')->count() }}</p>
            </div>

            <div class="bg-green-50 p-4 rounded">
                <p class="text-xs text-gray-600 mb-1">Coefficients</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($structure->fields()->where('field_type', 'subject')->sum('coefficient'), 2) }}</p>
            </div>

            <div class="bg-yellow-50 p-4 rounded">
                <p class="text-xs text-gray-600 mb-1">Formule</p>
                <p class="text-sm font-bold text-yellow-600">
                    {{ ucfirst(str_replace('_', ' ', $structure->formula_config['algorithm'] ?? 'moyenne')) }}
                </p>
            </div>
        </div>

        {{-- Subjects List --}}
        <div class="border-t pt-6">
            <h3 class="font-semibold text-gray-800 mb-4">Matières Détectées</h3>

            <div class="grid grid-cols-2 gap-4">
                @foreach ($structure->fields()->where('field_type', 'subject')->orderBy('display_order')->get() as $field)
                    <div class="bg-blue-50 p-4 rounded border border-blue-200">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-semibold text-gray-800">{{ $field->field_label }}</h4>
                            <span class="px-2 py-1 bg-blue-600 text-white text-xs rounded">{{ $field->coefficient }}</span>
                        </div>
                        <p class="text-xs text-gray-600">{{ $field->field_name }}</p>
                        <p class="text-xs text-gray-500 mt-2">{{ $field->min_value }}/{{ $field->max_value }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Formula Config --}}
        <div class="border-t mt-6 pt-6">
            <h3 class="font-semibold text-gray-800 mb-4">⚙️ Configuration de Calcul</h3>

            <div class="grid grid-cols-2 gap-4">
                @foreach ($structure->formula_config as $key => $value)
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-xs text-gray-600 mb-1 capitalize">{{ str_replace('_', ' ', $key) }}</p>
                        <p class="font-semibold text-gray-800">
                            @if (is_array($value))
                                <pre class="text-xs overflow-auto">{{ json_encode($value) }}</pre>
                            @else
                                {{ $value }}
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Moteur de Calcul Interactif --}}
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        @livewire('bulletin-calculator', ['structure' => $structure])
    </div>

    {{-- Sample Bulletins --}}
    <h2 class="text-2xl font-bold text-gray-800 mb-6">👥 Exemples de Bulletins</h2>

    <div class="space-y-8">
        @forelse ($sampleStudents as $studentIndex => $student)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6">
                    <h3 class="text-2xl font-bold">{{ $student->first_name }} {{ $student->last_name }}</h3>
                    <p class="text-blue-100">Étudiant #{{ $studentIndex + 1 }}</p>
                </div>

                <div class="p-6">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b-2 border-gray-300">
                                <th class="text-left py-3 px-4 font-bold text-gray-800">Matière</th>
                                <th class="text-center py-3 px-4 font-bold text-gray-800 w-20">Note</th>
                                <th class="text-center py-3 px-4 font-bold text-gray-800 w-20">Coef.</th>
                                <th class="text-center py-3 px-4 font-bold text-gray-800 w-20">Calcul</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($structure->fields()->where('field_type', 'subject')->orderBy('display_order')->get() as $field)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-4 font-semibold text-gray-800">{{ $field->field_label }}</td>
                                    <td class="text-center py-3 px-4">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-semibold">
                                            {{ rand(8, 20) }}/{{ $field->max_value }}
                                        </span>
                                    </td>
                                    <td class="text-center py-3 px-4 text-gray-700">{{ $field->coefficient }}</td>
                                    <td class="text-center py-3 px-4">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded">
                                            {{ number_format(rand(800, 2000) / 100, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Summary Rows --}}
                            @foreach ($structure->fields()->whereNotNull('calculation_formula')->where('field_type', '!=', 'subject')->get() as $field)
                                <tr class="border-t-2 border-gray-300 bg-gray-50 font-semibold">
                                    <td class="py-3 px-4 text-gray-800">{{ $field->field_label }}</td>
                                    <td class="text-center py-3 px-4">
                                        <span class="px-3 py-1 bg-{{ match($field->field_type) {
                                            'average' => 'purple',
                                            'rank' => 'orange',
                                            default => 'gray'
                                        } }}-100 text-{{ match($field->field_type) {
                                            'average' => 'purple',
                                            'rank' => 'orange',
                                            default => 'gray'
                                        } }}-800 rounded-full">
                                            {{ number_format(rand(1200, 1800) / 100, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center py-3 px-4 text-gray-700">-</td>
                                    <td class="text-center py-3 px-4 text-xs text-gray-600">{{ $field->calculation_formula }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Appreciation --}}
                    @if ($appreciationField = $structure->fields()->where('field_type', 'appreciation')->first())
                        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                            <p class="text-sm font-semibold text-gray-700 mb-2">{{ $appreciationField->field_label }}:</p>
                            <p class="text-gray-700 italic">
                                "Étudiant montrant de bons efforts dans l'apprentissage avec une participation régulière..."
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center">
                <p class="text-gray-700">Aucun étudiant dans cette classe pour générer des exemples</p>
            </div>
        @endforelse
    </div>

    {{-- Info Box --}}
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-900">
            <strong>💡 Note:</strong> Les notes affichées ci-dessus sont des exemples simulés. Les notes réelles proviendront de votre système de gestion des notes.
        </p>
    </div>

    {{-- PDF Export Section (Phase 9) --}}
    @if ($structure->status === 'active')
        <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">📄 Export PDF</h2>
            <p class="text-gray-600 mb-6">Téléchargez les bulletins au format PDF</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Download Bulk PDF --}}
                <a href="{{ route('admin.bulletin.downloadBulkBulletinsPDF', $structure) }}" 
                   class="flex items-center gap-3 px-6 py-4 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg font-semibold hover:from-red-600 hover:to-red-700 transition shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div class="text-left">
                        <div class="text-sm">Télécharger</div>
                        <div class="text-xs opacity-90">Tous les bulletins (ZIP)</div>
                    </div>
                </a>

                {{-- Save to Storage --}}
                <form action="{{ route('admin.bulletin.saveBulletinsToStorage', $structure) }}" method="POST" class="inline-block w-full">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center gap-3 px-6 py-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg font-semibold hover:from-green-600 hover:to-green-700 transition shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h12a2 2 0 002-2v-12a2 2 0 00-2-2h-2.5a2 2 0 00-1 .268"></path>
                        </svg>
                        <div class="text-left">
                            <div class="text-sm">Sauvegarder</div>
                            <div class="text-xs opacity-90">Stocker les bulletins</div>
                        </div>
                    </button>
                </form>

                {{-- Preview HTML --}}
                @if ($structure->classe->members->count() > 0)
                    <a href="{{ route('admin.bulletin.previewBulletinHTML', [$structure, $structure->classe->members->first()->user_id]) }}" 
                       target="_blank"
                       class="flex items-center gap-3 px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg font-semibold hover:from-blue-600 hover:to-blue-700 transition shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <div class="text-left">
                            <div class="text-sm">Prévisualiser</div>
                            <div class="text-xs opacity-90">HTML (étudiant 1)</div>
                        </div>
                    </a>
                @endif
            </div>

            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded">
                <p class="text-sm text-green-800">
                    <strong>✓ Structure active</strong> - Les bulletins peuvent être générés et exportés au format PDF.
                </p>
            </div>
        </div>
    @else
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-sm text-yellow-800">
                <strong>⚠️</strong> Veuillez d'abord valider cette structure pour activer l'export PDF.
            </p>
        </div>
    @endif

            <form action="{{ route('admin.bulletin.validate', $structure) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
                    ✓ Valider cette Structure
                </button>
            </form>
        @endif
    </div>
</div>
@endsection


