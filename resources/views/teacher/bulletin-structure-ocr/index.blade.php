@extends('layouts.app')

@section('title', "Structures de Bulletin — OCR")

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-file-invoice text-blue-600 mr-3"></i>
                Structures de Bulletin
            </h1>
            <p class="text-gray-600 mt-1">Structures extraites via OCR</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('teacher.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Retour
            </a>
            <a href="{{ route('teacher.bulletin-structure-ocr.create', ['classe' => $classes->first()?->id ?? 0]) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-plus mr-2"></i> Nouvelle Structure
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Total Structures</p>
            <p class="text-3xl font-bold text-gray-900">{{ $bulletinStructures->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Vérifiées</p>
            <p class="text-3xl font-bold text-green-600">{{ $bulletinStructures->where('is_verified', true)->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">En Attente</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $bulletinStructures->where('is_verified', false)->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Actives</p>
            <p class="text-3xl font-bold text-blue-600">{{ $bulletinStructures->where('is_active', true)->count() }}</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex gap-3 items-end flex-wrap">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Classe</label>
                <select name="classe" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Toutes les classes</option>
                    @foreach ($classes as $clase)
                        <option value="{{ $clase->id }}" {{ request('classe') == $clase->id ? 'selected' : '' }}>
                            {{ $clase->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Statut</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Tous</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Vérifiées</option>
                    <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>En Attente</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actives</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-filter mr-2"></i> Filtrer
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nom</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Classe</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Matières</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Confiance</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bulletinStructures as $structure)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('teacher.bulletin-structure-ocr.show', $structure) }}" class="font-semibold text-blue-600 hover:underline">
                                {{ $structure->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-gray-700">
                            {{ $structure->classe->name }}
                        </td>
                        <td class="px-6 py-4 text-gray-700">
                            <div class="flex gap-1 flex-wrap">
                                @foreach (array_slice($structure->getSubjects(), 0, 3) as $subject)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">{{ $subject }}</span>
                                @endforeach
                                @if (count($structure->getSubjects()) > 3)
                                    <span class="text-gray-500 text-xs">+{{ count($structure->getSubjects()) - 3 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $structure->ocr_confidence }}%"></div>
                                </div>
                                <span class="text-sm font-semibold {{ $structure->ocr_confidence >= 80 ? 'text-green-600' : ($structure->ocr_confidence >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $structure->ocr_confidence }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                @if ($structure->is_verified)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded font-semibold">
                                        <i class="fas fa-check mr-1"></i> Vérifiée
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded font-semibold">
                                        <i class="fas fa-hourglass-half mr-1"></i> En Attente
                                    </span>
                                @endif
                                
                                @if ($structure->is_active)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">Actif</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">Inactif</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ route('teacher.bulletin-structure-ocr.show', $structure) }}" 
                                   class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded transition" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if (!$structure->is_verified)
                                    <a href="{{ route('teacher.bulletin-structure-ocr.verify', $structure->classe) }}" 
                                       class="px-3 py-1 text-yellow-600 hover:bg-yellow-50 rounded transition" title="Vérifier">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                @endif
                                <a href="{{ route('teacher.bulletin-structure-ocr.edit', $structure) }}" 
                                   class="px-3 py-1 text-green-600 hover:bg-green-50 rounded transition" title="Éditer">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('teacher.bulletin-structure-ocr.destroy', $structure) }}" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-red-600 hover:bg-red-50 rounded transition" 
                                            onclick="return confirm('Êtes-vous sûr?')" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            <p>Aucune structure trouvée</p>
                            <a href="{{ route('teacher.dashboard') }}" class="text-blue-600 hover:underline mt-2 inline-block">
                                Retour au tableau de bord
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($bulletinStructures instanceof \Illuminate\Pagination\Paginator)
        <div class="mt-6">
            {{ $bulletinStructures->links() }}
        </div>
    @endif
</div>
@endsection
