@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Gestion des Emplois du Temps</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('schedules.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un Horaire
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Classe</th>
                        <th>Matière</th>
                        <th>Enseignant</th>
                        <th>Jour</th>
                        <th>Heure</th>
                        <th>Salle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->classe->name }}</td>
                            <td>{{ $schedule->classSubjectTeacher->subject->name }}</td>
                            <td>{{ $schedule->classSubjectTeacher->teacher->user->name }}</td>
                            <td>{{ ucfirst($schedule->day_of_week) }}</td>
                            <td>{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                            <td>{{ $schedule->room_number }}</td>
                            <td>
                                <a href="{{ route('schedules.edit', $schedule) }}" class="btn btn-sm btn-warning">Éditer</a>
                                <form action="{{ route('schedules.destroy', $schedule) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $schedules->links() }}
        </div>
    </div>
</div>
@endsection
