@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Journaux d'Activité</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.reports.dashboard') }}" class="btn btn-secondary">Retour aux Rapports</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Modèle</th>
                        <th>Utilisateur</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-info">{{ $log->action }}</span></td>
                            <td>{{ class_basename($log->loggable_type) }}</td>
                            <td>{{ $log->user?->name ?? 'Système' }}</td>
                            <td>{{ $log->getChangeDescription() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
