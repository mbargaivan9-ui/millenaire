{{-- Composant Badge pour les statuts --}}
@props(['variant' => 'primary', 'size' => 'sm'])

@php
$variantClasses = match($variant) {
    'primary' => 'bg-primary',
    'secondary' => 'bg-secondary',
    'success' => 'bg-success',
    'danger' => 'bg-danger',
    'warning' => 'bg-warning text-dark',
    'info' => 'bg-info',
    'light' => 'bg-light text-dark',
    'dark' => 'bg-dark',
    'paid' => 'bg-success',
    'unpaid' => 'bg-danger',
    'partial' => 'bg-warning text-dark',
    'pending' => 'bg-info',
    'active' => 'bg-success',
    'inactive' => 'bg-secondary',
    default => 'bg-primary'
};

$sizeClasses = match($size) {
    'sm' => 'px-2 py-1',
    'md' => 'px-3 py-2',
    'lg' => 'px-4 py-3',
    default => 'px-2 py-1'
};
@endphp

<span class="badge rounded-pill {{ $variantClasses }} {{ $sizeClasses }}">
    {{ $slot }}
</span>
