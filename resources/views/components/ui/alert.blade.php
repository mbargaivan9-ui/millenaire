{{-- Composant Alerte --}}
@props(['type' => 'info', 'dismissible' => true, 'icon' => null])

@php
$alertClasses = match($type) {
    'success' => 'alert-success',
    'danger' => 'alert-danger',
    'warning' => 'alert-warning',
    'info' => 'alert-info',
    'light' => 'alert-light',
    default => 'alert-info'
};

$defaultIcon = match($type) {
    'success' => 'fa-check-circle',
    'danger' => 'fa-exclamation-circle',
    'warning' => 'fa-warning',
    'info' => 'fa-info-circle',
    'light' => 'fa-info-circle',
    default => 'fa-info-circle'
};
@endphp

<div class="alert {{ $alertClasses }} {{ $dismissible ? 'alert-dismissible fade show' : '' }}" role="alert">
    @if($icon || $type !== 'light')
        <i class="fas {{ $icon ?? $defaultIcon }} me-2"></i>
    @endif
    
    {{ $slot }}
    
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
