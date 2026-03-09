@extends('layouts.app')

@section('title','Notifications')

@push('styles')
<style>
  .notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
  }

  .notifications-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
  }

  .notifications-toolbar {
    display: flex;
    gap: 12px;
    align-items: center;
  }

  .btn-mark-all {
    background: #1abc9c;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: .875rem;
    cursor: pointer;
    transition: all .2s ease;
    font-weight: 500;
  }

  .btn-mark-all:hover {
    background: #16a085;
  }

  .notifications-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .notification-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    gap: 12px;
    transition: all .2s ease;
    cursor: pointer;
  }

  .notification-item:hover {
    background: #f8fafc;
    border-color: #1abc9c;
    box-shadow: 0 2px 8px rgba(26,188,156,.1);
  }

  .notification-item.unread {
    background: #e8faf6;
    border-color: #1abc9c;
    border-left: 4px solid #1abc9c;
  }

  .notification-icon {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
  }

  .notification-icon.danger {
    background: #fee2e2;
    color: #dc2626;
  }

  .notification-icon.success {
    background: #dcfce7;
    color: #16a34a;
  }

  .notification-icon.info {
    background: #dbeafe;
    color: #2563eb;
  }

  .notification-icon.warning {
    background: #fef3c7;
    color: #d97706;
  }

  .notification-content {
    flex: 1;
    min-width: 0;
  }

  .notification-title {
    font-weight: 600;
    color: #111827;
    margin-bottom: 4px;
  }

  .notification-message {
    font-size: .875rem;
    color: #6b7280;
    margin-bottom: 4px;
    line-height: 1.4;
  }

  .notification-category {
    display: inline-block;
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 4px;
    background: #f3f4f6;
    color: #6b7280;
    margin-right: 8px;
  }

  .notification-time {
    font-size: .875rem;
    color: #9ca3af;
  }

  .notification-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-left: auto;
    min-width: fit-content;
  }

  .btn-small {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 6px;
    border-radius: 6px;
    transition: all .2s ease;
  }

  .btn-small:hover {
    background: #f3f4f6;
    color: #1abc9c;
  }

  .filters {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }

  .filter-btn {
    background: white;
    border: 1px solid #e5e7eb;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: .875rem;
    color: #6b7280;
    transition: all .2s ease;
  }

  .filter-btn:hover,
  .filter-btn.active {
    background: #1abc9c;
    color: white;
    border-color: #1abc9c;
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
  }

  .pagination-wrapper {
    margin-top: 24px;
    display: flex;
    justify-content: center;
  }
</style>
@endpush

@section('content')
<div class="notifications-header">
  <h1 class="notifications-title">{{ __('nav.notifications') ?? 'Notifications' }}</h1>
  <div class="notifications-toolbar">
    @if($unreadCount > 0)
      <button type="button" class="btn-mark-all" onclick="markAllAsRead()">
        {{ __('Mark all as read') ?? 'Marquer tout comme lu' }}
      </button>
    @endif
  </div>
</div>

@if($notifications->count() > 0)
  @if(isset($categories) && $categories->count() > 0)
    <div class="filters">
      <button class="filter-btn active" onclick="filterNotifications(null)">
        {{ __('All') ?? 'Tous' }}
      </button>
      @foreach($categories as $cat)
        <button class="filter-btn" onclick="filterNotifications('{{ $cat->category }}')">
          {{ ucfirst($cat->category) }} ({{ $cat->count }})
        </button>
      @endforeach
    </div>
  @endif

  <div class="notifications-container">
    @foreach($notifications as $n)
      <div class="notification-item {{ !$n->is_read ? 'unread' : '' }}" data-category="{{ $n->category }}">
        <div class="notification-icon {{ $n->type ?? 'info' }}">
          @if($n->type === 'danger')
            ⚠️
          @elseif($n->type === 'success')
            ✓
          @elseif($n->type === 'warning')
            ⚡
          @else
            ℹ️
          @endif
        </div>
        <div class="notification-content">
          <div class="notification-title">{{ $n->title ?? 'Notification' }}</div>
          <div class="notification-message">{{ Str::limit($n->message, 100) }}</div>
          <div>
            <span class="notification-category">{{ ucfirst($n->category ?? 'system') }}</span>
            <span class="notification-time">{{ $n->created_at->diffForHumans() }}</span>
          </div>
        </div>
        <div class="notification-actions">
          @if(!$n->is_read)
            <button class="btn-small" onclick="markNotificationAsRead({{ $n->id }})" title="{{ __('Mark as read') }}">
              <i data-lucide="check"></i>
            </button>
          @endif
          <button class="btn-small" onclick="deleteNotification({{ $n->id }})" title="{{ __('Delete') }}">
            <i data-lucide="trash-2"></i>
          </button>
        </div>
      </div>
    @endforeach
  </div>

  <div class="pagination-wrapper">
    {{ $notifications->links() }}
  </div>
@else
  <div class="empty-state">
    <div class="empty-state-icon">🔔</div>
    <div class="empty-state-title">{{ __('No notifications') ?? 'Pas de notifications' }}</div>
    <div class="empty-state-text">{{ __('You don\'t have any notifications yet.') ?? 'Vous n\'avez pas encore de notifications.' }}</div>
  </div>
@endif

<script>
  function filterNotifications(category) {
    const items = document.querySelectorAll('.notification-item');
    items.forEach(item => {
      if (!category || item.dataset.category === category) {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    });
  }

  function markAllAsRead() {
    fetch('{{ route("notifications.mark-all-read") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      }
    })
    .catch(error => console.error('Error:', error));
  }

  function markNotificationAsRead(notificationId) {
    fetch('{{ route("notifications.mark-read", ":id") }}'.replace(':id', notificationId), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      }
    })
    .catch(error => console.error('Error:', error));
  }

  function deleteNotification(notificationId) {
    if (!confirm('{{ __("Are you sure?") }}')) return;
    
    fetch('{{ route("notifications.destroy", ":id") }}'.replace(':id', notificationId), {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      }
    })
    .catch(error => console.error('Error:', error));
  }

  // Fix lucide icons
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  // Auto-refresh lucide icons after any DOM changes
  window.addEventListener('htmx:load', () => {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  });
</script>
@endsection
