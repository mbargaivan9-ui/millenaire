@extends('layouts.app')
@section('title', 'Rapport des Présences')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4">Rapport des Présences</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted">Rapport complet des présences par classe et période.</p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Classe</th><th>Présents</th><th>Absents</th><th>Taux</th></tr></thead>
                    <tbody>
                        @forelse($stats ?? [] as $stat)
                        <tr>
                            <td>{{ $stat->class_name }}</td>
                            <td>{{ $stat->present }}</td>
                            <td>{{ $stat->absent }}</td>
                            <td>{{ $stat->rate }}%</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Aucune donnée disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


