@extends('layouts.app')

@section('title', 'Mes Cours')

@section('content')
    <h1 class="page-title">Mes Cours</h1>

    <div class="card">
        <div class="card-body">
            <table class="table data-table">
                <thead>
                    <tr>
                        <th>Classe</th>
                        <th>Matière</th>
                        <th>Heures</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $c)
                        <tr>
                            <td>{{ $c->classe->name ?? 'N/A' }}</td>
                            <td>{{ $c->subject->name ?? 'N/A' }}</td>
                            <td>{{ $c->hours_per_week ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
