@extends('layouts.app')
@section('title', 'Vue Emploi du Temps')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4">Emploi du Temps — Vue Globale</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            @foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'] as $day)
            <h6 class="fw-bold mt-3 text-primary">{{ $day }}</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered">
                    <thead class="table-light"><tr><th>Heure</th><th>Classe</th><th>Matière</th><th>Enseignant</th><th>Salle</th></tr></thead>
                    <tbody>
                        @forelse($schedulesByDay[$day] ?? [] as $s)
                        <tr>
                            <td>{{ $s->start_time }}–{{ $s->end_time }}</td>
                            <td>{{ $s->classe->name ?? '—' }}</td>
                            <td>{{ $s->subject->name ?? '—' }}</td>
                            <td>{{ $s->teacher->user->name ?? '—' }}</td>
                            <td>{{ $s->room ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-muted text-center">Aucun cours</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection


