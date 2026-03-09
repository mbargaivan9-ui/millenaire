@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Gestion des Tuteurs</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('guardians.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un Tuteur
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        @foreach($guardians as $guardian)
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $guardian->user->name }}</h5>
                        <p class="card-text">
                            <strong>Email:</strong> {{ $guardian->user->email }}<br>
                            <strong>Téléphone:</strong> {{ $guardian->user->phoneNumber }}<br>
                            <strong>Relation:</strong> {{ $guardian->relationship }}<br>
                            <strong>Profession:</strong> {{ $guardian->profession }}
                        </p>
                        <div class="btn-group" role="group">
                            <a href="{{ route('guardians.edit', $guardian) }}" class="btn btn-sm btn-warning">Éditer</a>
                            <form action="{{ route('guardians.destroy', $guardian) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">Désactiver</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{ $guardians->links() }}
</div>
@endsection
