@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Modèles de Bulletins</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('bulletin-templates.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau Modèle
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
        @foreach($templates as $template)
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $template->name }}</h5>
                        <p class="card-text">
                            <strong>Classe:</strong> {{ $template->classe->name }}<br>
                            <strong>Statut:</strong> @if($template->is_active) <span class="badge bg-success">Actif</span> @else <span class="badge bg-secondary">Inactif</span> @endif
                        </p>
                        <div class="btn-group" role="group">
                            <a href="{{ route('bulletin-templates.preview', $template) }}" class="btn btn-sm btn-info">Aperçu</a>
                            <a href="{{ route('bulletin-templates.edit', $template) }}" class="btn btn-sm btn-warning">Éditer</a>
                            <form action="{{ route('bulletin-templates.destroy', $template) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{ $templates->links() }}
</div>
@endsection
