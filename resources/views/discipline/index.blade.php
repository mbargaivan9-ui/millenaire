@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Gestion Disciplinaire</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('discipline.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Signaler une Discipline
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
                        <th>Type</th>
                        <th>Raison</th>
                        <th>Date Incident</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($disciplines as $discipline)
                        <tr>
                            <td>{{ $discipline->student->user->name }}</td>
                            <td><span class="badge bg-danger">{{ ucfirst($discipline->type) }}</span></td>
                            <td>{{ $discipline->reason }}</td>
                            <td>{{ $discipline->incident_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $discipline->status === 'resolved' ? 'success' : ($discipline->status === 'active' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($discipline->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('discipline.edit', $discipline) }}" class="btn btn-sm btn-warning">Éditer</a>
                                <form action="{{ route('discipline.destroy', $discipline) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $disciplines->links() }}
        </div>
    </div>
</div>
@endsection
