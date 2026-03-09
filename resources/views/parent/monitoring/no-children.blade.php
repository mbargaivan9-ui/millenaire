{{-- resources/views/parent/monitoring/no-children.blade.php --}}
@extends('layouts.app')
@section('title', 'Aucun enfant associé')
@section('content')
<div class="container py-5 text-center">
    <div class="py-5">
        <i class="fas fa-user-slash fa-4x text-muted mb-4"></i>
        <h3 class="fw-bold">Aucun élève associé à votre compte</h3>
        <p class="text-muted">Contactez l'administration de l'établissement pour lier votre compte à votre enfant.</p>
        <a href="{{ route('home') }}" class="btn btn-primary mt-3">Retour à l'accueil</a>
    </div>
</div>
@endsection
