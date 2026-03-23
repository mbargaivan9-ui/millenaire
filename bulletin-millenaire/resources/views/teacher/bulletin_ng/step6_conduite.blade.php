@extends('layouts.app')

@section('title', 'Bulletin NG — Conduite')

@php $currentStep = 6; $isEN = $config->langue === 'EN'; @endphp

@section('content')
<div class="bng-page">

    <div class="bng-page-header">
        <div class="bng-page-header-inner">
            <div class="bng-page-icon">🧑‍💼</div>
            <div>
                <h1 class="bng-page-title">{{ $isEN ? 'Step 6 — Conduct & Behavior' : 'Étape 6 — Conduite & Comportement' }}</h1>
                <p class="bng-page-subtitle">{{ $config->nom_classe }} | {{ $config->trimestre_label }}</p>
            </div>
        </div>
    </div>

    @include('teacher.bulletin_ng.partials.wizard_header')

    <form action="{{ route('teacher.bulletin_ng.finaliser-conduite', $config->id) }}" method="POST" id="conduiteForm">
        @csrf

        {{-- Liste des élèves avec formulaire conduite --}}
        <div class="bng-conduites-list">
            @foreach($students as $si => $student)
                @php
                    $cond = $conduites->get($student->id);
                    $avg  = $stats['avgs'][$student->id] ?? 0;
                    $rank = $stats['ranks'][$student->id] ?? '-';
                @endphp
                <div class="bng-card bng-conduite-card" data-student-id="{{ $student->id }}">
                    {{-- En-tête élève --}}
                    <div class="bng-conduite-header">
                        <div class="bng-conduite-num">{{ $si + 1 }}</div>
                        <div class="bng-conduite-info">
                            <div class="bng-conduite-name">{{ $student->nom }}</div>
                            <div class="bng-conduite-meta">{{ $student->matricule }}</div>
                        </div>
                        <div class="bng-conduite-scores">
                            <span class="bng-badge {{ $avg >= 10 ? 'bng-badge-success' : 'bng-badge-danger' }}">
                                {{ $isEN ? 'Avg' : 'Moy' }}: {{ number_format($avg, 2) }}
                            </span>
                            <span class="bng-badge bng-badge-primary">
                                {{ $isEN ? 'Rank' : 'Rang' }}: {{ $rank }}/{{ $students->count() }}
                            </span>
                        </div>
                    </div>

                    <div class="bng-conduite-body">
                        {{-- Travail --}}
                        <div class="bng-conduite-section">
                            <div class="bng-conduite-section-title bng-cs-travail">
                                📚 {{ $isEN ? 'Work' : 'Travail' }}
                            </div>
                            <div class="bng-conduite-fields">

                                @php $yesNo = $isEN ? ['Yes','No'] : ['Oui','Non']; @endphp

                                <div class="bng-cfield">
                                    <label>{{ $isEN ? 'Honor Roll' : "Tableau d'Honneur" }}</label>
                                    <div class="bng-toggle-group">
                                        <label class="bng-toggle {{ ($cond->tableau_honneur ?? false) ? 'active' : '' }}">
                                            <input type="radio" name="conduct[{{ $student->id }}][tableau_honneur]" value="1"
                                                   {{ ($cond->tableau_honneur ?? false) ? 'checked' : '' }}>
                                            {{ $yesNo[0] }}
                                        </label>
                                        <label class="bng-toggle {{ !($cond->tableau_honneur ?? false) ? 'active' : '' }}">
                                            <input type="radio" name="conduct[{{ $student->id }}][tableau_honneur]" value="0"
                                                   {{ !($cond->tableau_honneur ?? true) ? 'checked' : '' }}>
                                            {{ $yesNo[1] }}
                                        </label>
                                    </div>
                                </div>

                                <div class="bng-cfield">
                                    <label>{{ $isEN ? 'Encouragement' : 'Encouragement' }}</label>
                                    <div class="bng-toggle-group">
                                        <label class="bng-toggle {{ ($cond->encouragement ?? false) ? 'active' : '' }}">
                                            <input type="radio" name="conduct[{{ $student->id }}][encouragement]" value="1"
                                                   {{ ($cond->encouragement ?? false) ? 'checked' : '' }}>{{ $yesNo[0] }}
                                        </label>
                                        <label class="bng-toggle {{ !($cond->encouragement ?? false) ? 'active' : '' }}">
                                            <input type="radio" name="conduct[{{ $student->id }}][encouragement]" value="0"
                                                   {{ !($cond->encouragement ?? true) ? 'checked' : '' }}>{{ $yesNo[1] }}
                                        </label>
                                    </div>
                                </div>

                                <div class="bng-cfield">
                                    <label>{{ $isEN ? 'Congratulations' : 'Félicitations' }}</label>
                                    <div class="bng-toggle-group">
                                        <label class="bng-toggle {{ ($cond->felicitations ?? false) ? 'active' : '' }}">
                                            <input type="radio" name="conduct[{{ $student->id }}][felicitations]" value="1"
                                                   {{ ($cond->felicitations ?? false) ? 'checked' : '' }}>{{ $yesNo[0] }}
                                        </label>
                                        <label class="bng-toggle {{ !($cond->felicitations ?? false) ? 'active' : '' }}">
                                            <input type="radio" name="conduct[{{ $student->id }}][felicitations]" value="0"
                                                   {{ !($cond->felicitations ?? true) ? 'checked' : '' }}>{{ $yesNo[1] }}
                                        </label>
                                    </div>
                                </div>

                                <div class="bng-cfield">
                                    <label>{{ $isEN ? 'Work Blame' : 'Blame Travail' }}</label>
                                    <div class="bng-toggle-group">
                                        <label class="bng-toggle {{ ($cond->blame_travail ?? false) ? 'active' : '' }}">
                                            <input type="radio" name="conduct[{{ $student->id }}][blame_travail]" value="1"
                                                   {{ ($cond->blame_travail ?? false) ? 'checked' : '' }}>{{ $yesNo[0] }}
                                        </label>
                                        <label class="bng-toggle {{ !($cond->blame_travail ?? false) ? 'active' : '' }}">
                                            <input type="radio" name="conduct[{{ $student->id }}][blame_travail]" value="0"
                                                   {{ !($cond->blame_travail ?? true) ? 'checked' : '' }}>{{ $yesNo[1] }}
                                        </label>
                                    </div>
                                </div>

                                <div class="bng-cfield">
                                    <label>{{ $isEN ? 'Work Warning' : 'Avert. Travail' }}</label>
                                    <select name="conduct[{{ $student->id }}][avert_travail]" class="bng-select bng-select-sm">
                                        <option value="Non" {{ ($cond->avert_travail ?? 'Non') === 'Non' ? 'selected' : '' }}>Non</option>
                                        <option value="Oui" {{ ($cond->avert_travail ?? '') === 'Oui' ? 'selected' : '' }}>Oui</option>
                                    </select>
                                </div>

                            </div>
                        </div>

                        {{-- Conduite --}}
                        <div class="bng-conduite-section">
                            <div class="bng-conduite-section-title bng-cs-conduite">
                                🧑‍💼 {{ $isEN ? 'Conduct' : 'Conduite' }}
                            </div>
                            <div class="bng-conduite-fields">

                                <div class="bng-cfield">
                                    <label>{{ $isEN ? 'Total Absences (h)' : 'Absences Totales (h)' }}</label>
                                    <input type="number" min="0" class="bng-input bng-input-sm"
                                           name="conduct[{{ $student->id }}][absences_totales]"
                                           value="{{ $cond->absences_totales ?? 0 }}" style="width:80px;">
                                </div>

                                <div class="bng-cfield">
                                    <label>{{ $isEN ? 'Unjustified Abs. (h)' : 'Absences NJ (h)' }}</label>
                                    <input type="number" min="0" class="bng-input bng-input-sm"
                                           name="conduct[{{ $student->id }}][absences_nj]"
                                           value="{{ $cond->absences_nj ?? 0 }}" style="width:80px;">
                                </div>

                                <div class="bng-cfield">
                                    <label>{{ $isEN ? 'Exclusion' : 'Exclusion' }}</label>
                                    <div class="bng-toggle-group">
                                        <label class="bng-toggle {{ ($cond->exclusion ?? false) ? 'active' : '' }}">
                                            <input type="radio" name="conduct[{{ $student->id }}][exclusion]" value="1"
                                                   {{ ($cond->exclusion ?? false) ? 'checked' : '' }}>{{ $yesNo[0] }}
                                        </label>
                                        <label class="bng-toggle {{ !($cond->exclusion ?? false) ? 'active' : '' }}">
                                            <input type="radio" name="conduct[{{ $student->id }}][exclusion]" value="0"
                                                   {{ !($cond->exclusion ?? true) ? 'checked' : '' }}>{{ $yesNo[1] }}
                                        </label>
                                    </div>
                                </div>

                                <div class="bng-cfield">
                                    <label>{{ $isEN ? 'Conduct Warning' : 'Aver. Conduite' }}</label>
                                    <select name="conduct[{{ $student->id }}][avert_conduite]" class="bng-select bng-select-sm">
                                        <option value="Non" {{ ($cond->avert_conduite ?? 'Non') === 'Non' ? 'selected' : '' }}>Non</option>
                                        <option value="Oui" {{ ($cond->avert_conduite ?? '') === 'Oui' ? 'selected' : '' }}>Oui</option>
                                    </select>
                                </div>

                                <div class="bng-cfield">
                                    <label>{{ $isEN ? 'Conduct Blame' : 'Blame Conduite' }}</label>
                                    <select name="conduct[{{ $student->id }}][blame_conduite]" class="bng-select bng-select-sm">
                                        <option value="Non" {{ ($cond->blame_conduite ?? 'Non') === 'Non' ? 'selected' : '' }}>Non</option>
                                        <option value="Oui" {{ ($cond->blame_conduite ?? '') === 'Oui' ? 'selected' : '' }}>Oui</option>
                                    </select>
                                </div>

                            </div>
                        </div>

                    </div>{{-- /bng-conduite-body --}}
                </div>
            @endforeach
        </div>

        <div class="bng-form-actions" style="margin-top: 24px;">
            <a href="{{ route('teacher.bulletin_ng.step5', $config->id) }}" class="bng-btn bng-btn-secondary">
                ← {{ $isEN ? 'Back' : 'Retour' }}
            </a>
            <button type="submit" class="bng-btn bng-btn-primary bng-btn-lg">
                ✅ {{ $isEN ? 'Save & Generate Report Cards →' : 'Enregistrer & Générer les Bulletins →' }}
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
// Toggle radio buttons styling
document.querySelectorAll('.bng-toggle-group').forEach(group => {
    group.querySelectorAll('.bng-toggle').forEach(label => {
        label.querySelector('input').addEventListener('change', function () {
            group.querySelectorAll('.bng-toggle').forEach(l => l.classList.remove('active'));
            label.classList.add('active');
        });
    });
});
</script>
@endpush

@endsection
