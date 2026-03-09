@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Matériaux de Cours</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('course-materials.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un Matériau
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
        @foreach($materials as $material)
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $material->title }}</h5>
                        <p class="card-text text-muted">{{ Str::limit($material->description, 100) }}</p>
                        <p class="card-text">
                            <small class="badge bg-info">{{ ucfirst($material->type) }}</small>
                        </p>
                        <div class="btn-group" role="group">
                            @if($material->file_path)
                                <a href="{{ route('course-materials.download', $material) }}" class="btn btn-sm btn-info">Télécharger</a>
                            @endif
                            <a href="{{ route('course-materials.edit', $material) }}" class="btn btn-sm btn-warning">Éditer</a>
                            <form action="{{ route('course-materials.destroy', $material) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{ $materials->links() }}
</div>
@endsection
