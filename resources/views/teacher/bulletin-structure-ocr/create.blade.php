@extends('layouts.app')

@section('title', "Créer Structure Bulletin par OCR — {$classe->name}")

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('teacher.bulletin-templates.index', $classe) }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-4">
            <i class="fas fa-arrow-left"></i> Retour aux modèles
        </a>
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-camera text-blue-500 mr-3"></i>
            Créer Structure par OCR
        </h1>
        <p class="text-gray-600 mt-2">Téléchargez une photo du bulletin pour extraire automatiquement sa structure</p>
    </div>

    <!-- Messages d'alerte -->
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-red-900 mb-2">Erreurs:</h3>
            <ul class="list-disc list-inside text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Card Principal -->
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl mx-auto">
        
        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-900 mb-2">
                <i class="fas fa-info-circle mr-2"></i> Instructions
            </h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>✓ Prenez une photo claire du bulletin scolaire</li>
                <li>✓ Assurez-vous que tout le tableau est visible</li>
                <li>✓ Formats acceptés: JPG, PNG, PDF (max 10MB)</li>
                <li>✓ Notre OCR détectera automatiquement: matières, coefficients, formules</li>
            </ul>
        </div>

        <!-- Formulaire -->
        <form action="{{ route('teacher.bulletin-structure-ocr.upload', $classe) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Zone de drop -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition cursor-pointer"
                 id="dropZone">
                <input type="file" name="bulletin_image" id="bulletinImage" class="hidden" accept=".jpg,.jpeg,.png,.pdf" required>
                
                <div id="uploadPrompt">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-700 font-semibold">Cliquez ou glissez votre image ici</p>
                    <p class="text-gray-500 text-sm mt-1">JPG, PNG ou PDF</p>
                </div>

                <div id="uploadSuccess" class="hidden">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                    <p id="fileName" class="text-gray-700 font-semibold"></p>
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50"
                        id="submitBtn" disabled>
                    <i class="fas fa-magic mr-2"></i> Lancer l'OCR
                </button>
                <a href="{{ route('teacher.bulletin-templates.index', $classe) }}" 
                   class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400 transition text-center">
                    Annuler
                </a>
            </div>

            <!-- Barre de progression -->
            <div id="progressContainer" class="hidden">
                <p class="text-sm text-gray-600 mb-2">Traitement en cours...</p>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div id="progressBar" class="bg-blue-600 h-full rounded-full animate-pulse" style="width: 0%"></div>
                </div>
            </div>
        </form>

        <!-- Infos supplémentaires -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h4 class="font-semibold text-gray-900 mb-3">
                <i class="fas fa-cogs text-gray-600 mr-2"></i> Qu'est-ce qui sera extrait?
            </h4>
            <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-green-500 mt-1 flex-shrink-0"></i>
                    <span>Matières enseignées</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-green-500 mt-1 flex-shrink-0"></i>
                    <span>Coefficients</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-green-500 mt-1 flex-shrink-0"></i>
                    <span>Échelle de notation</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-green-500 mt-1 flex-shrink-0"></i>
                    <span>Formules de calcul</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-green-500 mt-1 flex-shrink-0"></i>
                    <span>Règles d'appréciation</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-green-500 mt-1 flex-shrink-0"></i>
                    <span>Cas spéciaux</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('bulletinImage');
const uploadPrompt = document.getElementById('uploadPrompt');
const uploadSuccess = document.getElementById('uploadSuccess');
const fileName = document.getElementById('fileName');
const submitBtn = document.getElementById('submitBtn');
const progressContainer = document.getElementById('progressContainer');
const progressBar = document.getElementById('progressBar');

// Drag and drop
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => {
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });
});

dropZone.addEventListener('drop', (e) => {
    const dt = e.dataTransfer;
    const files = dt.files;
    fileInput.files = files;
    handleFileSelect();
});

dropZone.addEventListener('click', () => fileInput.click());

fileInput.addEventListener('change', handleFileSelect);

function handleFileSelect() {
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        fileName.textContent = file.name;
        uploadPrompt.classList.add('hidden');
        uploadSuccess.classList.remove('hidden');
        submitBtn.disabled = false;
    } else {
        uploadPrompt.classList.remove('hidden');
        uploadSuccess.classList.add('hidden');
        submitBtn.disabled = true;
    }
}

// Simuler progression
document.querySelector('form').addEventListener('submit', (e) => {
    progressContainer.classList.remove('hidden');
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 30;
        if (progress > 90) progress = 90;
        progressBar.style.width = progress + '%';
    }, 300);
});
</script>

<style>
#dropZone {
    transition: all 0.3s ease;
}

#dropZone.border-blue-500 {
    background-color: rgba(59, 130, 246, 0.05);
}
</style>
@endsection
