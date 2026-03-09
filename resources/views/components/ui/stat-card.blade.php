{{-- Composant Stat Card pour les KPIs --}}
@props(['icon' => 'fa-chart-bar', 'iconColor' => 'primary', 'title' => '', 'value' => 0, 'evolution' => 0, 'evolutionLabel' => '', 'suffix' => '', 'prefix' => '', 'gauge' => false, 'gaugeMax' => 100, 'progress' => false, 'total' => 0, 'badge' => false, 'badgeColor' => 'danger'])

<div class="card border-0 rounded-4 shadow-sm h-100 glass-card overflow-hidden" style="--glass-bg: rgba(255, 255, 255, 0.7); --glass-blur: 10px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <p class="text-muted small fw-semibold text-uppercase mb-2">{{ $title }}</p>
                <h3 class="fw-bold mb-0" style="font-size: 2rem;">
                    <span class="text-dark">{{ $prefix }}{{ number_format($value) }}{{ $suffix }}</span>
                    @if($badge && $value > 0)
                    <span class="badge bg-{{ $badgeColor }} ms-2">{{ $value }}</span>
                    @endif
                </h3>
            </div>
            <div class="p-3 rounded-circle bg-{{ $iconColor }} bg-opacity-10">
                <i class="fas {{ $icon }} fa-lg text-{{ $iconColor }}"></i>
            </div>
        </div>

        @if($gauge)
            <div class="progress mt-3" style="height: 6px;">
                <div class="progress-bar bg-{{ $iconColor }}" role="progressbar" 
                     style="width: {{ ($value / $gaugeMax * 100) }}%" 
                     aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="{{ $gaugeMax }}"></div>
            </div>
        @endif

        @if($progress && $total > 0)
            <div class="progress mt-3" style="height: 6px;">
                <div class="progress-bar bg-{{ $iconColor }}" role="progressbar" 
                     style="width: {{ ($value / $total * 100) }}%" 
                     aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="{{ $total }}"></div>
            </div>
            <small class="text-muted d-block mt-2">{{ $value }}/{{ $total }}</small>
        @endif

        @if($evolution !== 0)
            <small class="text-muted d-block mt-2">
                @if($evolution > 0)
                    <i class="fas fa-arrow-up text-success me-1"></i>
                    <span class="text-success fw-semibold">+{{ $evolution }}%</span>
                @else
                    <i class="fas fa-arrow-down text-danger me-1"></i>
                    <span class="text-danger fw-semibold">{{ $evolution }}%</span>
                @endif
                <span class="text-muted">{{ $evolutionLabel }}</span>
            </small>
        @endif
    </div>
</div>
