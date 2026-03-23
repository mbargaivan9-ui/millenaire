{{--
    resources/views/teacher/bulletin_ng/partials/wizard_header.blade.php
    En-tête du wizard avec indicateur d'étapes
--}}
@php
    $steps = [
        1 => ['icon' => '🌐', 'label' => $config->langue === 'EN' ? 'Section' : 'Section'],
        2 => ['icon' => '⚙️', 'label' => $config->langue === 'EN' ? 'Config' : 'Config'],
        3 => ['icon' => '📚', 'label' => $config->langue === 'EN' ? 'Subjects' : 'Matières'],
        4 => ['icon' => '👨‍🎓', 'label' => $config->langue === 'EN' ? 'Students' : 'Élèves'],
        5 => ['icon' => '✏️', 'label' => $config->langue === 'EN' ? 'Grades' : 'Notes'],
        6 => ['icon' => '🧑‍💼', 'label' => $config->langue === 'EN' ? 'Conduct' : 'Conduite'],
        7 => ['icon' => '🎓', 'label' => $config->langue === 'EN' ? 'Reports' : 'Bulletins'],
    ];
@endphp

<div class="bng-wizard-header">
    <div class="bng-wizard-steps">
        @foreach($steps as $num => $step)
            <div class="bng-step {{ $num < $currentStep ? 'completed' : ($num == $currentStep ? 'active' : 'pending') }}">
                <div class="bng-step-circle">
                    @if($num < $currentStep)
                        <span>✓</span>
                    @else
                        <span>{{ $step['icon'] }}</span>
                    @endif
                </div>
                <div class="bng-step-label">{{ $step['label'] }}</div>
            </div>
            @if($num < 7)
                <div class="bng-step-line {{ $num < $currentStep ? 'done' : '' }}"></div>
            @endif
        @endforeach
    </div>

    <div class="bng-wizard-meta">
        @if(isset($config) && $config->nom_classe)
            <span class="bng-badge bng-badge-primary">
                {{ $config->langue === 'EN' ? 'Class' : 'Classe' }}: {{ $config->nom_classe }}
            </span>
        @endif
        @if(isset($config) && $config->trimestre)
            <span class="bng-badge bng-badge-warning">
                {{ $config->trimestre_label }}
            </span>
        @endif
        @if(isset($config) && $config->langue)
            <span class="bng-badge bng-badge-info">
                {{ $config->langue === 'FR' ? '🇫🇷 Francophone' : '🇬🇧 Anglophone' }}
            </span>
        @endif
    </div>
</div>
