@props([
    'alt' => 'Logo de l\'établissement',
    'style' => 'width: 48px; height: 48px; object-fit: contain;',
    'class' => '',
])

@php
    $logoUrl = \App\Helpers\SettingsHelper::logoUrl();
    $settings = \App\Models\EstablishmentSetting::getInstance();
@endphp

@if($logoUrl)
    <img src="{{ $logoUrl }}" 
         alt="{{ $alt }}" 
         style="{{ $style }}"
         class="{{ $class }}"
         {{ $attributes }}>
@else
    <svg xmlns="http://www.w3.org/2000/svg" 
         viewBox="0 0 24 24" 
         fill="none" 
         stroke="currentColor" 
         stroke-width="2.5" 
         style="{{ $style }}"
         class="{{ $class }}"
         {{ $attributes }}>
        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
    </svg>
@endif
