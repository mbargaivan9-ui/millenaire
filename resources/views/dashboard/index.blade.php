@extends('layouts.app')

@section('title', __('nav.dashboard'))

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ __('nav.dashboard') }}</span>
    </div>
    <h1 class="page-title">{{ __('dashboard.growth_command_center') }}</h1>
    <p class="page-subtitle">{{ __('dashboard.subtitle') }}</p>
  </div>
  <div class="page-actions">
    <button class="btn btn-outline">
      <i data-lucide="download" style="width:14px;height:14px"></i>
      {{ __('app.export') }}
    </button>
    <button class="btn btn-primary">
      <i data-lucide="plus" style="width:14px;height:14px"></i>
      {{ __('dashboard.create_report') }}
    </button>
  </div>
</div>

{{-- Daily Briefing --}}
<div class="briefing-card mb-20">
  <div class="briefing-header">
    <span class="briefing-label">{{ __('dashboard.daily_briefing') }}</span>
    <span class="briefing-badge">
      <i data-lucide="trending-up" style="width:13px;height:13px"></i>
      8.2% QoQ
    </span>
  </div>
  <h2 class="briefing-title">{{ $briefing['title'] ?? __('dashboard.momentum_strong') }}</h2>
  <p class="briefing-text">{{ $briefing['text'] ?? __('dashboard.briefing_text') }}</p>

  <div class="briefing-stats">
    <div class="briefing-stat">
      <div class="briefing-stat-label">{{ __('dashboard.net_revenue') }}</div>
      <div class="briefing-stat-value">{{ $stats['net_revenue'] ?? '$48,295' }}</div>
      <div class="briefing-stat-change text-success">{{ $stats['revenue_change'] ?? '+12.5%' }}</div>
    </div>
    <div class="briefing-stat">
      <div class="briefing-stat-label">{{ __('dashboard.active_users') }}</div>
      <div class="briefing-stat-value">{{ $stats['active_users'] ?? '5,432' }}</div>
      <div class="briefing-stat-change text-success">{{ $stats['users_change'] ?? '+5.8%' }}</div>
    </div>
    <div class="briefing-stat">
      <div class="briefing-stat-label">{{ __('dashboard.orders') }}</div>
      <div class="briefing-stat-value">{{ $stats['orders'] ?? '1,248' }}</div>
      <div class="briefing-stat-change text-danger">{{ $stats['orders_change'] ?? '-3.1%' }}</div>
    </div>
    <div class="briefing-stat">
      <div class="briefing-stat-label">{{ __('dashboard.conversion') }}</div>
      <div class="briefing-stat-value">{{ $stats['conversion'] ?? '3.24%' }}</div>
      <div class="briefing-stat-change text-success">{{ $stats['conversion_change'] ?? '+1.2%' }}</div>
    </div>
  </div>

  <div class="briefing-tags">
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <span class="briefing-tag">
        <i data-lucide="zap" style="width:12px;height:12px;color:var(--warning)"></i>
        {{ __('dashboard.campaign_roi_strong') }}
      </span>
      <span class="briefing-tag">
        <i data-lucide="shield-check" style="width:12px;height:12px;color:var(--success)"></i>
        SLA 98.1%
      </span>
      <span class="briefing-tag">
        <i data-lucide="trending-up" style="width:12px;height:12px;color:var(--primary)"></i>
        {{ __('dashboard.retention_improving') }}
      </span>
    </div>
    <a class="briefing-link" href="{{ route('dashboard.analytics') }}">
      {{ __('dashboard.open_analytics') }}
      <i data-lucide="arrow-right" style="width:13px;height:13px"></i>
    </a>
  </div>
</div>

{{-- KPI Row --}}
<div class="kpi-grid mb-20">
  <div class="kpi-card">
    <div class="kpi-label">MRR</div>
    <div class="kpi-value">{{ $stats['mrr'] ?? '$128.4K' }}</div>
    <div class="kpi-change up">
      <i data-lucide="trending-up" style="width:12px;height:12px"></i>
      7.1%
    </div>
  </div>
  <div class="kpi-card">
    <div class="kpi-label">{{ __('dashboard.churn_risk') }}</div>
    <div class="kpi-value">{{ $stats['churn_risk'] ?? '2.8%' }}</div>
    <div class="kpi-change" style="color:var(--text-muted)">
      <i data-lucide="minus" style="width:12px;height:12px"></i>
      {{ __('app.stable') }}
    </div>
  </div>
  <div class="kpi-card">
    <div class="kpi-label">{{ __('dashboard.nps_score') }}</div>
    <div class="kpi-value">{{ $stats['nps'] ?? '58' }}</div>
    <div class="kpi-change up">
      <i data-lucide="trending-up" style="width:12px;height:12px"></i>
      +4
    </div>
  </div>
  <div class="kpi-card">
    <div class="kpi-label">{{ __('dashboard.refund_rate') }}</div>
    <div class="kpi-value">{{ $stats['refund_rate'] ?? '0.9%' }}</div>
    <div class="kpi-change down">
      <i data-lucide="trending-down" style="width:12px;height:12px"></i>
      -0.2%
    </div>
  </div>
</div>

{{-- Middle Row: Team Pulse + Revenue Flow --}}
<div class="grid grid-2 mb-20" style="grid-template-columns:1fr 1.5fr">

  {{-- Team Pulse --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">{{ __('dashboard.team_pulse') }}</div>
      </div>
    </div>
    <div class="card-body" style="padding:0">
      <div style="padding:0 20px">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid var(--border-light)">
          <span style="font-size:13px;color:var(--text-secondary)">{{ __('dashboard.open_conversations') }}</span>
          <span style="font-size:15px;font-weight:700;color:var(--text-primary)">{{ $teamPulse['open_conv'] ?? 27 }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid var(--border-light)">
          <span style="font-size:13px;color:var(--text-secondary)">{{ __('dashboard.avg_response_time') }}</span>
          <span style="font-size:15px;font-weight:700;color:var(--text-primary)">{{ $teamPulse['avg_response'] ?? '11m' }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid var(--border-light)">
          <span style="font-size:13px;color:var(--text-secondary)">{{ __('dashboard.critical_tickets') }}</span>
          <span style="font-size:15px;font-weight:700;color:var(--danger)">{{ $teamPulse['critical'] ?? 3 }}</span>
        </div>
      </div>

      {{-- Team messages --}}
      @foreach($teamPulse['messages'] ?? $defaultMessages ?? [] as $msg)
      <div style="display:flex;align-items:center;gap:10px;padding:12px 20px;border-bottom:1px solid var(--border-light)">
        <div class="avatar avatar-sm avatar-placeholder" style="width:32px;height:32px;font-size:11px">
          {{ strtoupper(substr($msg['name'] ?? 'U', 0, 2)) }}
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:12.5px;font-weight:600;color:var(--text-primary)">{{ $msg['name'] ?? '' }}</div>
          <div style="font-size:11.5px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $msg['message'] ?? '' }}</div>
        </div>
        <span style="font-size:11px;color:var(--text-muted);flex-shrink:0">{{ $msg['time'] ?? '' }}</span>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Revenue Flow --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">{{ __('dashboard.revenue_flow') }}</div>
      </div>
      <div style="display:flex;gap:4px">
        <button class="btn btn-sm btn-outline" onclick="setChartPeriod('monthly',this)">{{ __('app.monthly') }}</button>
        <button class="btn btn-sm btn-outline" onclick="setChartPeriod('weekly',this)">{{ __('app.weekly') }}</button>
        <button class="btn btn-sm btn-outline" onclick="setChartPeriod('daily',this)">{{ __('app.daily') }}</button>
      </div>
    </div>
    <div class="card-body" style="padding:16px 20px">
      <div style="display:flex;gap:16px;margin-bottom:16px;font-size:12.5px;font-weight:600">
        <span style="display:flex;align-items:center;gap:6px">
          <span style="width:8px;height:8px;border-radius:50%;background:var(--primary);display:inline-block"></span>
          {{ __('dashboard.revenue') }} $48,295
        </span>
        <span style="display:flex;align-items:center;gap:6px">
          <span style="width:8px;height:8px;border-radius:50%;background:var(--success);display:inline-block"></span>
          {{ __('dashboard.expenses') }} $28,450
        </span>
        <span style="display:flex;align-items:center;gap:6px">
          <span style="width:8px;height:8px;border-radius:50%;background:var(--warning);display:inline-block"></span>
          {{ __('dashboard.profit') }} $19,845
        </span>
      </div>
      <canvas id="revenue-chart" height="180"></canvas>
    </div>
  </div>

</div>

{{-- Bottom Row: Pipeline + Transactions + Execution Board --}}
<div class="grid mb-20" style="grid-template-columns:1fr 1.6fr 1fr;gap:20px">

  {{-- Pipeline Health --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">{{ __('dashboard.pipeline_health') }}</div>
    </div>
    <div class="card-body">
      @foreach($pipeline ?? [
        ['label' => 'Lead', 'amount' => '$124,500', 'color' => '#0d9488', 'pct' => 85],
        ['label' => 'Qualified', 'amount' => '$98,200', 'color' => '#3b82f6', 'pct' => 70],
        ['label' => 'Proposal', 'amount' => '$72,800', 'color' => '#f59e0b', 'pct' => 50],
        ['label' => 'Negotiation', 'amount' => '$48,500', 'color' => '#10b981', 'pct' => 35],
      ] as $item)
      <div style="margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;margin-bottom:5px">
          <span style="font-size:12.5px;color:var(--text-secondary)">{{ $item['label'] }}</span>
          <span style="font-size:12.5px;font-weight:700;color:var(--text-primary)">{{ $item['amount'] }}</span>
        </div>
        <div style="height:5px;background:var(--surface-2);border-radius:3px;overflow:hidden">
          <div style="height:100%;width:{{ $item['pct'] }}%;background:{{ $item['color'] }};border-radius:3px;transition:width 1s ease"></div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Recent Transactions --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">{{ __('dashboard.recent_transactions') }}</div>
      <a href="#" class="btn btn-sm btn-outline">{{ __('app.view_all') }}</a>
    </div>
    <div class="table-wrapper" style="border:none;border-radius:0">
      <table class="table">
        <thead>
          <tr>
            <th>{{ __('app.transaction') }}</th>
            <th>{{ __('app.customer') }}</th>
            <th>{{ __('app.date') }}</th>
            <th>{{ __('app.amount') }}</th>
            <th>{{ __('app.status') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($transactions ?? $defaultTransactions ?? [] as $t)
          <tr>
            <td class="td-bold">{{ $t['id'] ?? '#TXN-0000' }}</td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div class="avatar avatar-sm avatar-placeholder" style="width:28px;height:28px;font-size:10px">
                  {{ strtoupper(substr($t['customer'] ?? 'U', 0, 1)) }}
                </div>
                {{ $t['customer'] ?? '' }}
              </div>
            </td>
            <td>{{ $t['date'] ?? '' }}</td>
            <td class="td-bold">{{ $t['amount'] ?? '' }}</td>
            <td>
              <span class="badge badge-{{ $t['status_class'] ?? 'gray' }}">{{ $t['status'] ?? '' }}</span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- Execution Board --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">{{ __('dashboard.execution_board') }}</div>
      <button class="btn btn-sm btn-primary">
        <i data-lucide="plus" style="width:12px;height:12px"></i>
        {{ __('app.add_task') }}
      </button>
    </div>
    <div class="card-body" style="padding:12px 16px">
      @foreach($tasks ?? $defaultTasks ?? [] as $task)
      <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid var(--border-light)">
        <input type="checkbox" {{ ($task['done'] ?? false) ? 'checked' : '' }}
               style="margin-top:2px;accent-color:var(--primary);width:14px;height:14px;cursor:pointer">
        <div style="flex:1;min-width:0">
          <div style="font-size:12.5px;font-weight:{{ ($task['done'] ?? false) ? '400' : '600' }};
               color:{{ ($task['done'] ?? false) ? 'var(--text-muted)' : 'var(--text-primary)' }};
               text-decoration:{{ ($task['done'] ?? false) ? 'line-through' : 'none' }}">
            {{ $task['title'] ?? '' }}
          </div>
          <div style="font-size:11px;color:var(--text-muted)">{{ $task['due'] ?? '' }}</div>
        </div>
        <span class="badge badge-{{ $task['priority_class'] ?? 'gray' }}" style="font-size:10px">
          {{ $task['priority'] ?? '' }}
        </span>
      </div>
      @endforeach
    </div>
  </div>

</div>

{{-- Bottom Row: Activity + Team + Sales by Region --}}
<div class="grid grid-3 mb-20">

  {{-- Recent Activity --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">{{ __('dashboard.recent_activity') }}</div>
    </div>
    <div class="card-body">
      @foreach($activities ?? $defaultActivities ?? [] as $act)
      <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:14px">
        <div style="width:8px;height:8px;border-radius:50%;background:{{ $act['color'] ?? 'var(--primary)' }};
             margin-top:5px;flex-shrink:0"></div>
        <div>
          <p style="font-size:12.5px;color:var(--text-primary);line-height:1.4">
            <strong>{{ $act['user'] ?? '' }}</strong> {{ $act['action'] ?? '' }}
          </p>
          <span style="font-size:11px;color:var(--text-muted)">{{ $act['time'] ?? '' }}</span>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Team Members --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">{{ __('dashboard.team_members') }}</div>
      <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline">{{ __('app.view_all') }}</a>
    </div>
    <div class="card-body" style="padding:8px 16px">
      @foreach($teamMembers ?? $defaultTeam ?? [] as $member)
      <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border-light)">
        <div class="avatar avatar-sm avatar-placeholder" style="width:36px;height:36px">
          {{ strtoupper(substr($member['name'] ?? 'U', 0, 1)) }}
        </div>
        <div style="flex:1">
          <div style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $member['name'] ?? '' }}</div>
          <div style="font-size:11.5px;color:var(--text-muted)">{{ $member['role'] ?? '' }}</div>
        </div>
        <div class="status-dot {{ $member['status'] ?? 'offline' }}"></div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Sales by Region --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">{{ __('dashboard.sales_by_region') }}</div>
    </div>
    <div class="card-body">
      @foreach($regions ?? [
        ['flag'=>'🇫🇷','name'=>'France','amount'=>'$45,820','color'=>'var(--primary)'],
        ['flag'=>'🇨🇲','name'=>'Cameroun','amount'=>'$28,450','color'=>'var(--info)'],
        ['flag'=>'🇧🇪','name'=>'Belgique','amount'=>'$21,380','color'=>'var(--warning)'],
        ['flag'=>'🇨🇩','name'=>'Congo','amount'=>'$18,240','color'=>'var(--danger)'],
      ] as $region)
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
        <span style="font-size:18px">{{ $region['flag'] }}</span>
        <div style="flex:1">
          <div style="display:flex;justify-content:space-between;margin-bottom:3px">
            <span style="font-size:12.5px;color:var(--text-secondary)">{{ $region['name'] }}</span>
            <span style="font-size:12.5px;font-weight:700;color:var(--text-primary)">{{ $region['amount'] }}</span>
          </div>
          <div style="height:4px;background:var(--surface-2);border-radius:2px">
            <div style="height:100%;width:{{ $region['pct'] ?? '60' }}%;background:{{ $region['color'] }};border-radius:2px"></div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
  const textColor = isDark ? '#94a3b8' : '#94a3b8';

  const ctx = document.getElementById('revenue-chart')?.getContext('2d');
  if (!ctx) return;

  const labels = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
  const revenues = [4200,5600,4900,5200,6200,6800,6400,7800,8200,9500,8800,9200];
  const expenses = [2100,2600,2200,2500,2800,3100,2900,3600,3800,4200,3900,4100];

  window.revenueChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Revenus',
          data: revenues,
          backgroundColor: 'rgba(13,148,136,0.85)',
          borderRadius: 4,
          borderSkipped: false,
        },
        {
          label: 'Dépenses',
          data: expenses,
          backgroundColor: 'rgba(16,185,129,0.65)',
          borderRadius: 4,
          borderSkipped: false,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: isDark ? '#1a2235' : '#fff',
          titleColor: isDark ? '#f1f5f9' : '#0f172a',
          bodyColor: isDark ? '#94a3b8' : '#475569',
          borderColor: isDark ? '#1e293b' : '#e2e8f0',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 10,
          callbacks: {
            label: ctx => ` $${ctx.parsed.y.toLocaleString()}`
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: textColor, font: { size: 11 } }
        },
        y: {
          grid: { color: gridColor, drawBorder: false },
          ticks: {
            color: textColor,
            font: { size: 11 },
            callback: v => '$' + (v/1000).toFixed(0) + 'k'
          }
        }
      }
    }
  });
});

function setChartPeriod(period, btn) {
  document.querySelectorAll('[onclick*="setChartPeriod"]').forEach(b => b.classList.remove('btn-primary'));
  btn.classList.add('btn-primary');
  // In a real app, you'd fetch new data from the server
}
</script>
@endpush
