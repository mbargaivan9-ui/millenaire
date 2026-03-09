{{--
    |--------------------------------------------------------------------------
    | admin/kpi.blade.php — Tableau de Bord KPI Admin
    |--------------------------------------------------------------------------
    | Phase 3 — Dashboard Administrateur Principal
    | KPIs en temps réel, graphiques, alertes, activité récente
    --}}

@extends('layouts.app')

@section('title', $pageTitle ?? 'Dashboard')

@push('styles')
<style>
/* ─── KPI Grid ───────────────────────────────────────────────────────────── */
.kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem; }
@media(max-width:991px) { .kpi-grid { grid-template-columns:repeat(2,1fr); } }
@media(max-width:575px) { .kpi-grid { grid-template-columns:1fr; } }

.kpi-card {
    border-radius:var(--radius-lg); padding:1.5rem;
    background:var(--surface); border:1.5px solid var(--border);
    position:relative; overflow:hidden; transition:transform .2s ease;
}
.kpi-card:hover { transform:translateY(-3px); box-shadow:var(--shadow-lg); }
.kpi-card::after {
    content:''; position:absolute; top:-30px; right:-30px;
    width:100px; height:100px; border-radius:50%;
    background:var(--kpi-color,var(--primary)); opacity:.06;
}
.kpi-icon { width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:1rem; }
.kpi-value { font-size:2rem; font-weight:900; color:var(--text-primary); line-height:1; margin-bottom:.25rem; }
.kpi-label { font-size:.8rem; color:var(--text-muted); font-weight:600; }
.kpi-trend {
    display:inline-flex; align-items:center; gap:.25rem;
    font-size:.75rem; font-weight:700; margin-top:.5rem;
    padding:.2rem .55rem; border-radius:12px;
}
.kpi-trend.up   { background:#ecfdf5; color:#059669; }
.kpi-trend.down { background:#fef2f2; color:#dc2626; }
.kpi-trend.flat { background:var(--surface-2); color:var(--text-muted); }

/* ─── Charts ─────────────────────────────────────────────────────────────── */
.chart-card { background:var(--surface); border:1.5px solid var(--border); border-radius:var(--radius-lg); padding:1.5rem; }
.chart-card .chart-title { font-weight:700; font-size:.95rem; margin-bottom:1.25rem; }

/* ─── Activity feed ──────────────────────────────────────────────────────── */
.activity-item {
    display:flex; align-items:flex-start; gap:.75rem;
    padding:.6rem 0; border-bottom:1px solid var(--border-light);
}
.activity-item:last-child { border-bottom:none; }
.activity-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:6px; }
.activity-text { font-size:.82rem; color:var(--text-secondary); }
.activity-time { font-size:.72rem; color:var(--text-muted); margin-top:.1rem; }

/* ─── Alerts widget ──────────────────────────────────────────────────────── */
.alert-item {
    display:flex; align-items:center; gap:.75rem;
    padding:.75rem; border-radius:8px; margin-bottom:.5rem;
    font-size:.83rem;
}
.alert-item.danger  { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; }
.alert-item.warning { background:#fffbeb; border:1px solid #fde68a; color:#d97706; }
.alert-item.info    { background:var(--primary-bg); border:1px solid rgba(13,148,136,.2); color:var(--primary); }
</style>
@endpush

@section('content')

@php
    $isFr = app()->getLocale() === 'fr';
    $kpis = $kpis ?? [];
    $activities = $activities ?? collect();
    $alerts = $alerts ?? [];
@endphp

{{-- ─── Header ──────────────────────────────────────────────────────────────── --}}
<div class="page-header mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,var(--primary),var(--primary-light))">
                <i data-lucide="layout-dashboard"></i>
            </div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Tableau de Bord' : 'Dashboard' }}</h1>
                <p class="page-subtitle text-muted">
                    {{ $isFr ? 'Vue d\'ensemble de' : 'Overview of' }} {{ config('app.establishment_name', 'Collège Millénaire Bilingue') }}
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <span class="badge" style="background:var(--primary-bg);color:var(--primary);font-size:.8rem;padding:.4rem .8rem">
                <i data-lucide="calendar" style="width:12px" class="me-1"></i>
                {{ now()->locale($isFr ? 'fr' : 'en')->isoFormat('dddd D MMMM YYYY') }}
            </span>
            <button class="btn btn-light btn-sm" onclick="location.reload()">
                <i data-lucide="refresh-cw" style="width:14px"></i>
            </button>
        </div>
    </div>
</div>

{{-- ─── KPI Grid ─────────────────────────────────────────────────────────────── --}}
<div class="kpi-grid">
    @php
        $kpiConfig = [
            ['key'=>'students',   'icon'=>'users',        'color'=>'#0d9488', 'label_fr'=>'Élèves inscrits',     'label_en'=>'Enrolled students'],
            ['key'=>'teachers',   'icon'=>'user-check',   'color'=>'#3b82f6', 'label_fr'=>'Enseignants actifs',  'label_en'=>'Active teachers'],
            ['key'=>'attendance', 'icon'=>'calendar-check','color'=>'#10b981','label_fr'=>'Présence aujourd\'hui','label_en'=>'Today attendance'],
            ['key'=>'revenue',    'icon'=>'banknote',     'color'=>'#f59e0b', 'label_fr'=>'Recettes ce mois',    'label_en'=>'Revenue this month'],
        ];
    @endphp
    @foreach($kpiConfig as $kpi)
    @php
        $val   = $kpis[$kpi['key']] ?? 0;
        $trend = $kpis[$kpi['key'] . '_trend'] ?? 0;
        $trendClass = $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'flat');
        $trendIcon  = $trend > 0 ? '▲' : ($trend < 0 ? '▼' : '→');
    @endphp
    <div class="kpi-card" style="--kpi-color:{{ $kpi['color'] }}">
        <div class="kpi-icon" style="background:{{ $kpi['color'] }}15;color:{{ $kpi['color'] }}">
            <i data-lucide="{{ $kpi['icon'] }}" style="width:24px"></i>
        </div>
        <div class="kpi-value">
            @if($kpi['key'] === 'revenue')
                <span style="font-size:1.2rem;font-weight:700">XAF </span>{{ number_format($val, 0, ',', ' ') }}
            @elseif($kpi['key'] === 'attendance')
                {{ $val }}%
            @else
                {{ number_format($val) }}
            @endif
        </div>
        <div class="kpi-label">{{ $isFr ? $kpi['label_fr'] : $kpi['label_en'] }}</div>
        @if($trend != 0)
        <span class="kpi-trend {{ $trendClass }}">
            {{ $trendIcon }} {{ abs($trend) }}{{ $kpi['key'] === 'attendance' ? '%' : '' }}
            {{ $isFr ? 'vs mois dernier' : 'vs last month' }}
        </span>
        @endif
    </div>
    @endforeach
</div>

{{-- ─── Charts + Alerts row ──────────────────────────────────────────────────── --}}
<div class="row gy-4 mb-4">

    {{-- Revenue chart --}}
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="chart-title mb-0">
                    <i data-lucide="trending-up" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Recettes Mensuelles (XAF)' : 'Monthly Revenue (XAF)' }}
                </h6>
                <select class="form-select form-select-sm" style="width:120px" id="revenue-year" onchange="updateRevenueChart()">
                    <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                    <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                </select>
            </div>
            <canvas id="revenueChart" height="90"></canvas>
        </div>
    </div>

    {{-- Alerts --}}
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <h6 class="chart-title">
                <i data-lucide="alert-triangle" style="width:16px" class="me-2;color:#f59e0b"></i>
                {{ $isFr ? 'Alertes & Actions requises' : 'Alerts & Required actions' }}
            </h6>
            @forelse($alerts as $alert)
            <div class="alert-item {{ $alert['type'] }}">
                <i data-lucide="{{ $alert['icon'] ?? 'info' }}" style="width:16px;flex-shrink:0"></i>
                <div>
                    <div class="fw-semibold">{{ $alert['title'] }}</div>
                    <div style="font-size:.75rem;opacity:.8">{{ $alert['desc'] }}</div>
                </div>
                @if($alert['action_url'] ?? null)
                <a href="{{ $alert['action_url'] }}" class="btn btn-sm ms-auto" style="font-size:.72rem;padding:.2rem .5rem;white-space:nowrap">
                    {{ $isFr ? 'Voir' : 'View' }}
                </a>
                @endif
            </div>
            @empty
            <div class="text-center text-muted py-3" style="font-size:.83rem">
                ✅ {{ $isFr ? 'Aucune alerte active.' : 'No active alerts.' }}
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ─── Secondary charts row ────────────────────────────────────────────────── --}}
<div class="row gy-4 mb-4">
    {{-- Attendance pie --}}
    <div class="col-md-4">
        <div class="chart-card">
            <h6 class="chart-title">{{ $isFr ? 'Présence Aujourd\'hui' : 'Today\'s Attendance' }}</h6>
            <canvas id="attendanceChart" height="180"></canvas>
            <div class="d-flex justify-content-center gap-3 mt-3" style="font-size:.78rem">
                <span><span style="color:#10b981">●</span> {{ $isFr ? 'Présents' : 'Present' }} ({{ $kpis['present'] ?? 0 }})</span>
                <span><span style="color:#ef4444">●</span> {{ $isFr ? 'Absents' : 'Absent' }} ({{ $kpis['absent'] ?? 0 }})</span>
                <span><span style="color:#f59e0b">●</span> {{ $isFr ? 'Retards' : 'Late' }} ({{ $kpis['late'] ?? 0 }})</span>
            </div>
        </div>
    </div>

    {{-- Grade distribution --}}
    <div class="col-md-4">
        <div class="chart-card">
            <h6 class="chart-title">{{ $isFr ? 'Distribution des Notes' : 'Grade Distribution' }}</h6>
            <canvas id="gradesChart" height="180"></canvas>
        </div>
    </div>

    {{-- Payment breakdown --}}
    <div class="col-md-4">
        <div class="chart-card">
            <h6 class="chart-title">{{ $isFr ? 'Paiements — État' : 'Payments — Status' }}</h6>
            <canvas id="paymentsChart" height="180"></canvas>
            <div class="d-flex justify-content-center gap-3 mt-3" style="font-size:.78rem">
                <span><span style="color:#10b981">●</span> {{ $isFr ? 'Réglés' : 'Paid' }}</span>
                <span><span style="color:#f59e0b">●</span> {{ $isFr ? 'Partiels' : 'Partial' }}</span>
                <span><span style="color:#ef4444">●</span> {{ $isFr ? 'En attente' : 'Pending' }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ─── Bottom row: Activity feed + Quick actions ─────────────────────────── --}}
<div class="row gy-4">
    {{-- Activity log --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">
                    <i data-lucide="activity" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Activité Récente' : 'Recent Activity' }}
                </h6>
                <a href="{{ route('admin.reports.activity-logs') }}" class="btn btn-sm btn-light">
                    {{ $isFr ? 'Tout voir' : 'View all' }}
                </a>
            </div>
            <div class="card-body">
                @forelse($activities as $act)
                @php
                    $dotColors = ['created'=>'#10b981','updated'=>'#3b82f6','deleted'=>'#ef4444','logged'=>'#8b5cf6'];
                    $dotColor  = $dotColors[$act->event ?? 'updated'] ?? '#94a3b8';
                @endphp
                <div class="activity-item">
                    <div class="activity-dot" style="background:{{ $dotColor }}"></div>
                    <div>
                        <div class="activity-text">
                            <strong>{{ $act->causer?->name ?? ($isFr ? 'Système' : 'System') }}</strong>
                            — {{ $act->description }}
                        </div>
                        <div class="activity-time">{{ $act->created_at?->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <p class="text-muted mb-0" style="font-size:.83rem">{{ $isFr ? 'Aucune activité récente.' : 'No recent activity.' }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-lucide="zap" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Actions Rapides' : 'Quick Actions' }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row gy-2 gx-2">
                    @foreach([
                        ['icon'=>'user-plus',   'label_fr'=>'Ajouter un élève',       'label_en'=>'Add student',        'route'=>'admin.students.create', 'color'=>'#0d9488'],
                        ['icon'=>'user-check',  'label_fr'=>'Ajouter un enseignant',  'label_en'=>'Add teacher',        'route'=>'admin.teachers.create', 'color'=>'#3b82f6'],
                        ['icon'=>'megaphone',   'label_fr'=>'Nouvelle annonce',       'label_en'=>'New announcement',   'route'=>'admin.announcements.create', 'color'=>'#8b5cf6'],
                        ['icon'=>'calendar',    'label_fr'=>'Gérer les emplois',      'label_en'=>'Manage schedules',   'route'=>'admin.schedule.index', 'color'=>'#f59e0b'],
                        ['icon'=>'file-down',   'label_fr'=>'Export bulletins',       'label_en'=>'Export report cards','route'=>'admin.kpi.index', 'color'=>'#10b981'],
                        ['icon'=>'settings',    'label_fr'=>'Paramètres',             'label_en'=>'Settings',           'route'=>'admin.settings.edit',  'color'=>'#64748b'],
                    ] as $action)
                    <div class="col-6">
                        <a href="{{ route($action['route']) }}"
                           class="d-flex align-items-center gap-2 p-2 rounded-3 text-decoration-none"
                           style="border:1.5px solid var(--border);transition:all .15s ease"
                           onmouseover="this.style.borderColor='{{ $action['color'] }}';this.style.background='{{ $action['color'] }}11'"
                           onmouseout="this.style.borderColor='var(--border)';this.style.background='transparent'">
                            <div style="width:32px;height:32px;border-radius:8px;background:{{ $action['color'] }}15;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <i data-lucide="{{ $action['icon'] }}" style="width:16px;color:{{ $action['color'] }}"></i>
                            </div>
                            <span style="font-size:.78rem;font-weight:600;color:var(--text-primary)">
                                {{ $isFr ? $action['label_fr'] : $action['label_en'] }}
                            </span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const isFr = {{ app()->getLocale() === 'fr' ? 'true' : 'false' }};

    // ─── Revenue Chart ─────────────────────────────────────────────────────────
    const revenueData = {!! json_encode((array) ($kpis['revenue_monthly'] ?? array_fill(0, 12, 0))) !!};
    const months = isFr
        ? ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc']
        : ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: isFr ? 'Recettes (XAF)' : 'Revenue (XAF)',
                data: revenueData,
                backgroundColor: 'rgba(13,148,136,.2)',
                borderColor: '#0d9488',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true, plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'XAF ' + v.toLocaleString() } } }
        }
    });

    // ─── Attendance Pie ────────────────────────────────────────────────────────
    const attendanceData = [
        {{ (int) ($kpis['present'] ?? 0) }}, 
        {{ (int) ($kpis['absent'] ?? 0) }}, 
        {{ (int) ($kpis['late'] ?? 0) }}
    ];
    new Chart(document.getElementById('attendanceChart'), {
        type: 'doughnut',
        data: {
            labels: isFr ? ['Présents','Absents','Retards'] : ['Present','Absent','Late'],
            datasets: [{
                data: attendanceData,
                backgroundColor: ['#10b981','#ef4444','#f59e0b'],
                borderWidth: 0,
            }]
        },
        options: { responsive: true, cutout: '70%', plugins: { legend: { display: false } } }
    });

    // ─── Grades Distribution ───────────────────────────────────────────────────
    const gradeLabels  = isFr ? ['< 10','10-12','13-15','16-18','19-20'] : ['< 10','10-12','13-15','16-18','19-20'];
    const gradeColors  = ['#ef4444','#f59e0b','#3b82f6','#10b981','#8b5cf6'];
    const gradeData = {!! json_encode((array) ($kpis['grade_distribution'] ?? [0, 0, 0, 0, 0])) !!};
    new Chart(document.getElementById('gradesChart'), {
        type: 'bar',
        data: {
            labels: gradeLabels,
            datasets: [{
                label: isFr ? 'Élèves' : 'Students',
                data: gradeData,
                backgroundColor: gradeColors,
                borderRadius: 6,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    // ─── Payments Pie ──────────────────────────────────────────────────────────
    const paymentsData = [
        {{ (int) ($kpis['payments_paid'] ?? 0) }}, 
        {{ (int) ($kpis['payments_partial'] ?? 0) }}, 
        {{ (int) ($kpis['payments_pending'] ?? 0) }}
    ];
    new Chart(document.getElementById('paymentsChart'), {
        type: 'doughnut',
        data: {
            labels: isFr ? ['Réglés','Partiels','En attente'] : ['Paid','Partial','Pending'],
            datasets: [{
                data: paymentsData,
                backgroundColor: ['#10b981','#f59e0b','#ef4444'],
                borderWidth: 0,
            }]
        },
        options: { responsive: true, cutout: '70%', plugins: { legend: { display: false } } }
    });
});
</script>
@endpush


