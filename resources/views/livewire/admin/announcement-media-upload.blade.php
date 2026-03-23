<div class="space-y-6">
    {{-- Photo de Couverture --}}
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border-2 border-dashed border-blue-200 hover:border-blue-400 transition-colors">
        <div class="mb-4">
            <label class="flex items-center gap-2 font-semibold text-gray-800 mb-3">
                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" />
                </svg>
                {{ app()->getLocale() === 'fr' ? 'Photo de Couverture' : 'Cover Image' }}
            </label>
            <p class="text-sm text-gray-600">{{ app()->getLocale() === 'fr' ? 'JPEG, PNG, GIF ou WebP - Max 5MB' : 'JPEG, PNG, GIF or WebP - Max 5MB' }}</p>
        </div>

        @if($coverImagePreview)
        <div class="relative mb-4">
            <img src="{{ $coverImagePreview }}" alt="Preview" class="w-full h-48 object-cover rounded-lg shadow-md">
            <button wire:click="clearCoverImage" class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        @else
        <label class="block cursor-pointer group">
            <div class="border-2 border-dashed border-blue-300 rounded-lg p-8 text-center hover:bg-blue-100 transition-colors">
                <svg class="w-12 h-12 mx-auto text-blue-400 mb-3 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="text-blue-600 font-semibold">{{ app()->getLocale() === 'fr' ? 'Déposer votre image ou cliquer' : 'Drag & drop your image or click' }}</p>
            </div>
            <input type="file" wire:model="coverImage" accept="image/*" class="hidden">
        </label>
        @endif

        @error('coverImage') 
        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- Fichier Attaché --}}
    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 border-2 border-dashed border-purple-200 hover:border-purple-400 transition-colors">
        <div class="mb-4">
            <label class="flex items-center gap-2 font-semibold text-gray-800 mb-3">
                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7a2 2 0 11-4 0 2 2 0 014 0z" />
                    <path d="M3 20h14a2 2 0 002-2V4a2 2 0 00-2-2H3a2 2 0 00-2 2v14a2 2 0 002 2zm9-13a2 2 0 100-4 2 2 0 000 4z" />
                </svg>
                {{ app()->getLocale() === 'fr' ? 'Fichier Attaché' : 'Attached File' }}
            </label>
            <p class="text-sm text-gray-600">{{ app()->getLocale() === 'fr' ? 'Tous les formats - Max 25MB' : 'All formats - Max 25MB' }}</p>
        </div>

        @if($attachmentInfo)
        <div class="bg-white rounded-lg p-4 mb-4 border border-purple-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 truncate">{{ $attachmentInfo['name'] }}</p>
                        <p class="text-xs text-gray-600">{{ $attachmentInfo['size'] }} KB</p>
                    </div>
                </div>
                <button wire:click="clearAttachedFile" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
        @else
        <label class="block cursor-pointer group">
            <div class="border-2 border-dashed border-purple-300 rounded-lg p-8 text-center hover:bg-purple-100 transition-colors">
                <svg class="w-12 h-12 mx-auto text-purple-400 mb-3 group-hover:text-purple-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-purple-600 font-semibold">{{ app()->getLocale() === 'fr' ? 'Déposer votre fichier ou cliquer' : 'Drag & drop your file or click' }}</p>
            </div>
            <input type="file" wire:model="attachedFile" class="hidden">
        </label>
        @endif

        @error('attachedFile') 
        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
        @enderror
    </div>
</div>
