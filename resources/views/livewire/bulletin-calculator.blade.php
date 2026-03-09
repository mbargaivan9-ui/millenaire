<div class="bulletin-calculator">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Moteur de Calcul</h3>
            <p class="text-sm text-gray-600">Testez les calculs avec des valeurs d'exemple</p>
        </div>
        
        <button 
            wire:click="toggleCalculator"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
            {{ $showCalculator ? '▼ Masquer' : '▶ Afficher' }}
        </button>
    </div>

    @if($showCalculator)
        <div class="space-y-6">
            {{-- Student Info --}}
            <div class="p-4 bg-white rounded-lg border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Élève Test</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $sampleStudent }}</p>
                    </div>
                    <button 
                        wire:click="randomizeStudent"
                        class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition"
                    >
                        🔄 Nouveau
                    </button>
                </div>
            </div>

            {{-- Subject Fields Input --}}
            @if($subjectFields->count() > 0)
                <div class="p-4 bg-white rounded-lg border border-gray-200">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Saisie des Notes</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($subjectFields as $field)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ $field->field_label }}
                                    @if($field->coefficient != 1)
                                        <span class="text-xs text-gray-500">(coef: {{ $field->coefficient }})</span>
                                    @endif
                                </label>
                                
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="range" 
                                        min="{{ $field->min_value ?? 0 }}" 
                                        max="{{ $field->max_value ?? 20 }}" 
                                        step="0.5"
                                        wire:change="updateSampleValue('{{ $field->field_name }}', $event.target.value)"
                                        value="{{ $sampleValues[$field->field_name] ?? 0 }}"
                                        class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                    >
                                    
                                    <input 
                                        type="number" 
                                        min="{{ $field->min_value ?? 0 }}" 
                                        max="{{ $field->max_value ?? 20 }}" 
                                        step="0.5"
                                        wire:change="updateSampleValue('{{ $field->field_name }}', $event.target.value)"
                                        value="{{ $sampleValues[$field->field_name] ?? 0 }}"
                                        class="w-16 px-2 py-1 border border-gray-300 rounded text-right"
                                    >
                                </div>

                                @if(isset($validationErrors))
                                    @foreach($validationErrors as $error)
                                        @if($error['field'] === $field->field_label)
                                            <p class="text-xs mt-1 text-{{ $error['type'] === 'error' ? 'red' : 'amber' }}-600">
                                                ⚠️ {{ $error['message'] }}
                                            </p>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <button 
                        wire:click="resetSampleValues"
                        class="mt-4 px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition"
                    >
                        🔄 Réinitialiser les notes
                    </button>
                </div>
            @endif

            {{-- Calculation Results --}}
            @if($calculatedFields->count() > 0)
                <div class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg border border-green-200">
                    <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <span>📊 Résultats des Calculs</span>
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($calculatedFields as $field)
                            @php
                                $value = $calculations[$field->field_name] ?? 0;
                                $isValid = true;
                                
                                if ($field->min_value !== null && $value < $field->min_value) {
                                    $isValid = false;
                                }
                                if ($field->max_value !== null && $value > $field->max_value) {
                                    $isValid = false;
                                }
                            @endphp
                            
                            <div class="p-3 bg-white rounded border {{ $isValid ? 'border-green-300' : 'border-red-300' }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">{{ $field->field_label }}</p>
                                        @if($field->calculation_formula)
                                            <p class="text-xs text-gray-500">
                                                ƒ: {{ $field->calculation_formula }}
                                            </p>
                                        @endif
                                    </div>
                                    
                                    <div class="text-right">
                                        <p class="text-2xl font-bold {{ $isValid ? 'text-green-600' : 'text-red-600' }}">
                                            {{ number_format($value, 2, ',', ' ') }}
                                        </p>
                                        
                                        @if(!$isValid)
                                            <span class="text-xs text-red-600">⚠️ Hors limites</span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($field->min_value !== null || $field->max_value !== null)
                                    <div class="mt-2 text-xs text-gray-500">
                                        Plage: 
                                        {{ $field->min_value ?? '∞' }} 
                                        à 
                                        {{ $field->max_value ?? '∞' }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Validation Warnings/Errors --}}
            @if(count($validationErrors) > 0)
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <h4 class="text-md font-semibold text-red-800 mb-3">⚠️ Validations</h4>
                    
                    <ul class="space-y-2">
                        @foreach($validationErrors as $error)
                            <li class="flex items-start gap-2 text-sm">
                                <span class="text-red-600 font-bold mt-0.5">•</span>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $error['field'] }}</p>
                                    <p class="text-gray-600">{{ $error['message'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                @if($showCalculator && count($calculations) > 0)
                    <div class="p-4 bg-green-50 rounded-lg border border-green-200 text-center">
                        <p class="text-green-700 text-sm font-medium">✅ Tous les calculs sont valides</p>
                    </div>
                @endif
            @endif

            {{-- Summary Stats --}}
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-3xl font-bold text-blue-600">{{ $subjectFields->count() }}</p>
                    <p class="text-xs text-gray-600">Matières</p>
                </div>
                
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <p class="text-3xl font-bold text-purple-600">{{ $calculatedFields->count() }}</p>
                    <p class="text-xs text-gray-600">Calculs</p>
                </div>
                
                <div class="p-4 bg-amber-50 rounded-lg border border-amber-200">
                    <p class="text-3xl font-bold text-amber-600">{{ count($validationErrors) }}</p>
                    <p class="text-xs text-gray-600">Alertes</p>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Alpine.js integration for real-time updates
    document.addEventListener('livewire:update', () => {
        // Trigger any Alpine components to update
        window.Alpine && window.Alpine.flushAndStopDeferringMacros();
    });
</script>
@endpush
