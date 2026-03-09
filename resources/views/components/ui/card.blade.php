{{-- Composant Card générique --}}
@props(['title' => '', 'subtitle' => '', 'class' => '', 'header' => null, 'footer' => null])

<div class="card border-0 rounded-4 shadow-sm glass-card {{ $class }}" style="--glass-bg: rgba(255, 255, 255, 0.7); --glass-blur: 10px;">
    @if($title || $header)
        <div class="card-header bg-transparent border-0 pt-4 pb-0">
            @if($header)
                {{ $header }}
            @else
                <div>
                    <h5 class="card-title fw-bold mb-0">{{ $title }}</h5>
                    @if($subtitle)
                        <p class="card-subtitle text-muted small mt-1">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <div class="card-body p-4">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="card-footer bg-transparent border-top-0 pt-0 pb-4 px-4">
            {{ $footer }}
        </div>
    @endif
</div>
