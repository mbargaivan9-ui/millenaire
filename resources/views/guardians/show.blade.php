@extends('layouts.app')
@section('title', 'Détail du Tuteur')
@section('content')
<div class="container-fluid py-4" style="max-width:700px">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('guardians.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h4 mb-0">Tuteur : {{ $guardian->user->name ?? 'Tuteur' }}</h1>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-4">Nom</dt><dd class="col-sm-8">{{ $guardian->user->name ?? '—' }}</dd>
                <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $guardian->user->email ?? '—' }}</dd>
                <dt class="col-sm-4">Téléphone</dt><dd class="col-sm-8">{{ $guardian->phone ?? '—' }}</dd>
                <dt class="col-sm-4">Profession</dt><dd class="col-sm-8">{{ $guardian->profession ?? '—' }}</dd>
            </dl>
            <hr>
            <h6>Enfants Tutélaires</h6>
            @forelse($guardian->students ?? [] as $student)
            <div class="d-flex align-items-center py-2 border-bottom">
                <div class="me-3"><i class="bi bi-person-circle fs-4 text-secondary"></i></div>
                <div>
                    <strong>{{ $student->user->name ?? '—' }}</strong>
                    <div class="text-muted small">{{ $student->classe->name ?? 'Classe non assignée' }}</div>
                </div>
            </div>
            @empty
            <p class="text-muted">Aucun enfant associé.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
