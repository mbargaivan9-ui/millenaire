{{--
    |--------------------------------------------------------------------------
    | admin/finance.blade.php — Tableau de Bord Financier
    |--------------------------------------------------------------------------
    | Phase 10 — Section 11.2 — Gestion Financière Admin
    | Recettes, paiements Mobile Money, familles à jour, exports
    --}}

@extends('layouts.app')

@section('title', app()->getLocale() === 'fr' ? 'Finance & Paiements' : 'Finance & Payments')

@push('styles')
<style>
/* ─── Finance KPI Strip ──────────────────────────────────────────────────── */
.finance-strip { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem; }
@media(max-width:991px) { .finance-strip { grid-template-columns:repeat(2,1fr); } }
@media(max-width:575px) { .finance-strip { grid-template-columns:1fr; } }

.finance-kpi {
    border-radius:var(--radius-lg); padding:1.25rem;
    background:var(--surface); border:1.5px solid var(--border);
}
.finance-kpi-amount { font-size:1.6rem;font-weight:900; }
.finance-kpi-label  { font-size:.78rem;color:var(--text-muted);font-weight:600;margin-top:.25rem; }
.finance-kpi-sub    { font-size:.72rem;color:var(--text-muted);margin-top:.35rem; }

/* ─── Payment row ────────────────────────────────────────────────────────── */
.payment-row { display:flex;align-items:center;gap:.75rem;padding:.6rem 0;border-bottom:1px solid var(--border-light); }
.payment-row:last-child { border:none; }
.op-badge { padding:.2rem .6rem;border-radius:12px;font-size:.7rem;font-weight:700; }
.op-orange { background:#fff4e6;color:#FF6600; }
.op-mtn    { background:#fffde7;color:#856900; }
.status-pill { padding:.2rem .65rem;border-radius:12px;font-size:.72rem;font-weight:700; }
.status-success { background:#ecfdf5;color:#059669; }
.status-pending { background:#fffbeb;color:#d97706; }
.status-failed  { background:#fef2f2;color:#dc2626; }
</style>
@endpush

@section('content')

@php
    $isFr    = app()->getLocale() === 'fr';
    $payments = $payments ?? collect();
    $stats    = $stats ?? [];
@endphp

<div class="page-header mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                <i data-lucide="banknote"></i>
            </div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Finance & Paiements' : 'Finance & Payments' }}</h1>
                <p class="page-subtitle text-muted">{{ $isFr ? 'Gestion des frais scolaires' : 'School fees management' }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.export') }}" class="btn btn-light btn-sm">
                <i data-lucide="download" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Exporter Excel' : 'Export Excel' }}
            </a>
            <a href="{{ route('admin.finance.receipts.batch') }}" class="btn btn-primary btn-sm">
                <i data-lucide="file-down" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Reçus en masse' : 'Batch receipts' }}
            </a>
        </div>
    </div>
</div>

{{-- ─── Finance KPI Strip ────────────────────────────────────────────────────── --}}
<div class="finance-strip">
    @php
        $fKpis = [
            ['amount' => $stats['total_collected'] ?? 0, 'label_fr' => 'Total perçu ce mois', 'label_en' => 'Total collected this month', 'color' => '#10b981', 'icon' => 'trending-up', 'fmt' => 'currency'],
            ['amount' => $stats['pending_amount'] ?? 0,  'label_fr' => 'Paiements en attente', 'label_en' => 'Pending payments',          'color' => '#f59e0b', 'icon' => 'clock',       'fmt' => 'currency'],
            ['amount' => $stats['families_uptodate'] ?? 0,'label_fr'=> 'Familles à jour',      'label_en' => 'Up-to-date families',       'color' => '#0d9488', 'icon' => 'users',       'fmt' => 'number'],
            ['amount' => $stats['total_students'] > 0 ? round(($stats['families_uptodate'] ?? 0) / $stats['total_students'] * 100) : 0,
              'label_fr' => 'Taux de recouvrement', 'label_en' => 'Collection rate', 'color' => '#3b82f6', 'icon' => 'percent', 'fmt' => 'percent'],
        ];
    @endphp
    @foreach($fKpis as $k)
    <div class="finance-kpi">
        <div style="width:36px;height:36px;border-radius:10px;background:{{ $k['color'] }}15;display:flex;align-items:center;justify-content:center;margin-bottom:.75rem">
            <i data-lucide="{{ $k['icon'] }}" style="width:18px;color:{{ $k['color'] }}"></i>
        </div>
        <div class="finance-kpi-amount" style="color:{{ $k['color'] }}">
            @if($k['fmt'] === 'currency') XAF {{ number_format($k['amount'], 0, ',', ' ') }}
            @elseif($k['fmt'] === 'percent') {{ $k['amount'] }}%
            @else {{ number_format($k['amount']) }}
            @endif
        </div>
        <div class="finance-kpi-label">{{ $isFr ? $k['label_fr'] : $k['label_en'] }}</div>
    </div>
    @endforeach
</div>

{{-- ─── Content ──────────────────────────────────────────────────────────────── --}}
<div class="row gy-4">

    {{-- Recent Transactions --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">
                    <i data-lucide="list" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Transactions Récentes' : 'Recent Transactions' }}
                </h6>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="search-payments"
                           placeholder="{{ $isFr ? 'Rechercher...' : 'Search...' }}"
                           style="width:180px" oninput="filterPayments(this.value)">
                    <select class="form-select form-select-sm" style="width:120px" id="filter-operator" onchange="filterPayments()">
                        <option value="">{{ $isFr ? 'Tous' : 'All' }}</option>
                        <option value="orange">Orange</option>
                        <option value="mtn">MTN</option>
                    </select>
                    <select class="form-select form-select-sm" style="width:120px" id="filter-status" onchange="filterPayments()">
                        <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
                        <option value="success">{{ $isFr ? 'Succès' : 'Success' }}</option>
                        <option value="pending">{{ $isFr ? 'En attente' : 'Pending' }}</option>
                        <option value="failed">{{ $isFr ? 'Échoué' : 'Failed' }}</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ $isFr ? 'Transaction' : 'Transaction' }}</th>
                                <th>{{ $isFr ? 'Famille' : 'Family' }}</th>
                                <th>{{ $isFr ? 'Opérateur' : 'Operator' }}</th>
                                <th style="text-align:right">{{ $isFr ? 'Montant' : 'Amount' }}</th>
                                <th style="text-align:center">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                <th>{{ $isFr ? 'Date' : 'Date' }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="payments-tbody">
                            @forelse($payments as $payment)
                            <tr class="payment-tr"
                                data-search="{{ strtolower($payment->student?->user?->name . ' ' . $payment->phone_number . ' ' . $payment->transaction_ref) }}"
                                data-operator="{{ $payment->operator }}"
                                data-status="{{ $payment->status }}">
                                <td>
                                    <span style="font-family:monospace;font-size:.78rem;color:var(--text-secondary)">
                                        {{ Str::limit($payment->transaction_ref ?? '—', 16) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold" style="font-size:.83rem">{{ $payment->student?->user?->name }}</div>
                                    <div style="font-size:.72rem;color:var(--text-muted)">{{ $payment->student?->classe?->name }}</div>
                                </td>
                                <td>
                                    <span class="op-badge op-{{ $payment->operator }}">
                                        {{ $payment->operator === 'orange' ? '🟠 Orange' : '🟡 MTN' }}
                                    </span>
                                    <div style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem">{{ $payment->phone_number }}</div>
                                </td>
                                <td style="text-align:right;font-weight:700;color:var(--text-primary)">
                                    XAF {{ number_format($payment->amount, 0, ',', ' ') }}
                                </td>
                                <td style="text-align:center">
                                    <span class="status-pill status-{{ $payment->status }}">
                                        {{ $payment->status === 'success' ? ($isFr ? 'Succès' : 'Success')
                                            : ($payment->status === 'pending' ? ($isFr ? 'En attente' : 'Pending')
                                            : ($isFr ? 'Échoué' : 'Failed')) }}
                                    </span>
                                </td>
                                <td style="font-size:.78rem;color:var(--text-muted)">
                                    {{ $payment->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    @if($payment->status === 'success')
                                    <a href="{{ route('admin.finance.receipt', $payment->id) }}" class="btn btn-sm btn-light" target="_blank">
                                        <i data-lucide="file-text" style="width:12px"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">{{ $isFr ? 'Aucune transaction.' : 'No transactions.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Right: pending families + chart --}}
    <div class="col-lg-4">
        {{-- Revenue chart --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ $isFr ? 'Recettes (6 derniers mois)' : 'Revenue (last 6 months)' }}</h6>
            </div>
            <div class="card-body">
                <canvas id="miniRevenueChart" height="150"></canvas>
            </div>
        </div>

        {{-- Families behind on payments --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">
                    <i data-lucide="alert-circle" style="width:16px;color:#ef4444" class="me-2"></i>
                    {{ $isFr ? 'Familles en retard' : 'Families behind' }}
                </h6>
                <span class="badge bg-danger">{{ $stats['families_overdue'] ?? 0 }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($overdueStudents ?? [] as $s)
                <div class="payment-row px-3">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate" style="font-size:.83rem">{{ $s->user?->name }}</div>
                        <div style="font-size:.72rem;color:var(--text-muted)">{{ $s->classe?->name }}</div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:.8rem;font-weight:700;color:#ef4444">XAF {{ number_format($s->amount_due ?? 0, 0, ',', ' ') }}</div>
                        <a href="{{ route('payment.mobile-money', ['student_id' => $s->id]) }}" style="font-size:.7rem;color:var(--primary)">
                            {{ $isFr ? 'Relancer' : 'Remind' }}
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-3 text-muted" style="font-size:.83rem">
                    ✅ {{ $isFr ? 'Toutes les familles sont à jour.' : 'All families are up to date.' }}
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const months6 = @json($stats['revenue_6months_labels'] ?? ['M-5','M-4','M-3','M-2','M-1','Ce mois']);
    const data6   = @json($stats['revenue_6months'] ?? [0,0,0,0,0,0]);
    new Chart(document.getElementById('miniRevenueChart'), {
        type: 'line',
        data: {
            labels: months6,
            datasets: [{
                data: data6,
                borderColor: '#0d9488', backgroundColor: 'rgba(13,148,136,.08)',
                tension: .4, fill: true, pointRadius: 3,
            }]
        },
        options: {
            responsive: true, plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'XAF ' + (v/1000).toFixed(0) + 'k' } } }
        }
    });
});

window.filterPayments = function() {
    const q   = (document.getElementById('search-payments')?.value ?? '').toLowerCase().trim();
    const op  = document.getElementById('filter-operator')?.value ?? '';
    const st  = document.getElementById('filter-status')?.value ?? '';
    document.querySelectorAll('.payment-tr').forEach(tr => {
        const matchQ  = !q  || (tr.dataset.search ?? '').includes(q);
        const matchOp = !op || tr.dataset.operator === op;
        const matchSt = !st || tr.dataset.status   === st;
        tr.style.display = matchQ && matchOp && matchSt ? '' : 'none';
    });
};
</script>
@endpush


