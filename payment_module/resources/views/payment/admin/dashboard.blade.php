@extends('layouts.app')

@section('title', 'Finance — Paiements Mobile Money')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/payment/schoolpay.css') }}">
@endpush

@section('content')

<div class="sp-admin-wrap">

  {{-- ─── En-tête ──────────────────────────────────────── --}}
  <div class="sp-admin-header">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div>
        <h1>Dashboard <span style="color:var(--primary)">Paiements</span></h1>
        <p>Suivi en temps réel — Orange Money &amp; MTN Mobile Money</p>
      </div>
      <div style="display:flex;gap:10px;align-items:center">
        <span class="sp-badge sp-badge--live">
          <span class="sp-badge__dot"></span>LIVE
        </span>
        <span style="font-size:12px;color:var(--text-secondary)">Actualisation toutes les 5s</span>
        <a href="{{ asset('') }}" class="sp-btn sp-btn--ghost" style="font-size:13px;padding:8px 16px">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
          Exporter Excel
        </a>
      </div>
    </div>
  </div>

  {{-- ─── Comptes opérateurs ─────────────────────────── --}}
  <div class="sp-accounts">
    <div class="sp-account sp-account--orange">
      <div class="sp-account__icon">
        <svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg" width="30" height="30">
          <circle cx="30" cy="30" r="28" fill="rgba(255,255,255,.15)"/>
          <circle cx="30" cy="30" r="20" fill="rgba(255,255,255,.9)"/>
          <text x="30" y="38" text-anchor="middle" font-family="Arial Black" font-weight="900" font-size="18" fill="#FF6600">O</text>
        </svg>
      </div>
      <div>
        <div class="sp-account__lbl">Compte Orange Money</div>
        <div class="sp-account__val" id="admin-om-balance">
          {{ number_format($stats['orange_total'] ?? 0, 0, ',', ' ') }} FCFA
        </div>
      </div>
    </div>

    <div class="sp-account sp-account--mtn">
      <div class="sp-account__icon">
        <svg viewBox="0 0 70 28" xmlns="http://www.w3.org/2000/svg" width="38" height="16">
          <text x="0" y="22" font-family="Arial Black" font-weight="900" font-size="24" fill="#1A1A1A">MTN</text>
        </svg>
      </div>
      <div>
        <div class="sp-account__lbl">Compte MTN MoMo</div>
        <div class="sp-account__val" id="admin-mtn-balance">
          {{ number_format($stats['mtn_total'] ?? 0, 0, ',', ' ') }} FCFA
        </div>
      </div>
    </div>

    <div class="sp-account sp-account--total">
      <div class="sp-account__icon">💰</div>
      <div>
        <div class="sp-account__lbl">Total collecté (mois)</div>
        <div class="sp-account__val" id="admin-total-balance">
          {{ number_format($stats['month_total'] ?? 0, 0, ',', ' ') }} FCFA
        </div>
      </div>
    </div>
  </div>

  {{-- ─── Stats ──────────────────────────────────────── --}}
  <div class="sp-stats">
    <div class="sp-stat sp-stat--primary">
      <div class="sp-stat__icon">💳</div>
      <div class="sp-stat__val" id="admin-today-total">
        {{ number_format($stats['today_total'] ?? 0, 0, ',', ' ') }} F
      </div>
      <div class="sp-stat__lbl">Recettes aujourd'hui</div>
    </div>
    <div class="sp-stat sp-stat--success">
      <div class="sp-stat__icon">✅</div>
      <div class="sp-stat__val" id="admin-success-count">{{ $stats['today_count'] ?? 0 }}</div>
      <div class="sp-stat__lbl">Paiements validés (today)</div>
    </div>
    <div class="sp-stat sp-stat--warning">
      <div class="sp-stat__icon">⏳</div>
      <div class="sp-stat__val" id="admin-pending-count">{{ $stats['pending_count'] ?? 0 }}</div>
      <div class="sp-stat__lbl">En attente</div>
    </div>
    <div class="sp-stat">
      <div class="sp-stat__icon">📊</div>
      <div class="sp-stat__val" id="admin-success-rate">{{ $stats['success_rate'] ?? 0 }}%</div>
      <div class="sp-stat__lbl">Taux de succès (mois)</div>
    </div>
  </div>

  {{-- ─── Notifications temps réel ───────────────────── --}}
  <div style="margin-bottom:24px">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
      <h3 style="font-size:15px;font-weight:700;margin:0">🔔 Notifications en temps réel</h3>
      <span id="notif-count" style="background:rgba(239,68,68,.12);color:var(--danger);font-size:11px;font-weight:700;padding:2px 8px;border-radius:100px">0</span>
    </div>
    <div class="sp-notifs" id="admin-notifs">
      <div style="font-size:13px;color:var(--text-muted);padding:12px;text-align:center">
        Aucune notification récente
      </div>
    </div>
  </div>

  {{-- ─── Table transactions ────────────────────────── --}}
  <div class="sp-txn-card">
    <div class="sp-txn-card__header">
      <h2 class="sp-txn-card__title">📊 Transactions — Historique complet</h2>
      <div style="display:flex;align-items:center;gap:10px">
        <div class="sp-live-badge">
          <div class="sp-live-badge__dot"></div>LIVE
        </div>
        {{-- Filtres --}}
        <select id="admin-filter-op" class="sp-select" style="width:auto;font-size:12px;padding:6px 28px 6px 10px">
          <option value="">Tous opérateurs</option>
          <option value="orange">Orange Money</option>
          <option value="mtn">MTN MoMo</option>
        </select>
        <select id="admin-filter-status" class="sp-select" style="width:auto;font-size:12px;padding:6px 28px 6px 10px">
          <option value="">Tous statuts</option>
          <option value="success">Validé</option>
          <option value="pending">En attente</option>
          <option value="failed">Échoué</option>
        </select>
      </div>
    </div>

    <div class="sp-table-wrap">
      <table class="sp-table">
        <thead>
          <tr>
            <th>Référence</th>
            <th>Élève</th>
            <th>Classe</th>
            <th>Tranche</th>
            <th>Opérateur</th>
            <th>Numéro</th>
            <th>Montant</th>
            <th>Heure</th>
            <th>Statut</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="admin-txn-body">
          @forelse($payments as $p)
          <tr>
            <td style="font-family:monospace;font-size:11px;color:var(--text-muted)">{{ $p->transaction_ref }}</td>
            <td style="font-weight:600">{{ $p->student?->user?->name ?? 'N/A' }}</td>
            <td style="color:var(--text-secondary);font-size:12px">{{ $p->student?->classe?->name ?? '—' }}</td>
            <td style="color:var(--text-secondary);font-size:12px">{{ $p->tranche ?? '—' }}</td>
            <td>
              @if($p->operator === 'orange')
                <span class="sp-op-tag sp-op-tag--orange">🟠 Orange Money</span>
              @else
                <span class="sp-op-tag sp-op-tag--mtn">🟡 MTN MoMo</span>
              @endif
            </td>
            <td style="font-family:monospace;font-size:12px">{{ $p->phone }}</td>
            <td class="sp-amount-col sp-amount-col--{{ $p->operator }}">
              {{ number_format($p->amount, 0, ',', ' ') }} F
            </td>
            <td style="color:var(--text-muted);font-size:12px">{{ $p->created_at->format('H:i') }}</td>
            <td>
              <span class="sp-status-badge sp-status-badge--{{ $p->status_color }}">
                {{ $p->status_label }}
              </span>
            </td>
            <td>
              @if($p->isSuccess())
                <a href="{{ route('payment.receipt.show', $p->transaction_ref) }}"
                   class="sp-btn sp-btn--ghost" style="font-size:11px;padding:5px 10px"
                   target="_blank">Reçu</a>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="10" style="text-align:center;color:var(--text-muted);padding:40px">
              Aucune transaction enregistrée
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($payments->hasPages())
    <div style="padding:16px 22px;border-top:1px solid var(--border)">
      {{ $payments->links() }}
    </div>
    @endif
  </div>

</div>

@endsection

@push('scripts')
<script>
const SP_ADMIN_STATS = '{{ route("schoolpay.admin.stats") }}';

// ─── Polling dashboard admin ────────────────────────────────
(function () {
  let lastCount = {{ $stats['today_count'] ?? 0 }};
  const notifications = [];

  const fmt = n => new Intl.NumberFormat('fr-CM').format(n) + ' FCFA';

  async function fetchStats() {
    try {
      const data = await fetch(SP_ADMIN_STATS, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json());

      // Mettre à jour les compteurs
      setText('admin-today-total',   fmt(data.today_total));
      setText('admin-total-balance', fmt(data.month_total));
      setText('admin-om-balance',    fmt(data.orange_total) );
      setText('admin-mtn-balance',   fmt(data.mtn_total));
      setText('admin-success-count', data.today_count);
      setText('admin-pending-count', data.pending_count);
      setText('admin-success-rate',  data.success_rate + '%');

      // Nouvelles transactions → notification
      if (data.today_count > lastCount) {
        const newCount = data.today_count - lastCount;
        const newTxns  = data.recent_transactions.slice(0, newCount);
        newTxns.forEach(addNotification);
        lastCount = data.today_count;
      }

      // Mettre à jour les premières lignes du tableau
      if (data.recent_transactions?.length) {
        updateTableRows(data.recent_transactions);
      }

    } catch (e) { /* réseau ok */ }
  }

  function setText(id, val) { const el = document.getElementById(id); if (el) el.textContent = val; }

  function addNotification(txn) {
    notifications.unshift(txn);
    const container = document.getElementById('admin-notifs');
    if (!container) return;

    // Effacer le placeholder si vide
    if (container.querySelector('[style*="text-align"]')) container.innerHTML = '';

    const div = document.createElement('div');
    div.className = `sp-notif sp-notif--${txn.operator}`;
    div.innerHTML = `
      <div class="sp-notif__icon">${txn.operator === 'orange' ? '🟠' : '🟡'}</div>
      <div class="sp-notif__body" style="flex:1">
        <div class="sp-notif__title">Paiement reçu — ${txn.student_name}</div>
        <div class="sp-notif__sub">${txn.formatted_total} • ${txn.tranche} • ${txn.operator_label}</div>
      </div>
      <div class="sp-notif__time">${txn.time}</div>
    `;
    container.insertBefore(div, container.firstChild);

    // Limiter à 5 notifs
    while (container.children.length > 5) container.removeChild(container.lastChild);

    // Mettre à jour le compteur
    document.getElementById('notif-count').textContent = Math.min(notifications.length, 5);
  }

  function updateTableRows(txns) {
    const tbody = document.getElementById('admin-txn-body');
    if (!tbody || !txns.length) return;

    // Ajouter les nouvelles lignes en tête
    const existing = Array.from(tbody.querySelectorAll('tr')).map(tr => tr.dataset.ref);
    const newTxns  = txns.filter(t => !existing.includes(t.transaction_ref));

    newTxns.forEach(t => {
      const tr = document.createElement('tr');
      tr.dataset.ref = t.transaction_ref;
      tr.className   = 'sp-row--new';
      tr.innerHTML = `
        <td style="font-family:monospace;font-size:11px;color:var(--text-muted)">${t.transaction_ref}</td>
        <td style="font-weight:600">${t.student_name}</td>
        <td style="color:var(--text-secondary);font-size:12px">${t.classe}</td>
        <td style="color:var(--text-secondary);font-size:12px">${t.tranche}</td>
        <td><span class="sp-op-tag sp-op-tag--${t.operator}">${t.operator === 'orange' ? '🟠' : '🟡'} ${t.operator_label}</span></td>
        <td style="font-family:monospace;font-size:12px">${t.phone}</td>
        <td class="sp-amount-col sp-amount-col--${t.operator}">${t.formatted_total}</td>
        <td style="color:var(--text-muted);font-size:12px">${t.time}</td>
        <td><span class="sp-status-badge sp-status-badge--${t.status_color}">${t.status_label}</span></td>
        <td>${t.receipt_url ? `<a href="${t.receipt_url}" class="sp-btn sp-btn--ghost" style="font-size:11px;padding:5px 10px" target="_blank">Reçu</a>` : ''}</td>
      `;
      tbody.insertBefore(tr, tbody.firstChild);
    });
  }

  // Polling toutes les 5s
  fetchStats();
  setInterval(fetchStats, 5000);
})();
</script>
@endpush
