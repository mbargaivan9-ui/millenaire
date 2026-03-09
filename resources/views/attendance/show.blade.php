@extends('layouts.app')
@section('title', 'Détail de Présence')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4">Présence — {{ $attendance->student->user->name ?? 'Étudiant' }}</h1>
    <div class="card shadow-sm" style="max-width:500px">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-4">Date</dt><dd class="col-sm-8">{{ $attendance->date ?? '—' }}</dd>
                <dt class="col-sm-4">Statut</dt><dd class="col-sm-8">{{ $attendance->status ?? '—' }}</dd>
                <dt class="col-sm-4">Remarque</dt><dd class="col-sm-8">{{ $attendance->notes ?? '—' }}</dd>
            </dl>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Retour</a>
        </div>
    </div>
</div>
@endsection
