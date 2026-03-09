@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Créer une Conversation</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('conversations.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('conversations.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="type" class="form-label">Type de Conversation</label>
                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required onchange="updateParticipantsLabel()">
                        <option value="">Sélectionner le type</option>
                        <option value="private">Privée</option>
                        <option value="group">Groupe</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" id="nameField" style="display:none;">
                    <label for="name" class="form-label">Nom du Groupe</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="participants" class="form-label" id="participantsLabel">Participants</label>
                    <div class="form-control" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                        @foreach($users as $user)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="{{ $user->id }}" id="user{{ $user->id }}" name="participants[]">
                                <label class="form-check-label" for="user{{ $user->id }}">
                                    {{ $user->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('participants')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Créer</button>
                    <a href="{{ route('conversations.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateParticipantsLabel() {
    const type = document.getElementById('type').value;
    const nameField = document.getElementById('nameField');
    const participantsLabel = document.getElementById('participantsLabel');
    
    if (type === 'group') {
        nameField.style.display = 'block';
        participantsLabel.textContent = 'Membres du Groupe';
    } else {
        nameField.style.display = 'none';
        participantsLabel.textContent = 'Participants';
    }
}
</script>
@endsection
