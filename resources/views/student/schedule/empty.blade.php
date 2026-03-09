@extends('layouts.app')
@section('title', 'Emploi du Temps')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4">Mon Emploi du Temps</h1>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar3 fs-1 text-muted mb-3 d-block"></i>
            <h5 class="text-muted">Emploi du temps non disponible</h5>
            <p class="text-muted">Votre emploi du temps n'a pas encore été défini. Contactez l'administration.</p>
        </div>
    </div>
</div>
@endsection
