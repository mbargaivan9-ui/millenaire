@extends('layouts.app')

@section('title', $conversation->name ?? 'Conversation')

@push('styles')
<style>
  .conversation-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 16px 24px;
    margin-bottom: 24px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .conversation-title {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .conversation-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1abc9c, #16a085);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
  }

  .conversation-info h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
  }

  .conversation-info p {
    margin: 0;
    font-size: .875rem;
    color: #6b7280;
  }

  .conversation-actions {
    display: flex;
    gap: 8px;
  }

  .btn-action {
    background: #f3f4f6;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: .875rem;
    color: #6b7280;
    transition: all .2s ease;
  }

  .btn-action:hover {
    background: #1abc9c;
    color: white;
  }

  .messages-thread {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    min-height: 400px;
    max-height: 600px;
    overflow-y: auto;
  }

  .message-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
  }

  .message-item.sent {
    flex-direction: row-reverse;
  }

  .message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1abc9c, #16a085);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    flex-shrink: 0;
  }

  .message-bubble {
    max-width: 70%;
    background: #f0f1f3;
    border-radius: 12px;
    padding: 12px 16px;
  }

  .message-item.sent .message-bubble {
    background: #1abc9c;
    color: white;
  }

  .message-sender {
    font-size: .75rem;
    color: #6b7280;
    margin-bottom: 4px;
    font-weight: 600;
  }

  .message-content {
    font-size: .95rem;
    color: #111827;
    line-height: 1.5;
    word-wrap: break-word;
  }

  .message-item.sent .message-content {
    color: white;
  }

  .message-time {
    font-size: .75rem;
    color: #a3a3a3;
    margin-top: 4px;
  }

  .message-item.sent .message-time {
    color: rgba(255,255,255,.7);
  }

  .reply-form {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .form-group label {
    font-weight: 600;
    color: #111827;
    font-size: .875rem;
  }

  .form-group textarea {
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-family: inherit;
    font-size: 1rem;
    resize: vertical;
    min-height: 100px;
  }

  .form-group textarea:focus {
    outline: none;
    border-color: #1abc9c;
    box-shadow: 0 0 0 3px rgba(26,188,156,.1);
  }

  .form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 16px;
  }

  .btn-primary {
    background: linear-gradient(135deg, #1abc9c, #16a085);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all .2s ease;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26,188,156,.3);
  }

  .btn-secondary {
    background: #f3f4f6;
    color: #111827;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all .2s ease;
  }

  .btn-secondary:hover {
    background: #d1d5db;
  }

  .back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #1abc9c;
    text-decoration: none;
    margin-bottom: 20px;
    font-weight: 500;
  }

  .back-link:hover {
    color: #16a085;
  }

  .pagination-wrapper {
    margin-top: 16px;
    text-align: center;
  }

  .empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
  }
</style>
@endpush

@section('content')
<a href="{{ route('messages.index') }}" class="back-link">← {{ __('Back to Messages') ?? 'Retour aux messages' }}</a>

<div class="conversation-header">
  <div class="conversation-title">
    <div class="conversation-avatar">
      @php
        $otherParticipant = $conversation->participants->where('id', '!=', auth()->id())->first();
      @endphp
      {{ strtoupper(substr($otherParticipant?->name ?? 'U', 0, 1)) }}
    </div>
    <div class="conversation-info">
      <h2>{{ $otherParticipant?->name ?? 'Unknown' }}</h2>
      <p>{{ __('Private conversation') ?? 'Conversation privée' }}</p>
    </div>
  </div>
  <div class="conversation-actions">
    <form method="POST" action="{{ route('messages.destroy', $messages->first()->id ?? 0) }}" style="display: inline;">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn-action" onclick="return confirm('{{ __('Delete this conversation?') ?? 'Supprimer cette conversation?' }}')">
        🗑️ {{ __('Delete') ?? 'Supprimer' }}
      </button>
    </form>
  </div>
</div>

<div class="messages-thread">
  @forelse($messages as $msg)
    <div class="message-item {{ $msg->sender_id === auth()->id() ? 'sent' : '' }}">
      <div class="message-avatar">
        {{ strtoupper(substr($msg->sender?->name ?? 'U', 0, 1)) }}
      </div>
      <div>
        <div class="message-bubble">
          <div class="message-sender">{{ $msg->sender?->name ?? 'Unknown' }}</div>
          <div class="message-content">{{ $msg->content }}</div>
          <div class="message-time">{{ $msg->created_at->format('H:i') }}</div>
        </div>
      </div>
    </div>
  @empty
    <div class="empty-state">
      {{ __('No messages in this conversation') ?? 'Pas de messages dans cette conversation' }}
    </div>
  @endforelse
</div>

@if($messages->hasPages())
  <div class="pagination-wrapper">
    {{ $messages->links() }}
  </div>
@endif

<form method="POST" action="{{ route('messages.store') }}" class="reply-form">
  @csrf
  <input type="hidden" name="receiver_id" value="{{ $otherParticipant?->id ?? '' }}">
  
  <div class="form-group">
    <label for="body">{{ __('Your message') ?? 'Votre message' }}</label>
    <textarea name="body" id="body" required placeholder="{{ __('Type a message...') ?? 'Tapez un message...' }}"></textarea>
    @error('body')
      <span style="color: #ef4444; font-size: .875rem;">{{ $message }}</span>
    @enderror
  </div>

  <div class="form-actions">
    <a href="{{ route('messages.index') }}" class="btn-secondary">{{ __('Cancel') ?? 'Annuler' }}</a>
    <button type="submit" class="btn-primary">{{ __('Send') ?? 'Envoyer' }}</button>
  </div>
</form>
@endsection
