{{-- resources/views/livewire/bulletin/grade-input.blade.php --}}
<div class="space-y-4" x-data="{ spreadsheet: $wire.entangle('showSpreadsheetMode') }">

    {{-- ══════════════════════════════════════════════════════════
         BARRE DE CONTRÔLE SUPÉRIEURE
    ══════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">

            {{-- Recherche instantanée --}}
            <div class="relative flex-1">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchQuery"
                    placeholder="🔍 Rechercher par nom, prénom ou matricule..."
                    class="w-full pl-4 pr-10 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                >
                @if($searchQuery)
                    <button wire:click="$set('searchQuery', '')" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                @endif
            </div>

            {{-- Navigation élève --}}
            <div class="flex items-center gap-3">
                <button
                    wire:click="previousStudent"
                    @if($currentStudentIndex === 0) disabled @endif
                    class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                </button>

                <span class="text-sm font-medium text-gray-700 min-w-max">
                    <span class="text-indigo-600 font-bold">{{ $currentStudentIndex + 1 }}</span>
                    / {{ $this->students->count() }} élèves
                </span>

                <button
                    wire:click="nextStudent"
                    @if($currentStudentIndex >= $this->students->count() - 1) disabled @endif
                    class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            {{-- Mode tableur (prof principal uniquement) --}}
            @if($this->isPrincipal)
            <button
                x-on:click="spreadsheet = !spreadsheet"
                class="flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 text-sm font-medium transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M3 14h18M10 3v18M14 3v18M3 6h18v12H3z"/></svg>
                <span x-text="spreadsheet ? 'Vue bulletin' : 'Mode tableur'"></span>
            </button>
            @endif

            {{-- Dernière sauvegarde --}}
            @if($lastSavedAt)
            <span class="text-xs text-green-600 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                Sauvegardé à {{ $lastSavedAt }}
            </span>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         VUE BULLETIN INDIVIDUEL
    ══════════════════════════════════════════════════════════ --}}
    @if($currentBulletin && !$showSpreadsheetMode)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- En-tête élève --}}
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    @if($currentBulletin->student->photo)
                        <img src="{{ Storage::url($currentBulletin->student->photo) }}"
                             class="w-12 h-12 rounded-full border-2 border-white/50 object-cover">
                    @else
                        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-lg">
                            {{ substr($currentBulletin->student->first_name, 0, 1) }}{{ substr($currentBulletin->student->last_name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="text-white font-bold text-lg">
                            {{ $currentBulletin->student->first_name }} {{ $currentBulletin->student->last_name }}
                        </h3>
                        <p class="text-indigo-200 text-sm">
                            Matricule : {{ $currentBulletin->student->matricule ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-white/80 text-xs">Complétion</div>
                    <div class="text-white font-bold text-xl">{{ $currentBulletin->completion_rate }}%</div>
                    <div class="mt-1 w-24 bg-white/20 rounded-full h-1.5">
                        <div class="bg-white rounded-full h-1.5 transition-all"
                             style="width: {{ $currentBulletin->completion_rate }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table des notes --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Matière</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">Coeff.</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Note /20</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">Absent</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Appréciation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($this->currentGrades as $grade)
                        @php
                            $isEditable = $this->isPrincipal || $grade->teacher_id === $this->currentTeacherId;
                            $isLocked   = $grade->is_locked || $currentBulletin->is_locked;
                            $canEdit    = $isEditable && !$isLocked;
                        @endphp

                        <tr class="transition {{ $isEditable ? 'hover:bg-indigo-50/30' : '' }} {{ $isEditable ? '' : 'opacity-75' }}">

                            {{-- Matière --}}
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    @if($isEditable)
                                        <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                                    @else
                                        <div class="w-2 h-2 rounded-full bg-gray-200"></div>
                                    @endif
                                    <span class="font-medium text-gray-800 text-sm">{{ $grade->subject->name }}</span>
                                    @if($grade->teacher->user ?? null)
                                        <span class="text-xs text-gray-400">({{ $grade->teacher->user->name }})</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Coefficient --}}
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm font-semibold text-gray-700">{{ $grade->coefficient }}</span>
                            </td>

                            {{-- Note --}}
                            <td class="px-4 py-3 text-center">
                                @if($canEdit)
                                    <input
                                        type="number"
                                        wire:model="gradeValues.{{ $grade->id }}"
                                        wire:change="saveGrade({{ $grade->id }})"
                                        step="0.25"
                                        min="0"
                                        max="20"
                                        placeholder="–"
                                        @if($absentValues[$grade->id] ?? false) disabled @endif
                                        class="w-20 text-center border-2 rounded-lg py-1.5 text-sm font-bold
                                               focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400
                                               {{ ($absentValues[$grade->id] ?? false) ? 'bg-gray-100 text-gray-400' : 'border-indigo-200 bg-indigo-50 focus:bg-white' }}"
                                    >
                                    @error("grade_{$grade->id}")
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                @else
                                    <span class="text-sm font-bold {{ $grade->grade !== null ? 'text-gray-800' : 'text-gray-300' }}">
                                        {{ $grade->display_grade }}
                                    </span>
                                @endif
                            </td>

                            {{-- Absent --}}
                            <td class="px-4 py-3 text-center">
                                @if($canEdit)
                                    <input
                                        type="checkbox"
                                        wire:model="absentValues.{{ $grade->id }}"
                                        wire:change="saveGrade({{ $grade->id }})"
                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                @else
                                    @if($grade->absent)
                                        <span class="text-xs text-red-500 font-medium">Absent</span>
                                    @endif
                                @endif
                            </td>

                            {{-- Appréciation + suggestion IA --}}
                            <td class="px-4 py-3">
                                @if($canEdit)
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="text"
                                            wire:model="appreciationValues.{{ $grade->id }}"
                                            wire:change="saveGrade({{ $grade->id }})"
                                            placeholder="Appréciation..."
                                            class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
                                        >

                                        {{-- Bouton suggestion IA --}}
                                        @if($gradeValues[$grade->id] ?? null)
                                            <button
                                                wire:click="requestAiSuggestion({{ $grade->id }})"
                                                title="Suggestion IA"
                                                class="p-1.5 rounded-lg bg-purple-100 hover:bg-purple-200 text-purple-700 transition"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Suggestion IA affichée en gris --}}
                                    @if(isset($aiSuggestions[$grade->id]))
                                        <div class="mt-1.5 flex items-center gap-2 p-2 bg-purple-50 border border-purple-200 rounded-lg">
                                            <span class="text-xs text-purple-600 italic flex-1">✨ {{ $aiSuggestions[$grade->id] }}</span>
                                            <button
                                                wire:click="acceptAiSuggestion({{ $grade->id }})"
                                                class="text-xs px-2 py-0.5 bg-purple-600 text-white rounded hover:bg-purple-700 transition"
                                            >Accepter</button>
                                            <button
                                                wire:click="$set('aiSuggestions.{{ $grade->id }}', null)"
                                                class="text-xs text-purple-400 hover:text-purple-600"
                                            >✕</button>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-sm text-gray-500 italic">{{ $grade->teacher_appreciation ?: '–' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pied : moyennes calculées --}}
        <div class="bg-gray-50 border-t border-gray-100 px-6 py-4">
            <div class="flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Moyenne élève :</span>
                    <span class="font-bold text-lg text-indigo-700">
                        {{ $currentBulletin->student_average !== null ? number_format($currentBulletin->student_average, 2) . '/20' : '–' }}
                    </span>
                </div>
                @if($currentBulletin->appreciation)
                    <span class="px-3 py-1 rounded-full text-sm font-semibold"
                          style="background-color: {{ $currentBulletin->appreciation_color }}22; color: {{ $currentBulletin->appreciation_color }}">
                        {{ $currentBulletin->appreciation }}
                    </span>
                @endif
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Rang :</span>
                    <span class="font-bold text-gray-700">{{ $currentBulletin->getRankLabel() }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Moy. classe :</span>
                    <span class="font-semibold text-gray-700">
                        {{ $currentBulletin->class_average !== null ? number_format($currentBulletin->class_average, 2) . '/20' : '–' }}
                    </span>
                </div>
            </div>

            {{-- Observation du prof principal --}}
            @if($this->isPrincipal)
                <div class="mt-3">
                    <textarea
                        wire:model.lazy="currentBulletin.principal_comment"
                        placeholder="Observation du professeur principal..."
                        rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent resize-none"
                    ></textarea>
                </div>
            @endif
        </div>
    </div>

    {{-- Liste des élèves (navigation rapide) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <h4 class="text-sm font-semibold text-gray-600 mb-3">Navigation rapide</h4>
        <div class="flex flex-wrap gap-2">
            @foreach($this->students as $index => $bulletin)
                <button
                    wire:click="goToStudent({{ $index }})"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition
                           {{ $index === $currentStudentIndex ? 'bg-indigo-600 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}
                           {{ $bulletin->completion_rate === 100 ? 'ring-2 ring-green-400' : '' }}"
                >
                    {{ $bulletin->student->first_name }} {{ substr($bulletin->student->last_name, 0, 1) }}.
                    @if($bulletin->completion_rate === 100)
                        ✓
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    @endif {{-- fin vue bulletin individuel --}}


    {{-- ══════════════════════════════════════════════════════════
         MODE TABLEUR (Prof Principal uniquement)
    ══════════════════════════════════════════════════════════ --}}
    @if($showSpreadsheetMode && $this->isPrincipal)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">📊 Mode Tableur — Saisie rapide</h3>
                <span class="text-xs text-gray-400">Navigation : Tab pour passer à la cellule suivante</span>
            </div>

            <div class="overflow-x-auto">
                @php $subjects = $this->students->first()?->grades->map->subject ?? collect(); @endphp
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase sticky left-0 bg-gray-50 z-10 min-w-[150px]">Élève</th>
                            @foreach($this->students->first()?->grades ?? [] as $grade)
                                <th class="px-3 py-2.5 text-xs font-semibold text-gray-500 uppercase text-center min-w-[90px]">
                                    {{ Str::limit($grade->subject->name, 12) }}
                                    <br><span class="text-gray-400 font-normal">×{{ $grade->coefficient }}</span>
                                </th>
                            @endforeach
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-500 uppercase text-center min-w-[80px]">Moy.</th>
                            <th class="px-3 py-2.5 text-xs font-semibold text-gray-500 uppercase text-center min-w-[60px]">Rang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($this->students as $index => $bulletin)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50/50' }} hover:bg-indigo-50/20 transition">
                                <td class="px-4 py-2 font-medium text-gray-800 sticky left-0 {{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50/50' }}">
                                    {{ $bulletin->student->first_name }} {{ $bulletin->student->last_name }}
                                </td>
                                @foreach($bulletin->grades as $grade)
                                    <td class="px-2 py-2 text-center">
                                        <input
                                            type="number"
                                            wire:model="gradeValues.{{ $grade->id }}"
                                            wire:change="saveGrade({{ $grade->id }})"
                                            step="0.25" min="0" max="20"
                                            placeholder="–"
                                            class="w-16 text-center border border-gray-200 rounded px-1 py-1 text-sm font-medium focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400 hover:border-indigo-300"
                                        >
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center font-bold text-indigo-700">
                                    {{ $bulletin->student_average !== null ? number_format($bulletin->student_average, 2) : '–' }}
                                </td>
                                <td class="px-3 py-2 text-center text-gray-600">
                                    {{ $bulletin->rank ? $bulletin->rank . 'e' : '–' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Loading overlay Livewire --}}
    <div wire:loading.flex class="fixed inset-0 bg-black/20 z-50 items-center justify-center">
        <div class="bg-white rounded-xl px-6 py-4 shadow-xl flex items-center gap-3">
            <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <span class="text-sm font-medium text-gray-700">Sauvegarde en cours...</span>
        </div>
    </div>

</div>
