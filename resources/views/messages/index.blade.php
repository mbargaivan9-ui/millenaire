@extends('layouts.app')

@section('title','Messages')

@push('styles')
<style>
  .messages-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .message-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    transition: all .2s ease;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .message-item:hover {
    background: #f8fafc;
    border-color: #1abc9c;
    box-shadow: 0 2px 8px rgba(26,188,156,.1);
  }

  .message-item.unread {
    background: #e8faf6;
    border-color: #1abc9c;
    font-weight: 600;
  }

  .message-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1abc9c, #16a085);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
  }

  .message-content {
    flex: 1;
    min-width: 0;
  }

  .message-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 4px;
  }

  .message-sender {
    font-weight: 600;
    color: #111827;
  }

  .message-time {
    font-size: .875rem;
    color: #6b7280;
  }

  .message-preview {
    font-size: .875rem;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .messages-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
  }

  .messages-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
  }

  .messages-stats {
    display: flex;
    gap: 12px;
    font-size: .875rem;
  }

  .stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #6b7280;
  }

  .stat-value {
    font-weight: 600;
    color: #111827;
  }

  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
  }

  .empty-state-icon {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: .5;
  }

  .empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 8px;
  }

  .empty-state-text {
    font-size: .875rem;
    color: #6b7280;
    margin-bottom: 24px;
  }

  .btn-compose {
    background: linear-gradient(135deg, #1abc9c, #16a085);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s ease;
    text-decoration: none;
    display: inline-block;
  }

  .btn-compose:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26,188,156,.3);
  }

  .pagination-wrapper {
    margin-top: 24px;
    display: flex;
    justify-content: center;
  }
</style>
@endpush

@section('content')
<div class="messages-header">
  <h1 class="messages-title">{{ __('nav.messages') ?? 'Messages' }}</h1>
  <div class="messages-stats">
    <div class="stat-item">
      <span>{{ __('Total:') }}</span>
      <span class="stat-value">{{ $conversations->total() }}</span>
    </div>
    @if($unreadCount > 0)
      <div class="stat-item">
        <span>{{ __('Unread:') }}</span>
        <span class="stat-value" style="color: #ef4444;">{{ $unreadCount }}</span>
      </div>
    @endif
  </div>
</div>

@if($conversations->count() > 0)
  <div class="messages-container">
    @foreach($conversations as $conv)
      @php
        // Get other participant from already-loaded collection
        $otherParticipant = $conv->participants->where('id', '!=', auth()->id())->first();
      @endphp
      <a href="{{ route('messages.show', $conv->lastMessage->id ?? '#') }}" class="message-item {{ $unreadCount > 0 ? 'unread' : '' }}">
        <div class="message-avatar">
          {{ strtoupper(substr($otherParticipant?->name ?? 'U', 0, 1)) }}
        </div>
        <div class="message-content">
          <div class="message-header">
            <span class="message-sender">
              {{ $otherParticipant?->name ?? 'Unknown' }}
            </span>
            <span class="message-time">
              {{ $conv->lastMessage?->created_at?->diffForHumans() ?? 'N/A' }}
            </span>
          </div>
          <div class="message-preview">
            {{ $conv->lastMessage?->sender_id == auth()->id() ? 'You: ' : '' }}
            {{ Str::limit($conv->lastMessage?->content ?? '(no message)', 60) }}
          </div>
        </div>
      </a>
    @endforeach
  </div>

  <div class="pagination-wrapper">
    {{ $conversations->links() }}
  </div>
@else
  <div class="empty-state">
    <div class="empty-state-icon">📭</div>
    <div class="empty-state-title">{{ __('No messages') ?? 'Pas de messages' }}</div>
    <div class="empty-state-text">{{ __('You don\'t have any messages yet.') ?? 'Vous n\'avez pas encore de messages.' }}</div>
    <a href="{{ route('messages.create') }}" class="btn-compose">{{ __('Send Message') ?? 'Envoyer un message' }}</a>
  </div>
@endif
@endsection
