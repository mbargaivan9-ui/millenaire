@extends('layouts.app')

@section('title', 'Modèles de Bulletin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Modèles de Bulletin</h1>
                    <p class="text-gray-600 mt-1">Gérez les modèles de bulletin de vos classes</p>
                </div>
                <a href="{{ route('prof-principal.templates.upload.form') }}" class="btn-primary">
                    + Nouveau modèle
                </a>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        @if($templates->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <div class="text-6xl mb-4">📋</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Aucun modèle</h2>
            <p class="text-gray-600 mb-6">Créez votre premier modèle en important une image de bulletin</p>
            <a href="{{ route('prof-principal.templates.upload.form') }}" class="btn-primary inline-block">
                Commencer
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($templates as $template)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
                <!-- Header -->
                <div class="border-b border-gray-200 p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $template->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $template->classroom->name }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $template->is_validated ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $template->is_validated ? '✓ Publié' : '○ Brouillon' }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4 space-y-4">
                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Matières</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $template->subjectAssignments->count() }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-600">Bulletins</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $template->student_bulletins_count ?? 0 }}
                            </p>
                        </div>
                    </div>

                    <!-- Confidence Badge -->
                    @if($template->ocr_confidence)
                    <div class="bg-blue-50 rounded p-2">
                        <p class="text-xs text-blue-600 font-mono">
                            Confiance OCR: {{ $template->ocr_confidence }}%
                        </p>
                    </div>
                    @endif

                    <!-- Dates -->
                    <div class="text-xs text-gray-500 space-y-1">
                        <p>Créé: {{ $template->created_at->format('d/m/Y H:i') }}</p>
                        @if($template->validated_at)
                        <p>Publié: {{ $template->validated_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="border-t border-gray-200 p-4 flex gap-2">
                    <a href="{{ route('prof-principal.templates.show', $template) }}" 
                       class="flex-1 btn-outline text-center">
                        Voir
                    </a>
                    @if(!$template->is_validated)
                    <a href="{{ route('prof-principal.templates.edit', $template) }}" 
                       class="flex-1 btn-primary text-center">
                        Éditer
                    </a>
                    @endif
                </div>

                <!-- Dropdown Menu -->
                <div class="border-t border-gray-200 px-4 py-2 relative group">
                    <button class="w-full py-2 text-gray-600 hover:text-gray-900 text-sm">⋮ Plus</button>
                    <div class="absolute right-0 mt-0 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden group-hover:block z-10">
                        @if(!$template->is_validated)
                        <form action="{{ route('prof-principal.templates.duplicate', $template) }}" method="POST" class="block">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-50 text-sm text-gray-700">
                                📋 Dupliquer
                            </button>
                        </form>
                        <form action="{{ route('prof-principal.templates.destroy', $template) }}" method="POST" class="block" onsubmit="return confirm('Supprimer ce modèle?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-red-50 text-sm text-red-700">
                                🗑️ Supprimer
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $templates->links() }}
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .btn-primary, .btn-outline {
        @apply px-4 py-2 rounded-lg font-medium transition-colors;
    }
    
    .btn-primary {
        @apply bg-blue-600 text-white hover:bg-blue-700;
    }
    
    .btn-outline {
        @apply border border-gray-300 text-gray-900 hover:bg-gray-50;
    }
</style>
@endpush
@endsection
