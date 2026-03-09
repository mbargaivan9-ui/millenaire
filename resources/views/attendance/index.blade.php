@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Gestion des Attendances</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('attendance.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter une Attendance
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
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Matière</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Enregistré par</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->student->user->name }}</td>
                            <td>{{ $attendance->classSubjectTeacher->subject->name }}</td>
                            <td>{{ $attendance->date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'absent' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td>{{ $attendance->recordedBy->name }}</td>
                            <td>
                                <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-sm btn-warning">Éditer</a>
                                <form action="{{ route('attendance.destroy', $attendance) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .badge {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }
</style>
@endsection
