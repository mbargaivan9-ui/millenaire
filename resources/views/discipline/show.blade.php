@extends('layouts.app')
@section('title', 'Détail de Discipline')
@section('content')
<div class="container-fluid py-4" style="max-width:700px">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('discipline.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h4 mb-0">Dossier Disciplinaire</h1>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-4">Élève</dt><dd class="col-sm-8">{{ $discipline->student->user->name ?? '—' }}</dd>
                <dt class="col-sm-4">Type</dt><dd class="col-sm-8">{{ $discipline->type ?? '—' }}</dd>
                <dt class="col-sm-4">Description</dt><dd class="col-sm-8">{{ $discipline->description ?? '—' }}</dd>
                <dt class="col-sm-4">Date</dt><dd class="col-sm-8">{{ $discipline->date ?? '—' }}</dd>
                <dt class="col-sm-4">Sanction</dt><dd class="col-sm-8">{{ $discipline->sanction ?? 'Aucune' }}</dd>
                <dt class="col-sm-4">Signalé par</dt><dd class="col-sm-8">{{ $discipline->reporter->name ?? '—' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
