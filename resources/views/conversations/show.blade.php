@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>{{ $conversation->getDisplayNameForUser(auth()->id()) }}</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('conversations.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-body" style="height: 500px; overflow-y: auto;">
                    @foreach($messages as $message)
                        <div class="mb-3 {{ $message->sender_id === auth()->id() ? 'text-end' : '' }}">
                            <div class="d-inline-block" style="max-width: 70%; padding: 10px; border-radius: 10px; background-color: {{ $message->sender_id === auth()->id() ? '#e3f2fd' : '#f5f5f5' }}">
                                <strong>{{ $message->sender->name }}</strong><br>
                                {{ $message->content }}
                                <br>
                                <small class="text-muted">{{ $message->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer">
                    <form action="{{ route('messages.store') }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <textarea class="form-control" name="content" placeholder="Votre message..." rows="2"></textarea>
                            <button class="btn btn-primary" type="submit">Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h6>Participants</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @foreach($conversation->participants as $participant)
                            <li class="mb-2">
                                {{ $participant->name }}
                                @if($participant->id !== auth()->id() && auth()->user()->can('update', $conversation))
                                    <form action="{{ route('conversations.removeParticipant', [$conversation, $participant]) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">x</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    @if(auth()->user()->can('update', $conversation))
                        <form action="{{ route('conversations.addParticipant', $conversation) }}" method="POST" class="mt-3">
                            @csrf
                            <div class="input-group input-group-sm">
                                <select name="user_id" class="form-select" required>
                                    <option value="">Ajouter participant</option>
                                </select>
                                <button class="btn btn-primary" type="submit">Ajouter</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
