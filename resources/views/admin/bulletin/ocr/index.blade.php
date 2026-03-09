@extends('layouts.app')

@section('title', "Structures de Bulletin - {$classe->name}")

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Structures de Bulletin</h1>
            <p class="text-gray-600">Classe: <span class="font-semibold">{{ $classe->name }}</span></p>
        </div>

        <a href="{{ route('admin.bulletin.uploadForm', $classe) }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition flex items-center gap-2">
            <span>+</span> Nouvelle Structure (OCR)
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabs de Filtrage --}}
    <div class="flex gap-4 mb-8 border-b">
        <a href="{{ route('admin.bulletin.index', $classe) }}" 
           class="px-4 py-2 font-semibold border-b-4 {{ !request('status') || request('status') === 'all' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600' }}">
            Tous ({{ $structures->count() }})
        </a>

        <a href="?status=draft" 
           class="px-4 py-2 font-semibold border-b-4 {{ request('status') === 'draft' ? 'border-yellow-600 text-yellow-600' : 'border-transparent text-gray-600' }}">
            Brouillon
        </a>

        <a href="?status=validated" 
           class="px-4 py-2 font-semibold border-b-4 {{ request('status') === 'validated' ? 'border-orange-600 text-orange-600' : 'border-transparent text-gray-600' }}">
            Validé
        </a>

        <a href="?status=active" 
           class="px-4 py-2 font-semibold border-b-4 {{ request('status') === 'active' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-600' }}">
            Actif
        </a>

        <a href="?status=archived" 
           class="px-4 py-2 font-semibold border-b-4 {{ request('status') === 'archived' ? 'border-gray-600 text-gray-600' : 'border-transparent text-gray-600' }}">
            Archivé
        </a>
    </div>

    @if ($structures->isEmpty())
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center">
            <p class="text-gray-700 text-lg mb-4">Aucune structure de bulletin pour cette classe</p>
            <p class="text-gray-600 mb-6">Commencez en téléchargeant une photo ou un document PDF du bulletin</p>
            <a href="{{ route('admin.bulletin.uploadForm', $classe) }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                + Créer une nouvelle structure
            </a>
        </div>
    @else
        <div class="grid gap-6">
            @foreach ($structures as $structure)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                    <div class="flex">
                        {{-- Left Border Status --}}
                        <div class="w-1 {{ match($structure->status) {
                            'draft' => 'bg-yellow-500',
                            'validated' => 'bg-orange-500',
                            'active' => 'bg-green-500',
                            'archived' => 'bg-gray-500',
                        } }}"></div>

                        {{-- Content --}}
                        <div class="flex-1 p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800">
                                        Semestre {{ $structure->metadata['term'] ?? 'N/A' }} - {{ $structure->metadata['academic_year'] ?? 'N/A' }}
                                    </h3>
                                    <p class="text-gray-600 text-sm">
                                        {{ $structure->fields()->where('field_type', 'subject')->count() }} matières • 
                                        Créée le {{ $structure->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>

                                {{-- Status Badge --}}
                                <span class="px-4 py-2 rounded-full text-sm font-semibold {{ match($structure->status) {
                                    'draft' => 'bg-yellow-100 text-yellow-800',
                                    'validated' => 'bg-orange-100 text-orange-800',
                                    'active' => 'bg-green-100 text-green-800',
                                    'archived' => 'bg-gray-100 text-gray-800',
                                } }}">
                                    {{ strtoupper($structure->status) }}
                                </span>
                            </div>

                            {{-- Subjects List --}}
                            <div class="mb-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Matières:</p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($structure->fields()->where('field_type', 'subject')->get() as $field)
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-semibold">
                                            {{ $field->field_label }} <span class="text-blue-600">({{ $field->coefficient }})</span>
                                        </span>
                                    @empty
                                        <span class="text-gray-500 text-sm italic">Aucune matière</span>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Validation Info --}}
                            @if ($structure->validated_at)
                                <div class="mb-4 text-xs text-gray-600">
                                    ✓ Validée par {{ $structure->validator?->name ?? 'Admin' }} le {{ $structure->validated_at->format('d/m/Y H:i') }}
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="flex gap-2 flex-wrap">
                                @if ($structure->status === 'draft')
                                    <a href="{{ route('admin.bulletin.review', $structure) }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg font-semibold hover:bg-blue-700 transition">
                                        👁️ Examiner
                                    </a>
                                @endif

                                @if (in_array($structure->status, ['draft', 'validated']))
                                    <a href="{{ route('admin.bulletin.preview', $structure) }}" class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg font-semibold hover:bg-purple-700 transition">
                                        📊 Aperçu
                                    </a>

                                    <form action="{{ route('admin.bulletin.validate', $structure) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg font-semibold hover:bg-green-700 transition">
                                            ✓ Valider
                                        </button>
                                    </form>
                                @endif

                                @if ($structure->status === 'validated')
                                    <form action="{{ route('admin.bulletin.activate', $structure) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white text-sm rounded-lg font-semibold hover:bg-yellow-700 transition">
                                            🚀 Activer
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('admin.bulletin.history', $structure) }}" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg font-semibold hover:bg-gray-700 transition">
                                    📜 Historique
                                </a>

                                <form action="{{ route('admin.bulletin.delete', $structure) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg font-semibold hover:bg-red-700 transition"
                                            onclick="return confirm('Êtes-vous sûr?')">
                                        🗑️ Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($structures instanceof \Illuminate\Pagination\Paginator)
            <div class="mt-8">
                {{ $structures->links() }}
            </div>
        @endif
    @endif

    {{-- Info Box --}}
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-900"><strong>💡 Conseil:</strong> Une seule structure peut être "Actif" à la fois. L'activer remplacera la structure précédente.</p>
    </div>
</div>
@endsection


