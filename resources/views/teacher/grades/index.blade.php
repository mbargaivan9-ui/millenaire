@extends('layouts.app')

@section('title', 'Saisie des Notes')

@section('content')

{{-- Header --}}
<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#8B5CF6,#7C3AED)">
                <i data-lucide="pen-tool"></i>
            </div>
            <div>
                <h1 class="page-title">Saisie des Notes</h1>
                <p class="page-subtitle text-muted">Enregistrez les notes des élèves pour chaque classe et matière</p>
            </div>
        </div>
    </div>
</div>

    {{-- Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <x-ui.card>
                <form method="GET" class="row g-3" id="gradeForm">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Classe</label>
                        <select class="form-select rounded-3 select2" name="class_id" required>
                            <option value="">Sélectionner une classe...</option>
                            @foreach($myClasses ?? [] as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} - Niveau {{ $class->level }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Matière</label>
                        <select class="form-select rounded-3 select2" name="subject_id" required>
                            <option value="">Sélectionner une matière...</option>
                            @foreach($subjects ?? [] as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fas fa-search me-2"></i>Charger Élèves
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>

    {{-- Grades Table --}}
    @if(request('class_id') && request('subject_id'))
    <div class="row">
        <div class="col-12">
            <x-ui.card title="Saisie des Notes" subtitle="Double-cliquez pour éditer une note">
                <form method="POST" action="{{ route('teacher.grades.entry.save', $classSubjectTeacher->id ?? '#') }}" id="gradesForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40%">Élève</th>
                                    <th class="text-center">Note Devoir</th>
                                    <th class="text-center">Note Classe</th>
                                    <th class="text-center">Examen</th>
                                    <th class="text-center">Moyenne</th>
                                    <th class="text-center">État</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students ?? [] as $student)
                                @php
                                $grades = $student->grades->where('class_subject_teacher_id', $classSubjectTeacher->id ?? null)->first();
                                $average = ($grades->homework ?? 0 + $grades->classwork ?? 0 + $grades->exam ?? 0) / 3;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $student->user->avatar_url }}" 
                                                 class="rounded-circle" width="32" height="32" alt="{{ $student->user->name }}" style="object-fit:cover">
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $student->user->name }}</h6>
                                                <small class="text-muted">{{ $student->matricule }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" class="form-control form-control-sm rounded-2 text-center" 
                                               name="grades[{{ $student->id }}][homework]" 
                                               value="{{ $grades->homework ?? '' }}" 
                                               min="0" max="20" step="0.5" placeholder="-">
                                    </td>
                                    <td class="text-center">
                                        <input type="number" class="form-control form-control-sm rounded-2 text-center" 
                                               name="grades[{{ $student->id }}][classwork]" 
                                               value="{{ $grades->classwork ?? '' }}" 
                                               min="0" max="20" step="0.5" placeholder="-">
                                    </td>
                                    <td class="text-center">
                                        <input type="number" class="form-control form-control-sm rounded-2 text-center" 
                                               name="grades[{{ $student->id }}][exam]" 
                                               value="{{ $grades->exam ?? '' }}" 
                                               min="0" max="20" step="0.5" placeholder="-">
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-primary">{{ number_format($average, 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @if($average >= 12)
                                            <x-ui.badge variant="success">Bon</x-ui.badge>
                                        @elseif($average >= 10)
                                            <x-ui.badge variant="warning">Moyen</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="danger">Faible</x-ui.badge>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Sélectionnez d'abord une classe et une matière
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Actions --}}
                    @if($students ?? false)
                    <div class="mt-4 d-flex gap-2 justify-content-end">
                        <a href="{{ route('teacher.dashboard') }}" class="btn btn-light rounded-3">
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fas fa-save me-2"></i>Enregistrer les Notes
                        </button>
                    </div>
                    @endif
                </form>
            </x-ui.card>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Validation et calcul automatique
            $('input[type="number"]').on('change', function() {
                const row = $(this).closest('tr');
                const hw = parseFloat(row.find('input[name*="[homework]"]').val()) || 0;
                const cw = parseFloat(row.find('input[name*="[classwork]"]').val()) || 0;
                const ex = parseFloat(row.find('input[name*="[exam]"]').val()) || 0;
                const average = (hw + cw + ex) / 3;
                
                row.find('strong.text-primary').text(average.toFixed(2));
                
                // Update status badge
                const status = row.find('td:last').find('span');
                if (average >= 12) {
                    status.removeClass('bg-warning bg-danger').addClass('bg-success').text('Bon');
                } else if (average >= 10) {
                    status.removeClass('bg-success bg-danger').addClass('bg-warning').text('Moyen');
                } else {
                    status.removeClass('bg-success bg-warning').addClass('bg-danger').text('Faible');
                }
            });
        });
    </script>
    @endpush

@endsection
