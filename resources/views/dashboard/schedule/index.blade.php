@extends('layouts.app')

@section('title', 'Emploi du Temps')

@section('content')
<div class="container-fluid">
    
    <div class="page-header mb-4">
        <h1 class="fw-bold text-dark">Emploi du Temps Hebdomadaire</h1>
        <p class="text-muted">Consultez les plannings des classes et enseignants</p>
    </div>

    <!-- Sélection de la classe -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Classe</label>
                    <select name="class_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Sélectionner une classe...</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Semaine du</label>
                    <input type="date" name="week_start" class="form-control" 
                           value="{{ request('week_start', now()->startOfWeek()->format('Y-m-d')) }}"
                           onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <!-- Emploi du temps - Vue grille -->
    @if($schedule)
    <div class="card border-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center fw-bold" style="width: 12%;">Horaire</th>
                        <th class="text-center fw-bold">Lundi</th>
                        <th class="text-center fw-bold">Mardi</th>
                        <th class="text-center fw-bold">Mercredi</th>
                        <th class="text-center fw-bold">Jeudi</th>
                        <th class="text-center fw-bold">Vendredi</th>
                        <th class="text-center fw-bold">Samedi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeSlots as $slot)
                    <tr>
                        <td class="text-center fw-bold bg-light">{{ $slot }}</td>
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                        <td class="p-0">
                            @php
                                $session = $schedule
                                    ->where('day_of_week', $loop->index + 1)
                                    ->where('start_time', $slot)
                                    ->first();
                            @endphp
                            @if($session)
                            <div class="schedule-cell bg-light-primary p-2 h-100">
                                <div class="fw-bold text-primary">{{ $session->subject->name }}</div>
                                <small class="text-muted">{{ $session->classroom }}</small>
                                <div class="mt-1">
                                    <span class="badge bg-info">{{ $session->teacher->user->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                            @else
                            <div class="schedule-cell p-2 h-100"></div>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i> Cliquez sur un créneau pour modifier l'horaire
            </small>
        </div>
    </div>
    @else
    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle me-2"></i> 
        Sélectionnez une classe pour afficher son emploi du temps.
    </div>
    @endif

    <!-- Statistiques -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card border-0">
                <div class="card-body text-center">
                    <h2 class="fw-bold text-primary">{{ $totalSessions ?? 0 }}</h2>
                    <p class="text-muted mb-0">Séances/semaine</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0">
                <div class="card-body text-center">
                    <h2 class="fw-bold text-success">{{ $totalHours ?? 0 }}h</h2>
                    <p class="text-muted mb-0">Heures de cours</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0">
                <div class="card-body text-center">
                    <h2 class="fw-bold text-warning">{{ $activeTeachers ?? 0 }}</h2>
                    <p class="text-muted mb-0">Enseignants affectés</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0">
                <div class="card-body text-center">
                    <h2 class="fw-bold text-info">{{ $subjects ?? 0 }}</h2>
                    <p class="text-muted mb-0">Matières</p>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.schedule-cell {
    min-height: 80px;
    border: 1px solid #e9ecef;
    cursor: pointer;
    transition: all 0.3s ease;
}
.schedule-cell:hover {
    background-color: #f8f9fa !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>

@endsection
