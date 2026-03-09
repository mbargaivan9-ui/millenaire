<div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">🎯 Éditeur de Champs Interactif</h2>
        <button wire:click="$toggle('showAddForm')" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
            {{ $showAddForm ? '✕ Fermer' : '+ Ajouter Champ' }}
        </button>
    </div>

    {{-- Success Messages --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" wire:poll.remove="clearSessionMessage">
            {{ session('message') }}
        </div>
    @endif

    {{-- Formulaire d'Ajout --}}
    @if ($showAddForm || $editingFieldId)
        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                {{ $editingFieldId ? '✏️ Modifier Champ' : '➕ Nouveau Champ' }}
            </h3>

            <div class="grid grid-cols-2 gap-4 mb-4">
                {{-- Field Name --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nom du Champ *</label>
                    <input type="text" 
                           wire:model="fieldName" 
                           placeholder="e.g., francais, math, moyenne"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('fieldName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Field Label --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Libellé *</label>
                    <input type="text" 
                           wire:model="fieldLabel" 
                           placeholder="e.g., Français, Mathématiques"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('fieldLabel') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Field Type --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Type de Champ *</label>
                    <select wire:model="fieldType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach ($fieldTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Coefficient --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Coefficient</label>
                    <input type="number" 
                           step="0.01" 
                           wire:model="coefficient" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('coefficient') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Min Value --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Valeur Minimale</label>
                    <input type="number" 
                           step="0.01" 
                           wire:model="minValue" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Max Value --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Valeur Maximale</label>
                    <input type="number" 
                           step="0.01" 
                           wire:model="maxValue" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Formula --}}
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Formule de Calcul</label>
                    <input type="text" 
                           wire:model="calculationFormula" 
                           placeholder="e.g., weighted_average, rank_by_average"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Display Order --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ordre d'Affichage</label>
                    <input type="number" 
                           wire:model="displayOrder" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex gap-3 justify-end">
                <button wire:click="cancelEdit" class="px-4 py-2 bg-gray-500 text-white rounded-lg font-semibold hover:bg-gray-600 transition">
                    Annuler
                </button>
                <button wire:click="{{ $editingFieldId ? 'updateField' : 'addField' }}" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
                    {{ $editingFieldId ? '💾 Mettre à jour' : '➕ Ajouter' }}
                </button>
            </div>
        </div>
    @endif

    {{-- Champs List --}}
    <div class="space-y-3">
        @forelse ($fields as $field)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:bg-gray-100 transition flex justify-between items-center">
                <div class="flex-1">
                    <h4 class="font-bold text-gray-800">{{ $field->field_label }}</h4>
                    <p class="text-sm text-gray-600">
                        {{ $field->field_name }} 
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full ml-2">
                            {{ $fieldTypes[$field->field_type] ?? $field->field_type }}
                        </span>
                    </p>
                    @if ($field->coefficient)
                        <p class="text-xs text-gray-500 mt-1">Coefficient: {{ $field->coefficient }}</p>
                    @endif
                    @if ($field->calculation_formula)
                        <p class="text-xs text-purple-600 mt-1">Formule: {{ $field->calculation_formula }}</p>
                    @endif
                </div>

                <div class="flex gap-2">
                    {{-- Toggle Visibility --}}
                    <button wire:click="toggleFieldVisibility({{ $field->id }})" 
                            title="Toggle visibility"
                            class="px-3 py-2 {{ $field->is_visible ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }} rounded-lg transition">
                        {{ $field->is_visible ? '👁️' : '👁️‍🗨️' }}
                    </button>

                    {{-- Toggle Editable --}}
                    <button wire:click="toggleFieldEditable({{ $field->id }})" 
                            title="Toggle editable"
                            class="px-3 py-2 {{ $field->is_editable ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-gray-600' }} rounded-lg transition">
                        {{ $field->is_editable ? '✏️' : '🔒' }}
                    </button>

                    {{-- Edit Button --}}
                    <button wire:click="startEditingField({{ $field->id }})" 
                            class="px-3 py-2 bg-yellow-100 text-yellow-800 rounded-lg hover:bg-yellow-200 transition">
                        ✏️ Éditer
                    </button>

                    {{-- Delete Button --}}
                    <button wire:click="deleteField({{ $field->id }})" 
                            wire:confirm="Êtes-vous sûr? Cette action est irréversible."
                            class="px-3 py-2 bg-red-100 text-red-800 rounded-lg hover:bg-red-200 transition">
                        🗑️ Supprimer
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <p class="text-gray-700">Aucun champ défini. Cliquez sur "Ajouter Champ" pour commencer.</p>
            </div>
        @endforelse
    </div>

    {{-- Summary Stats --}}
    @if ($fields->count() > 0)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-600">
                ✓ <strong>{{ $fields->where('field_type', 'subject')->count() }}</strong> matières
                • <strong>{{ $fields->where('is_visible', true)->count() }}</strong> visibles
                • <strong>{{ $fields->whereNotNull('calculation_formula')->count() }}</strong> calculées
            </p>
        </div>
    @endif
</div>

<script>
    // JavaScript pour les messages de succès Livewire
    document.addEventListener('livewire:navigated', function() {
        // Clear session message after 5 seconds
        const message = document.querySelector('[wire\\:poll\\.remove]');
        if (message) {
            setTimeout(() => message.remove(), 5000);
        }
    });

    // Optional: Drag-drop reordering would go here with a library like Sortable.js
</script>
