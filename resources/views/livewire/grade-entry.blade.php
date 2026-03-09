<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Saisie des notes</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Classe: <span class="font-semibold">{{ $this->template->classroom->name }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-sm text-gray-600">
                        @if($this->lastSavedAt)
                        <span class="text-green-600 font-medium">
                            ✓ Sauvegardé {{ $this->lastSavedAt->diffForHumans() }}
                        </span>
                        @else
                        <span>Aucune sauvegarde</span>
                        @endif
                    </div>
                    @if($this->getProfessorPrincipal())
                    <button 
                        wire:click="exportToPDF" 
                        class="btn-primary text-sm">
                        ⬇ Exporter PDF
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-4 gap-6 mb-8">
            <!-- Filters & Stats Sidebar -->
            <div class="space-y-6">
                <!-- Subject Filter -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Matière</h3>
                    <select 
                        wire:model.live="subjectFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Toutes</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject['subject_id'] }}">
                            {{ $subject['subject_name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Statut</h3>
                    <select 
                        wire:model.live="filterStatus"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all">Tous</option>
                        <option value="draft">Brouillon</option>
                        <option value="partial">Partiel</option>
                        <option value="complete">Complète</option>
                    </select>
                </div>

                <!-- Class Statistics -->
                @if($stats)
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Statistiques</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <p class="text-gray-600">Total élèves</p>
                            <p class="text-xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <p class="text-gray-600">Moyenne classe</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $stats['average'] }}/20</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Min: {{ $stats['min'] }} | Max: {{ $stats['max'] }}
                            </p>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <p class="text-gray-600">Médiane</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $stats['median'] }}/20</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Search -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Recherche</h3>
                    <input 
                        wire:model.live="searchStudent"
                        type="text" 
                        placeholder="Nom d'élève..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Grades Table -->
            <div class="col-span-3">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <!-- Table Toolbar -->
                    <div class="border-b border-gray-200 p-4 bg-gray-50 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Bulletins ({{ $this->getBulletins()->total() }})</h2>
                        <div class="flex items-center gap-2">
                            <button 
                                wire:click="toggleSortDirection"
                                title="Changer l'ordre"
                                class="btn-secondary text-xs">
                                {{ $this->sortDir === 'asc' ? '↑' : '↓' }}
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th 
                                        wire:click="$set('sortBy', 'name')"
                                        class="px-4 py-3 text-left font-semibold text-gray-900 cursor-pointer hover:bg-gray-100">
                                        Élève
                                        @if($this->sortBy === 'name') 
                                            {{ $this->sortDir === 'asc' ? '↑' : '↓' }}
                                        @endif
                                    </th>
                                    <th 
                                        width="120"
                                        class="px-4 py-3 text-center font-semibold text-gray-900">
                                        Note Classe
                                    </th>
                                    <th 
                                        width="120"
                                        class="px-4 py-3 text-center font-semibold text-gray-900">
                                        Note Compo
                                    </th>
                                    <th 
                                        width="100"
                                        class="px-4 py-3 text-right font-semibold text-gray-900">
                                        Moyenne
                                    </th>
                                    <th 
                                        width="80"
                                        class="px-4 py-3 text-center font-semibold text-gray-900">
                                        Rang
                                    </th>
                                    <th 
                                        width="120"
                                        class="px-4 py-3 text-center font-semibold text-gray-900">
                                        Appréciation
                                    </th>
                                    <th width="80" class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($this->getBulletins() as $bulletin)
                                <tr class="hover:bg-gray-50 transition-colors {{ $bulletin->is_locked ? 'bg-yellow-50' : '' }}">
                                    <!-- Student Info -->
                                    <td class="px-4 py-4">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $bulletin->student->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $bulletin->student->matricule ?? '-' }}</p>
                                        </div>
                                    </td>

                                    <!-- Note Classe (editable by teacher) -->
                                    <td class="px-4 py-4 text-center">
                                        @php
                                        $grade = $bulletin->grades->where('subject_id', $this->selectedSubjectId)->first();
                                        @endphp

                                        @if($grade && $this->canEditGrade($grade))
                                        <input 
                                            wire:blur="updateNoteClasse({{ $grade->id }}, $event.target.value)"
                                            type="number" 
                                            value="{{ $grade->note_classe ?? '' }}"
                                            min="0" 
                                            max="20" 
                                            step="0.5"
                                            placeholder="-"
                                            class="w-full px-2 py-1 text-center border border-blue-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        @elseif($grade)
                                        <span class="text-gray-900 font-medium">{{ $grade->note_classe ?? '-' }}</span>
                                        @else
                                        <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Note Composition (editable by teacher) -->
                                    <td class="px-4 py-4 text-center">
                                        @if($grade && $this->canEditGrade($grade))
                                        <input 
                                            wire:blur="updateNoteComposition({{ $grade->id }}, $event.target.value)"
                                            type="number" 
                                            value="{{ $grade->note_composition ?? '' }}"
                                            min="0" 
                                            max="20" 
                                            step="0.5"
                                            placeholder="-"
                                            class="w-full px-2 py-1 text-center border border-blue-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        @elseif($grade)
                                        <span class="text-gray-900 font-medium">{{ $grade->note_composition ?? '-' }}</span>
                                        @else
                                        <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Average -->
                                    <td class="px-4 py-4 text-right">
                                        @if($grade)
                                        <span class="font-semibold {{ $grade->average >= 10 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $grade->average ? number_format($grade->average, 2) : '-' }}
                                        </span>
                                        @else
                                        <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Rank -->
                                    <td class="px-4 py-4 text-center">
                                        @if($bulletin->class_rank)
                                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded font-semibold text-sm">
                                            #{{ $bulletin->class_rank }}
                                        </span>
                                        @else
                                        <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Appreciation -->
                                    <td class="px-4 py-4 text-center">
                                        @php
                                        $appreciation = $this->getAppreciation($bulletin);
                                        @endphp
                                        <span class="text-sm text-gray-600">{{ $appreciation }}</span>
                                    </td>

                                    <!-- Lock Button -->
                                    <td class="px-4 py-4 text-right">
                                        @if($this->isProfessorPrincipal())
                                        <button 
                                            wire:click="toggleLock({{ $bulletin->id }})"
                                            title="{{ $bulletin->is_locked ? 'Déverrouiller' : 'Verrouiller' }}"
                                            class="text-lg {{ $bulletin->is_locked ? 'text-yellow-600' : 'text-gray-400' }} hover:text-gray-600 transition">
                                            {{ $bulletin->is_locked ? '🔒' : '🔓' }}
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                        Aucun bulletin trouvé
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="border-t border-gray-200 px-4 py-4 bg-gray-50">
                        {{ $this->getBulletins()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-save Toast -->
    @if($this->showSaveIndicator)
    <div wire:init="$set('showSaveIndicator', false)" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg">
        ✓ Sauvegardé
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('livewire:navigating', () => {
            const unsaved = Object.keys(@js($this->unsavedChanges));
            if (unsaved.length > 0) {
                if (!confirm('Vous avez des changements non sauvegardés. Êtes-vous sûr?')) {
                    event.preventDefault();
                }
            }
        });

        // Auto-hide save indicator after 3 seconds
        $wire.on('saved', ({ message }) => {
            const toast = document.createElement('div');
            toast.innerHTML = `
                <div class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg">
                    ✓ ${message}
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .btn-primary, .btn-secondary {
            @apply px-3 py-2 rounded-lg font-medium transition-colors text-sm;
        }

        .btn-primary {
            @apply bg-blue-600 text-white hover:bg-blue-700;
        }

        .btn-secondary {
            @apply bg-gray-200 text-gray-900 hover:bg-gray-300;
        }

        input[type="number"] {
            @apply font-mono;
        }

        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            @apply opacity-100 h-6;
        }
    </style>
    @endpush
</div>
