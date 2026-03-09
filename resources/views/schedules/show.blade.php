@extends('layouts.app')
@section('title', 'Détail du Créneau')
@section('content')
<div class="container-fluid py-4" style="max-width:600px">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('schedules.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h4 mb-0">Créneau Horaire</h1>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-4">Jour</dt><dd class="col-sm-8">{{ $schedule->day_name ?? '—' }}</dd>
                <dt class="col-sm-4">Heure</dt><dd class="col-sm-8">{{ $schedule->start_time ?? '—' }} – {{ $schedule->end_time ?? '—' }}</dd>
                <dt class="col-sm-4">Classe</dt><dd class="col-sm-8">{{ $schedule->classe->name ?? '—' }}</dd>
                <dt class="col-sm-4">Matière</dt><dd class="col-sm-8">{{ $schedule->subject->name ?? '—' }}</dd>
                <dt class="col-sm-4">Enseignant</dt><dd class="col-sm-8">{{ $schedule->teacher->user->name ?? '—' }}</dd>
                <dt class="col-sm-4">Salle</dt><dd class="col-sm-8">{{ $schedule->room ?? '—' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
