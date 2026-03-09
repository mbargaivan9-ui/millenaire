@extends('layouts.app')

@section('title', $template->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <a href="{{ route('prof-principal.templates.index') }}" class="text-blue-600 hover:text-blue-700 text-sm mb-2 inline-block">
                        ← Retour aux modèles
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $template->name }}</h1>
                    <p class="text-gray-600 mt-1">Classe: {{ $template->classroom->name }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($canEdit && !$template->is_validated)
                    <a href="{{ route('prof-principal.templates.edit', $template) }}" class="btn-primary">
                        ✏️ Éditer
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="col-span-2 space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">État du modèle</h2>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Statut</p>
                            <p class="text-2xl font-bold mt-1">
                                @if($template->is_validated)
                                    <span class="text-green-600">✓ Publié</span>
                                @else
                                    <span class="text-yellow-600">○ Brouillon</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Confiance OCR</p>
                            <p class="text-2xl font-bold mt-1">
                                @if($template->ocr_confidence)
                                    {{ $template->ocr_confidence }}%
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Subjects Section -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Matières assignées</h2>
                    
                    @if($template->subjectAssignments->count() > 0)
                    <div class="space-y-3">
                        @foreach($template->subjectAssignments as $assignment)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $assignment->subject->name }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Enseignant: {{ $assignment->teacher->name }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">Coefficient</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $assignment->coefficient }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Total coefficient -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">Total coefficients</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $template->subjectAssignments->sum('coefficient') }}
                        </p>
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-8">Aucune matière assignée</p>
                    @endif
                </div>

                <!-- Student Bulletins Section -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Bulletins générés</h2>
                    
                    @if($template->studentBulletins->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Élève</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Statut</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Moyenne générale</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">Rang</th>
                                    <th class="text-right py-3 px-4 font-semibold text-gray-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($template->studentBulletins->take(10) as $bulletin)
                                <tr>
                                    <td class="py-3 px-4">
                                        <p class="font-medium text-gray-900">{{ $bulletin->student->name }}</p>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded text-xs font-semibold
                                            @if($bulletin->status === 'complete')
                                                bg-green-100 text-green-800
                                            @elseif($bulletin->status === 'partial')
                                                bg-yellow-100 text-yellow-800
                                            @else
                                                bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($bulletin->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($bulletin->general_average)
                                        <span class="font-mono text-gray-900">{{ number_format($bulletin->general_average, 2) }}/20</span>
                                        @else
                                        <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($bulletin->class_rank)
                                        <span class="font-semibold text-gray-900">#{{ $bulletin->class_rank }}</span>
                                        @else
                                        <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('prof-principal.bulletins.show', $bulletin) }}" class="text-blue-600 hover:text-blue-700 text-sm">
                                            Voir →
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($template->studentBulletins->count() > 10)
                    <div class="mt-4 text-center">
                        <a href="{{ route('prof-principal.bulletins.index', ['template_id' => $template->id]) }}" 
                           class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            Voir tous les bulletins ({{ $template->studentBulletins->count() }})
                        </a>
                    </div>
                    @endif
                    @else
                    <p class="text-gray-500 text-center py-8">Aucun bulletin généré pour ce modèle</p>
                    @endif
                </div>

                <!-- Template Structure Preview -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Structure du modèle</h2>
                    
                    @php
                    $templateData = json_decode($template->template_json, true);
                    @endphp

                    @if($templateData)
                    <div class="space-y-4">
                        <!-- Header -->
                        @if(isset($templateData['header']))
                        <details class="border border-gray-200 rounded-lg">
                            <summary class="px-4 py-3 cursor-pointer font-semibold text-gray-900 bg-gray-50 hover:bg-gray-100">
                                📄 En-tête
                            </summary>
                            <div class="px-4 py-3 space-y-2 text-sm text-gray-600 bg-white">
                                @if(isset($templateData['header']['school_name']))
                                <p>• Nom école: <span class="font-mono text-gray-900">{{ $templateData['header']['school_name'] }}</span></p>
                                @endif
                                @if(isset($templateData['header']['academic_year']))
                                <p>• Année scolaire: <span class="font-mono text-gray-900">{{ $templateData['header']['academic_year'] }}</span></p>
                                @endif
                                @if(isset($templateData['header']['period']))
                                <p>• Période: <span class="font-mono text-gray-900">{{ $templateData['header']['period'] }}</span></p>
                                @endif
                            </div>
                        </details>
                        @endif

                        <!-- Subjects -->
                        @if(isset($templateData['subjects']) && count($templateData['subjects']) > 0)
                        <details class="border border-gray-200 rounded-lg">
                            <summary class="px-4 py-3 cursor-pointer font-semibold text-gray-900 bg-gray-50 hover:bg-gray-100">
                                📚 Matières ({{ count($templateData['subjects']) }})
                            </summary>
                            <div class="px-4 py-3 space-y-2 text-sm text-gray-600 bg-white">
                                @foreach($templateData['subjects'] as $subject)
                                <p>• {{ $subject['name'] ?? 'Sans nom' }} (Coef: {{ $subject['coefficient'] ?? '1' }})</p>
                                @endforeach
                            </div>
                        </details>
                        @endif

                        <!-- Calculations -->
                        @if(isset($templateData['calculations']))
                        <details class="border border-gray-200 rounded-lg">
                            <summary class="px-4 py-3 cursor-pointer font-semibold text-gray-900 bg-gray-50 hover:bg-gray-100">
                                🎯 Calculs
                            </summary>
                            <div class="px-4 py-3 space-y-2 text-sm text-gray-600 bg-white">
                                <p>• Méthode: <span class="font-mono text-gray-900">{{ $templateData['calculations']['method'] ?? 'weighted' }}</span></p>
                                @if(isset($templateData['calculations']['appreciation_scale']))
                                <p>• Échelle d'appréciation personnalisée</p>
                                @endif
                            </div>
                        </details>
                        @endif
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-4">Aucune structure disponible</p>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Info Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Informations</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-600">Créateur</p>
                            <p class="font-medium text-gray-900">{{ $template->creator->name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Créé le</p>
                            <p class="font-medium text-gray-900">{{ $template->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        @if($template->validated_at)
                        <div class="border-t border-gray-200 pt-3">
                            <p class="text-gray-600">Publié le</p>
                            <p class="font-medium text-gray-900">{{ $template->validated_at->format('d/m/Y H:i') }}</p>
                            @if($template->validator)
                            <p class="text-gray-600 mt-2">Par</p>
                            <p class="font-medium text-gray-900">{{ $template->validator->name }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-blue-50 rounded-lg border border-blue-200 p-6">
                    <h3 class="font-semibold text-blue-900 mb-4">Statistiques</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-blue-700">Matières</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $template->subjectAssignments->count() }}</p>
                        </div>
                        <div>
                            <p class="text-blue-700">Bulletins</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $template->studentBulletins->count() }}</p>
                        </div>
                        <div>
                            <p class="text-blue-700">Complétés</p>
                            <p class="text-2xl font-bold text-blue-900">
                                {{ $template->studentBulletins->where('status', 'complete')->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-2">
                        @if($canEdit && !$template->is_validated)
                        <a href="{{ route('prof-principal.templates.edit', $template) }}" class="block btn-primary text-center">
                            Éditer le modèle
                        </a>
                        @endif

                        @if($template->is_validated)
                        <a href="{{ route('prof-principal.bulletins.export', $template) }}" class="block btn-secondary text-center">
                            Exporter les bulletins
                        </a>
                        @endif

                        @if($canEdit && !$template->is_validated)
                        <form action="{{ route('prof-principal.templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full btn-danger text-center">
                                Supprimer
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .btn-primary, .btn-secondary, .btn-danger {
        @apply px-4 py-2 rounded-lg font-medium transition-colors;
    }
    
    .btn-primary {
        @apply bg-blue-600 text-white hover:bg-blue-700;
    }

    .btn-secondary {
        @apply bg-gray-200 text-gray-900 hover:bg-gray-300;
    }
    
    .btn-danger {
        @apply bg-red-600 text-white hover:bg-red-700;
    }
</style>
@endpush
@endsection
