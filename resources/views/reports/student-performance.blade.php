@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Performance des Étudiants</h1>
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
                        <th>Étudiant</th>
                        <th>Moyenne Générale</th>
                        <th>Présences</th>
                        <th>Absences</th>
                        <th>Taux Assiduité</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($performance as $p)
                        @php
                            $total = $p['present_count'] + $p['absent_count'];
                            $rate = $total > 0 ? ($p['present_count'] / $total) * 100 : 0;
                        @endphp
                        <tr>
                            <td>{{ $p['student']->user->name }}</td>
                            <td>
                                <span class="badge bg-{{ $p['average_grade'] >= 15 ? 'success' : ($p['average_grade'] >= 12 ? 'warning' : 'danger') }}">
                                    {{ number_format($p['average_grade'], 2) }}/20
                                </span>
                            </td>
                            <td>{{ $p['present_count'] }}</td>
                            <td>{{ $p['absent_count'] }}</td>
                            <td>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-{{ $rate >= 90 ? 'success' : ($rate >= 75 ? 'warning' : 'danger') }}" role="progressbar" style="width: {{ $rate }}%">
                                        {{ number_format($rate, 1) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
