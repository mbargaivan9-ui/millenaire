@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Conversations</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('conversations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelles Conversations
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
        <div class="col-md-12">
            <div class="list-group">
                @foreach($conversations as $conversation)
                    <a href="{{ route('conversations.show', $conversation) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $conversation->getDisplayNameForUser(auth()->id()) }}</h6>
                            <small>{{ $conversation->last_message_at?->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">{{ Str::limit($conversation->lastMessage?->content, 100) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{ $conversations->links() }}
</div>
@endsection
