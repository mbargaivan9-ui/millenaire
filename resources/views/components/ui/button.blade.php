{{-- Composant Bouton personnalisé --}}
@props(['variant' => 'primary', 'size' => 'md', 'icon' => null, 'loading' => false, 'disabled' => false])

@php
$baseClasses = 'btn fw-600 border-0 transition-all text-decoration-none';
$variantClasses = match($variant) {
    'primary' => 'btn-primary',
    'secondary' => 'btn-secondary',
    'success' => 'btn-success',
    'danger' => 'btn-danger',
    'warning' => 'btn-warning',
    'info' => 'btn-info',
    'light' => 'btn-light',
    'dark' => 'btn-dark',
    'outline-primary' => 'btn-outline-primary',
    'outline-secondary' => 'btn-outline-secondary',
    'outline-danger' => 'btn-outline-danger',
    'ghost' => 'btn-link text-dark',
    default => 'btn-primary'
};

$sizeClasses = match($size) {
    'sm' => 'btn-sm',
    'md' => '',
    'lg' => 'btn-lg',
    default => ''
};
@endphp

<button {{ $attributes->merge(['class' => "$baseClasses $variantClasses $sizeClasses", 'type' => 'button']) }} @disabled($disabled)>
    @if($icon)
        <i class="fas {{ $icon }} me-2"></i>
    @endif
    
    @if($loading)
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
    @endif
    
    {{ $slot }}
</button>
