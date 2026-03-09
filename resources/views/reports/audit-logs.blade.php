@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Journaux d'Audit Admin</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.reports.dashboard') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Utilisateur</th>
                        <th>Entité</th>
                        <th>Raison</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-danger">{{ $log->action }}</span></td>
                            <td>{{ $log->user->name }}</td>
                            <td>{{ $log->entity_type }} <br><small class="text-muted">#{{ $log->entity_id }}</small></td>
                            <td>{{ $log->reason }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
