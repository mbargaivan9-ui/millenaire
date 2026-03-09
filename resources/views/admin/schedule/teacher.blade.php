@extends('layouts.app')
@section('title', 'Emploi du Temps - Enseignant')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4">Emploi du Temps : {{ $teacher->user->name ?? 'Enseignant' }}</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr><th>Jour</th><th>Heure</th><th>Classe</th><th>Matière</th><th>Salle</th></tr>
                    </thead>
                    <tbody>
                        @forelse($schedules ?? [] as $schedule)
                        <tr>
                            <td>{{ $schedule->day_name }}</td>
                            <td>{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                            <td>{{ $schedule->classe->name ?? '—' }}</td>
                            <td>{{ $schedule->subject->name ?? '—' }}</td>
                            <td>{{ $schedule->room ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucun créneau programmé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


