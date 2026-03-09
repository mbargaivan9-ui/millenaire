@extends('layouts.app')

@section('title', 'Compose Message')

@push('styles')
<style>
  .compose-form {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    max-width: 800px;
    margin: 0 auto;
  }

  .compose-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 24px;
  }

  .form-group {
    margin-bottom: 24px;
  }

  .form-label {
    display: block;
    font-weight: 500;
    color: #111827;
    margin-bottom: 8px;
  }

  .form-input,
  .form-select,
  .form-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    font-family: inherit;
    transition: all .2s ease;
    color: #111827;
    background: white;
  }

  .form-input:focus,
  .form-select:focus,
  .form-textarea:focus {
    outline: none;
    border-color: #1abc9c;
    box-shadow: 0 0 0 3px rgba(26,188,156,.1);
  }

  .form-textarea {
    resize: vertical;
    min-height: 200px;
  }

  .form-error {
    color: #dc2626;
    font-size: .875rem;
    margin-top: 6px;
  }

  .form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
  }

  .btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all .2s ease;
    font-size: 1rem;
  }

  .btn-primary {
    background: linear-gradient(135deg, #1abc9c, #16a085);
    color: white;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26,188,156,.3);
  }

  .btn-secondary {
    background: #f3f4f6;
    color: #6b7280;
  }

  .btn-secondary:hover {
    background: #e5e7eb;
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

  .user-option {
    display: flex;
    align-items: center;
    gap: 8px;
  }
</style>
@endpush

@section('content')
<a href="{{ route('messages.index') }}" class="back-link">
  <span>←</span>
  {{ __('Back to messages') ?? 'Retour aux messages' }}
</a>

<form method="POST" action="{{ route('messages.store') }}" class="compose-form">
  @csrf

  <h1 class="compose-title">{{ __('Compose Message') ?? 'Composer un message' }}</h1>

  <div class="form-group">
    <label for="receiver_id" class="form-label">{{ __('Recipient') ?? 'Destinataire' }} *</label>
    <select name="receiver_id" id="receiver_id" class="form-select" required>
      <option value="">-- {{ __('Select a recipient') ?? 'Sélectionner un destinataire' }} --</option>
      @foreach($users as $user)
        <option value="{{ $user->id }}" {{ old('receiver_id') == $user->id ? 'selected' : '' }}>
          {{ $user->name }} 
          @if($user->role)
            ({{ ucfirst($user->role) }})
          @endif
        </option>
      @endforeach
    </select>
    @error('receiver_id')
      <div class="form-error">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-group">
    <label for="body" class="form-label">{{ __('Message') ?? 'Message' }} *</label>
    <textarea 
      name="body" 
      id="body" 
      class="form-textarea" 
      placeholder="{{ __('Type your message here...') ?? 'Tapez votre message ici...' }}"
      required
    >{{ old('body') }}</textarea>
    @error('body')
      <div class="form-error">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-actions">
    <a href="{{ route('messages.index') }}" class="btn btn-secondary">
      {{ __('Cancel') ?? 'Annuler' }}
    </a>
    <button type="submit" class="btn btn-primary">
      {{ __('Send Message') ?? 'Envoyer le message' }}
    </button>
  </div>
</form>
@endsection
