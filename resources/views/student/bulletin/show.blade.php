@php
    $isFr = auth()->user()->language ?? 'fr' === 'fr';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-size-h2 font-weight-bolder text-dark mb-0">
                {{ $bulletin->classe->name }} - {{ $isFr ? 'Trimestre' : 'Term' }} {{ $bulletin->term }}, {{ $isFr ? 'Séquence' : 'Sequence' }} {{ $bulletin->sequence }}
            </h2>
            <a href="{{ route('student.bulletins.pdf', $bulletin->id) }}" class="btn btn-primary">
                <i class="fas fa-download"></i> {{ $isFr ? 'Télécharger PDF' : 'Download PDF' }}
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-bottom">
                <h4 class="mb-0">{{ $isFr ? 'Détails du Bulletin' : 'Bulletin Details' }}</h4>
            </div>
            <div class="card-body">
                <!-- Student Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">{{ $isFr ? 'Étudiant' : 'Student' }}</p>
                        <h5 class="mb-0 text-dark font-weight-bold">
                            {{ $bulletin->student->user->full_name ?? 'N/A' }}
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">{{ $isFr ? 'Classe' : 'Class' }}</p>
                        <h5 class="mb-0 text-dark font-weight-bold">
                            {{ $bulletin->classe->name ?? 'N/A' }}
                        </h5>
                    </div>
                </div>

                <!-- Marks Table -->
                @if($bulletin->marks && $bulletin->marks->count())
                    <h5 class="mb-3 font-weight-bold">{{ $isFr ? 'Notes' : 'Grades' }}</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="bg-light">
                            <tr>
                                <th>{{ $isFr ? 'Matière' : 'Subject' }}</th>
                                <th class="text-center">{{ $isFr ? 'Note' : 'Grade' }}</th>
                                <th class="text-center">{{ $isFr ? 'Moyenne Classe' : 'Class Average' }}</th>
                                <th class="text-center">{{ $isFr ? 'Position' : 'Rank' }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($bulletin->marks as $mark)
                                <tr>
                                    <td class="fw-500">{{ $mark->subject->name ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ number_format($mark->value ?? 0, 2) }}/20</span>
                                    </td>
                                    <td class="text-center">{{ number_format($mark->class_average ?? 0, 2) }}/20</td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $mark->rank ?? '-' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Comments Section -->
                @if($bulletin->comments)
                    <h5 class="mb-3 mt-4 font-weight-bold">{{ $isFr ? 'Observations' : 'Comments' }}</h5>
                    <div class="bg-light p-3 rounded">
                        {!! nl2br(e($bulletin->comments)) !!}
                    </div>
                @endif

                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">{{ $isFr ? 'Moyenne Générale' : 'Overall Average' }}</h6>
                                <h3 class="text-primary font-weight-bold">
                                    {{ number_format($bulletin->overall_average ?? 0, 2) }}/20
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">{{ $isFr ? 'Moyenne Classe' : 'Class Average' }}</h6>
                                <h3 class="text-info font-weight-bold">
                                    {{ number_format($classMoyenne ?? 0, 2) }}/20
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">{{ $isFr ? 'Étudiants en Classe' : 'Students in Class' }}</h6>
                                <h3 class="text-success font-weight-bold">{{ $totalStudents }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="{{ route('student.bulletins.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> {{ $isFr ? 'Retour' : 'Back' }}
            </a>
        </div>
    </div>
</x-app-layout>
