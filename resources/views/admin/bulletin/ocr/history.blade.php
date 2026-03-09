@extends('layouts.app')

@section('title', "Historique - Structure Bulletin")

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.bulletin.index', $structure->classe) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
            ← Retour aux structures
        </a>
    </div>

    <h1 class="text-3xl font-bold text-gray-800 mb-2">Historique des Modifications</h1>
    <p class="text-gray-600 mb-8">
        Semestre {{ $structure->metadata['term'] ?? 'N/A' }} - {{ $structure->metadata['academic_year'] ?? 'N/A' }} 
        (<span class="font-semibold">{{ $revisions->count() }} revision{{ $revisions->count() > 1 ? 's' : '' }}</span>)
    </p>

    @if ($revisions->isEmpty())
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <p class="text-gray-700">Aucune révision trouvée</p>
        </div>
    @else
        <div class="grid gap-6">
            @foreach ($revisions as $index => $revision)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="border-b p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">
                                    @if ($index === 0)
                                        <span class="text-green-600">✓ Version Actuelle</span>
                                    @else
                                        Révision #{{ $revisions->count() - $index }}
                                    @endif
                                </h3>
                                <p class="text-gray-600 text-sm mt-2">
                                    Modifiée par <strong>{{ $revision->modifier?->name ?? 'Système' }}</strong> le 
                                    <strong>{{ $revision->modified_at->format('d/m/Y H:i:s') }}</strong>
                                </p>
                            </div>

                            @if ($index > 0)
                                <form action="{{ route('admin.bulletin.revertToRevision', [$structure, $revision->id]) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" 
                                            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg font-semibold hover:bg-blue-700 transition"
                                            onclick="return confirm('Restaurer cette version?')">
                                        ↪️ Restaurer
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if ($revision->change_description)
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                                <p class="text-gray-800">{{ $revision->change_description }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Changes Detail --}}
                    <div class="p-6 bg-gray-50">
                        <button type="button" 
                                onclick="toggleDetails(this, 'revision-{{ $revision->id }}')"
                                class="w-full text-left font-semibold text-gray-800 hover:text-blue-600 transition flex justify-between items-center">
                            <span>Voir les changements détaillés</span>
                            <span class="text-xl">▼</span>
                        </button>

                        <div id="revision-{{ $revision->id }}" class="hidden mt-4">
                            <div class="space-y-4">
                                {{-- Old Structure --}}
                                @if ($revision->old_structure)
                                    <div>
                                        <h4 class="font-semibold text-gray-800 mb-3">Avant</h4>
                                        <div class="bg-red-50 border border-red-200 rounded p-4">
                                            <div class="space-y-2 text-sm">
                                                @if (is_array($revision->old_structure))
                                                    @forelse ($revision->old_structure['subjects'] ?? [] as $subject)
                                                        <div class="flex justify-between">
                                                            <span class="text-gray-800">{{ $subject['name'] ?? 'N/A' }}</span>
                                                            <span class="text-gray-600">Coef: {{ $subject['coefficient'] ?? 0 }}</span>
                                                        </div>
                                                    @empty
                                                        <p class="text-gray-600 italic">Aucune matière</p>
                                                    @endforelse
                                                @else
                                                    <pre class="text-xs overflow-auto">{{ $revision->old_structure }}</pre>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- New Structure --}}
                                @if ($revision->new_structure)
                                    <div>
                                        <h4 class="font-semibold text-gray-800 mb-3">Après</h4>
                                        <div class="bg-green-50 border border-green-200 rounded p-4">
                                            <div class="space-y-2 text-sm">
                                                @if (is_array($revision->new_structure))
                                                    @forelse ($revision->new_structure['subjects'] ?? [] as $subject)
                                                        <div class="flex justify-between">
                                                            <span class="text-gray-800">{{ $subject['name'] ?? 'N/A' }}</span>
                                                            <span class="text-gray-600">Coef: {{ $subject['coefficient'] ?? 0 }}</span>
                                                        </div>
                                                    @empty
                                                        <p class="text-gray-600 italic">Aucune matière</p>
                                                    @endforelse
                                                @else
                                                    <pre class="text-xs overflow-auto">{{ $revision->new_structure }}</pre>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Timeline (Optional) --}}
    @if ($revisions->count() > 1)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Chronologie</h2>
            
            <div class="space-y-4">
                @foreach ($revisions as $index => $revision)
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-4 h-4 rounded-full {{ $index === 0 ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                            @if ($index < $revisions->count() - 1)
                                <div class="w-1 h-12 bg-gray-300"></div>
                            @endif
                        </div>

                        <div class="pb-4">
                            <p class="font-semibold text-gray-800">
                                @if ($index === 0)
                                    Version Actuelle
                                @else
                                    Révision #{{ $revisions->count() - $index }}
                                @endif
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ $revision->modified_at->format('d/m/Y H:i') }} par {{ $revision->modifier?->name ?? 'Système' }}
                            </p>
                            @if ($revision->change_description)
                                <p class="text-sm text-gray-700 mt-2">{{ $revision->change_description }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
function toggleDetails(button, elementId) {
    const element = document.getElementById(elementId);
    element.classList.toggle('hidden');
    
    const icon = button.querySelector('span:last-child');
    if (element.classList.contains('hidden')) {
        icon.textContent = '▼';
    } else {
        icon.textContent = '▲';
    }
}
</script>
@endsection


