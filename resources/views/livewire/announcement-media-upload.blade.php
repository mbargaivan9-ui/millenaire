<div class="space-y-6">
    {{-- Photo de Couverture --}}
    <div class="border-2 border-gray-200 rounded-lg p-6 bg-gray-50 hover:border-gray-300 transition">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-image text-blue-500"></i>
                    {{ app()->getLocale() === 'fr' ? 'Photo de Couverture' : 'Cover Image' }}
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    {{ app()->getLocale() === 'fr' ? 'Ou glissez-déposez une image (JPG, PNG, GIF, WebP - Max 5MB)' : 'Or drag & drop an image (JPG, PNG, GIF, WebP - Max 5MB)' }}
                </p>
            </div>
        </div>

        {{-- Aperçu de l'image existante --}}
        @if($existingCover && !$coverImage && !$removeCover)
            <div class="mb-4 relative inline-block">
                <img src="{{ asset('storage/' . $existingCover) }}" alt="Cover" class="h-32 w-auto rounded-lg object-cover border border-gray-300">
                <button type="button" wire:click="removeCoverImage" class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </div>
        @endif

        {{-- Aperçu de la nouvelle image upload --}}
        @if($coverImage)
            <div class="mb-4 relative inline-block">
                <img src="{{ $coverImage->temporaryUrl() }}" alt="Preview" class="h-32 w-auto rounded-lg object-cover border border-blue-300">
                <button type="button" wire:click="$set('coverImage', null)" class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center transition">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </div>
        @endif

        {{-- Zone d'upload drag & drop --}}
        <label class="flex items-center justify-center w-full px-6 py-8 border-2 border-dashed border-gray-400 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition" 
               id="coverDropZone"
               wire:key="cover-zone">
            <div class="text-center">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3 block"></i>
                <input type="file" wire:model="coverImage" accept="image/*" class="hidden" @change="$wire.updateCoverImage()">
                <p class="text-gray-600 font-medium">{{ app()->getLocale() === 'fr' ? 'Cliquez ou glissez une image' : 'Click or drag an image' }}</p>
            </div>
        </label>

        @error('coverImage')
            <div class="mt-2 flex items-center gap-2 text-red-600 text-sm">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </div>
        @enderror

        @if($coverImage)
            <div class="mt-3 text-sm text-blue-600 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                {{ app()->getLocale() === 'fr' ? 'Image sélectionnée' : 'Image selected' }}
            </div>
        @endif
    </div>

    {{-- Fichier Joint --}}
    <div class="border-2 border-gray-200 rounded-lg p-6 bg-gray-50 hover:border-gray-300 transition">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-paperclip text-green-500"></i>
                    {{ app()->getLocale() === 'fr' ? 'Fichier Joint' : 'Attached File' }}
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    {{ app()->getLocale() === 'fr' ? 'PDF, DOC, XLS, PPT, ZIP - Max 10MB (Optionnel)' : 'PDF, DOC, XLS, PPT, ZIP - Max 10MB (Optional)' }}
                </p>
            </div>
        </div>

        {{-- Fichier existant --}}
        @if($existingAttachment && !$attachedFile && !$removeFile)
            <div class="mb-4 flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-300">
                <span class="text-2xl">{{ $this->getFileIcon() }}</span>
                <div class="flex-1">
                    <p class="font-medium text-gray-800 text-sm truncate">{{ $attachmentName }}</p>
                </div>
                <button type="button" wire:click="removeAttachedFile" class="text-red-500 hover:text-red-700 transition">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        @endif

        {{-- Nouveau fichier upload --}}
        @if($attachedFile)
            <div class="mb-4 flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-300">
                <span class="text-2xl">{{ $this->getFileIcon() }}</span>
                <div class="flex-1">
                    <p class="font-medium text-gray-800 text-sm truncate">{{ $attachedFile->getClientOriginalName() }}</p>
                    <p class="text-xs text-gray-600">{{ number_format($attachedFile->getSize() / 1024, 2) }} KB</p>
                </div>
                <button type="button" wire:click="$set('attachedFile', null)" class="text-red-500 hover:text-red-700 transition">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        @endif

        {{-- Zone d'upload drag & drop --}}
        <label class="flex items-center justify-center w-full px-6 py-8 border-2 border-dashed border-gray-400 rounded-lg cursor-pointer hover:border-green-500 hover:bg-green-50 transition"
               id="fileDropZone"
               wire:key="file-zone">
            <div class="text-center">
                <i class="fas fa-file-upload text-4xl text-gray-400 mb-3 block"></i>
                <input type="file" wire:model="attachedFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip" class="hidden" @change="$wire.updateAttachedFile()">
                <p class="text-gray-600 font-medium">{{ app()->getLocale() === 'fr' ? 'Cliquez ou glissez un fichier' : 'Click or drag a file' }}</p>
            </div>
        </label>

        @error('attachedFile')
            <div class="mt-2 flex items-center gap-2 text-red-600 text-sm">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </div>
        @enderror

        @if($attachedFile)
            <div class="mt-3 text-sm text-green-600 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                {{ app()->getLocale() === 'fr' ? 'Fichier sélectionné' : 'File selected' }}
            </div>
        @endif
    </div>

    {{-- Messages de chargement --}}
    <div wire:loading class="flex items-center gap-2 text-blue-600 text-sm bg-blue-50 p-3 rounded-lg">
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ app()->getLocale() === 'fr' ? 'Chargement...' : 'Loading...' }}
    </div>
</div>

<script>
document.addEventListener('livewire:navigated', () => {
    setupDragDrop();
});

function setupDragDrop() {
    const zones = ['coverDropZone', 'fileDropZone'];
    
    zones.forEach(zoneId => {
        const zone = document.getElementById(zoneId);
        if (!zone) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            zone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            zone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            zone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            zone.classList.add('border-blue-500', 'bg-blue-50');
        }

        function unhighlight(e) {
            zone.classList.remove('border-blue-500', 'bg-blue-50');
        }

        zone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            const input = zone.querySelector('input');
            input.files = files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
}

// Initialiser au chargement
setupDragDrop();
</script>
