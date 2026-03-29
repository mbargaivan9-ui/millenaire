@extends('layouts.app')

@section('title', 'Saisie des Notes - Bulletins')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2">📝 Saisie des Notes</h1>
            <p class="text-muted">Cliquez sur une session pour saisir vos notes</p>
        </div>
    </div>

    @if ($sessions->isEmpty())
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>Aucune session disponible</strong>
        <p>Vous n'avez actuellement aucune session visible. Veuillez attendre que votre prof principal vous ajoute aux bulletins.</p>
    </div>
    @else
    <!-- Sessions Grid -->
    <div class="row">
        @foreach ($sessions as $session)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm hover-lift">
                <!-- Header -->
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $session->getSessionLabel() }}</h5>
                    <small>{{ $session->config->nom_classe ?? 'Classe' }}</small>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <!-- Config Info -->
                    <div class="mb-3">
                        <small class="text-muted d-block">Année académique</small>
                        <strong>{{ $session->config->annee_academique ?? 'N/A' }}</strong>
                    </div>

                    <!-- My Subjects -->
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Mes matières</small>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse ($session->mySubjects as $subject)
                            <span class="badge bg-info">{{ $subject->nom }}</span>
                            @empty
                            <span class="text-muted small">Aucune matière</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Completion Progress -->
                    @if ($session->mySubjects->isNotEmpty())
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Progression</small>
                        @foreach ($session->mySubjects as $subject)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small>{{ $subject->nom }}</small>
                                <small>{{ $subject->completion ?? 0 }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $subject->completion ?? 0 }}%"
                                     aria-valuenow="{{ $subject->completion ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Status Badge -->
                    <div class="mb-3">
                        <span class="badge
                            @if ($session->statut === 'saisie_ouverte')
                                bg-success
                            @elseif ($session->statut === 'saisie_fermee')
                                bg-warning
                            @else
                                bg-secondary
                            @endif
                        ">
                            {{ ucfirst(str_replace('_', ' ', $session->statut)) }}
                        </span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer bg-light">
                    @if ($session->isEntryOpen())
                    <a href="{{ route('teacher.grades.bulletin_ng.form', ['session' => $session->id]) }}"
                       class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-edit"></i> Saisir les notes
                    </a>
                    @else
                    <button class="btn btn-sm btn-secondary w-100" disabled>
                        Saisie fermée
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<style>
.hover-lift {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
@endsection
