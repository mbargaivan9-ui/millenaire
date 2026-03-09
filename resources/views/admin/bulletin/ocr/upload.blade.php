@extends('layouts.app')

@section('title', "Analyser Bulletin — {$classe->name}")

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Analyser un Bulletin</h1>
        <p class="text-gray-600">Uploadez une photo ou un PDF du bulletin pour l'analyser automatiquement</p>
        <p class="text-sm text-blue-600 mt-2">Classe: <strong>{{ $classe->name }}</strong></p>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('admin.bulletin.process-upload', $classe) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg p-8">
        @csrf

        {{-- Drag & Drop Zone --}}
        <div class="mb-8">
            <div class="border-4 border-dashed border-blue-300 rounded-lg p-12 text-center bg-blue-50 cursor-pointer hover:border-blue-500 transition" id="dropZone">
                <svg class="w-16 h-16 mx-auto mb-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8l3-3m0 0l3 3m-3-3v10" />
                </svg>

                <h3 class="text-xl font-semibold text-gray-800 mb-2">Uploadez votre bulletin</h3>
                <p class="text-gray-600 mb-4">Glissez-déposez une image ou un PDF du bulletin</p>
                
                <input 
                    type="file" 
                    name="bulletin_file" 
                    id="bulletinFile" 
                    accept=".jpg,.jpeg,.png,.pdf" 
                    class="hidden"
                    required
                />

                <label for="bulletinFile" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition cursor-pointer">
                    Sélectionner un fichier
                </label>

                <p class="text-sm text-gray-600 mt-4">
                    Formats acceptés: JPG, PNG, PDF (max 5MB)
                </p>
            </div>

            <div id="filePreview" class="mt-4 hidden">
                <p class="text-green-600 font-semibold">
                    <span id="fileName"></span>
                </p>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex gap-4">
            <button 
                type="submit" 
                class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition disabled:bg-gray-400"
                id="submitBtn"
                disabled
            >
                Analyser le Bulletin
            </button>

            <a 
                href="{{ route('admin.bulletin.index', $classe) }}" 
                class="px-6 py-3 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition"
            >
                Annuler
            </a>
        </div>
    </form>

    {{-- Information Box --}}
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="font-semibold text-blue-900 mb-4">💡 Comment ça marche?</h3>
        <ul class="space-y-2 text-blue-800 text-sm">
            <li>✓ L'image/PDF est traité par OCR (reconnaissance optique)</li>
            <li>✓ La structure du bulletin est extraite automatiquement</li>
            <li>✓ Vous pouvez vérifier et corriger les données</li>
            <li>✓ Une fois validée, la structure s'applique à tous les bulletins de la classe</li>
            <li>✓ Les calculs sont automatisés selon les formules détectées</li>
        </ul>
    </div>

    {{-- Recent Structures --}}
    @if ($classe->dynamicBulletinStructures()->exists())
        <div class="mt-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Structures existantes</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($classe->dynamicBulletinStructures()->latest()->limit(5)->get() as $structure)
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-semibold text-gray-800">Structure #{{ $structure->id }}</h4>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ 
                                $structure->status === 'active' ? 'bg-green-100 text-green-800' :
                                ($structure->status === 'validated' ? 'bg-blue-100 text-blue-800' :
                                'bg-gray-100 text-gray-800')
                            }}">
                                {{ ucfirst($structure->status) }}
                            </span>
                        </div>

                        <p class="text-xs text-gray-600 mb-3">
                            Créée le {{ $structure->created_at->format('d/m/Y à H:i') }}
                        </p>

                        <a 
                            href="{{ route('admin.bulletin.review', $structure) }}"
                            class="text-blue-600 hover:text-blue-800 text-sm font-semibold"
                        >
                            Voir détails →
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- JavaScript for Drag & Drop --}}
<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('bulletinFile');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const submitBtn = document.getElementById('submitBtn');

    // Drag & Drop handlers
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-blue-500', 'bg-blue-100');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-100');
        });
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        updateFileInfo();
    });

    // File input change
    fileInput.addEventListener('change', updateFileInfo);

    function updateFileInfo() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
            filePreview.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('cursor-not-allowed');
        } else {
            filePreview.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('cursor-not-allowed');
        }
    }

    // Click zone to select file
    dropZone.addEventListener('click', () => fileInput.click());
</script>
@endsection


