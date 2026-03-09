@extends('layouts.app')

@section('title', "Vérifier Structure Bulletin")

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Vérifier la Structure Extraite</h1>
        <p class="text-gray-600">Vérifiez les données extraites par OCR et corrigez si nécessaire</p>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-3 gap-8">
        {{-- Main Content --}}
        <div class="col-span-2">
            {{-- Structure Summary --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📊 Structure Détectée</h2>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded">
                        <p class="text-sm text-gray-600 mb-1">Classe</p>
                        <p class="font-semibold">{{ $structure->classe->name }}</p>
                    </div>

                    <div class="bg-blue-50 p-4 rounded">
                        <p class="text-sm text-gray-600 mb-1">Type Source</p>
                        <p class="font-semibold">{{ ucfirst($structure->source_type) }}</p>
                    </div>

                    <div class="bg-blue-50 p-4 rounded">
                        <p class="text-sm text-gray-600 mb-1">Matières Détectées</p>
                        <p class="font-semibold text-lg">{{ count($fields->where('field_type', 'subject')) }}</p>
                    </div>

                    <div class="bg-blue-50 p-4 rounded">
                        <p class="text-sm text-gray-600 mb-1">Statut</p>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                            Brouillon
                        </span>
                    </div>
                </div>

            {{-- Livewire Field Editor Component --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                @livewire('bulletin-field-editor', ['structure' => $structure])
            </div>

            {{-- Matières --}}
            <div class="border-t pt-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Matières et Coefficients</h3>

                    <div class="space-y-3">
                        @foreach ($fields->where('field_type', 'subject') as $field)
                            <div class="flex items-center justify-between bg-gray-50 p-4 rounded">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $field->field_label }}</p>
                                    <p class="text-xs text-gray-600">{{ $field->field_name }}</p>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600">Coefficient</p>
                                        <p class="font-semibold text-lg">{{ $field->coefficient }}</p>
                                    </div>

                                    <div class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        {{ $field->max_value }}/{{ $field->max_value }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Calculs Détectés --}}
                <div class="border-t mt-6 pt-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Colonnes Calculées</h3>

                    <div class="space-y-3">
                        @foreach ($fields->whereIn('field_type', ['average', 'rank', 'appreciation']) as $field)
                            <div class="flex items-center justify-between bg-gray-50 p-4 rounded">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $field->field_label }}</p>
                                    <p class="text-xs text-gray-600">{{ $field->calculation_formula ?? 'Manuel' }}</p>
                                </div>

                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                    {{ $field->field_type }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Métadonnées --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📝 Métadonnées</h2>

                <div class="grid grid-cols-2 gap-4">
                    @forelse ($structure->metadata as $key => $value)
                        <div>
                            <p class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $key) }}</p>
                            <p class="font-semibold">{{ $value }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 col-span-2">Aucune métadonnée détectée</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            {{-- Status Card --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6 sticky top-6">
                <h3 class="font-bold text-gray-800 mb-4">Prochaines Étapes</h3>

                <div class="space-y-3 mb-6">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center flex-shrink-0 text-sm">✓</div>
                        <div>
                            <p class="font-semibold text-sm">Extraction OCR</p>
                            <p class="text-xs text-gray-600">Complétée</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-yellow-500 text-white flex items-center justify-center flex-shrink-0 text-sm">2</div>
                        <div>
                            <p class="font-semibold text-sm">Vérification</p>
                            <p class="text-xs text-gray-600">En cours</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center flex-shrink-0 text-sm">3</div>
                        <div>
                            <p class="font-semibold text-sm">Validation</p>
                            <p class="text-xs text-gray-600">En attente</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center flex-shrink-0 text-sm">4</div>
                        <div>
                            <p class="font-semibold text-sm">Activation</p>
                            <p class="text-xs text-gray-600">En attente</p>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="space-y-3">
                    <form action="{{ route('admin.bulletin.validate', $structure) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
                            ✓ Valider la Structure
                        </button>
                    </form>

                    <a href="{{ route('admin.bulletin.preview', $structure) }}" class="block text-center px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                        👁️ Aperçu
                    </a>

                    <a href="{{ route('admin.bulletin.index', $structure->classe) }}" class="block text-center px-4 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition">
                        ← Retour
                    </a>
                </div>

                {{-- Quick Stats --}}
                <div class="border-t mt-6 pt-6">
                    <p class="text-xs font-semibold text-gray-600 mb-3 uppercase">Statistiques</p>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Champs</span>
                            <span class="font-semibold">{{ $fields->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Matières</span>
                            <span class="font-semibold">{{ $fields->where('field_type', 'subject')->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Calculées</span>
                            <span class="font-semibold">{{ $fields->whereNotNull('calculation_formula')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Source Info --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-600 uppercase font-semibold mb-2">Fichier Source</p>
                <p class="text-sm font-semibold text-gray-800 break-all">{{ basename($structure->source_file_path) }}</p>
                <p class="text-xs text-gray-500 mt-2">{{ $structure->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection


