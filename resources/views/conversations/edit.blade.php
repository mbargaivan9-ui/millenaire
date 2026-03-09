@extends('layouts.app')
@section('title', 'Modifier la Conversation')
@section('content')
<div class="container-fluid py-4" style="max-width:700px">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('conversations.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h4 mb-0">Modifier la Conversation</h1>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('conversations.update', $conversation) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nom de la conversation</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $conversation->name) }}">
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>
</div>
@endsection
