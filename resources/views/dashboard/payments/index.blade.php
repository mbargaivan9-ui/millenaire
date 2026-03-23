@extends('layouts.app')

@section('title', 'Suivi des Paiements')

@section('content')
<style>
  :root {
    --primary: #2563eb;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
  }

  .payment-header {
    background: linear-gradient(135deg, var(--primary) 0%, #06b6d4 100%);
    color: white;
    padding: 40px 0;
    margin: -20px -15px 0;
    border-radius: 0 0 20px 20px;
  }

  .payment-header h1 {
    font-weight: 700;
    font-size: 2rem;
    letter-spacing: -0.5px;
  }

  .stat-card {
    position: relative;
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
  }

  .stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12);
  }

  .stat-card.collected {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  }

  .stat-card.outstanding {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
  }

  .stat-card.pending {
    background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
  }

  .stat-card.rate {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  }

  .stat-card .card-body {
    color: white;
    position: relative;
    z-index: 2;
    padding: 24px;
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
    z-index: 1;
  }

  .stat-icon {
    font-size: 3rem;
    opacity: 0.12;
    margin-bottom: 12px;
  }

  .stat-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    opacity: 0.9;
    margin-bottom: 8px;
  }

  .stat-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 8px;
    letter-spacing: -0.5px;
  }

  .stat-footer {
    font-size: 12px;
    opacity: 0.88;
    font-weight: 500;
  }

  .filter-section {
    background: white;
    border: none;
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    padding: 24px;
  }

  .table-card {
    background: white;
    border: none;
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
  }

  .table-card .table {
    margin-bottom: 0;
  }

  .table-card thead {
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
  }

  .table-card thead th {
    font-weight: 700;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
    color: #374151;
    border: none;
    padding: 16px;
  }

  .table-card tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.2s;
  }

  .table-card tbody tr:hover {
    background: #f9fafb;
  }

  .table-card tbody td {
    padding: 16px;
    color: #374151;
    border: none;
    font-size: 13px;
    vertical-align: middle;
  }

  .badge-status {
    border-radius: 8px;
    font-weight: 700;
    font-size: 11px;
    padding: 6px 12px;
    display: inline-block;
  }

  .badge-status.completed {
    background: #dcfce7;
    color: #166534;
  }

  .badge-status.pending {
    background: #fef08a;
    color: #854d0e;
  }

  .badge-status.failed {
    background: #fee2e2;
    color: #991b1b;
  }

  .filter-btn {
    border-radius: 8px;
    font-weight: 600;
    border: none;
    padding: 10px 16px;
    transition: all 0.3s ease;
  }

  .filter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .form-control,
  .form-select {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 10px 12px;
    font-size: 13px;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  }

  .pagination {
    margin: 0;
  }

  .pagination .page-link {
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    color: var(--primary);
    margin: 0 2px;
  }

  .pagination .page-link:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
  }

  .pagination .page-item.active .page-link {
    background: var(--primary);
    border-color: var(--primary);
  }
</style>

<div class="container-fluid">
  {{-- Header --}}
  <div class="payment-header mb-5">
    <div class="container-fluid px-4">
      <h1 class="mb-2">
        <i data-lucide="credit-card" style="width: 36px; height: 36px; display: inline-block; margin-right: 14px; vertical-align: middle;"></i>
        Suivi des Frais de Scolarité
      </h1>
      <p class="mb-0 opacity-90" style="font-size: 15px;">Gérez les paiements et les dettes scolaires en temps réel</p>
    </div>
  </div>

  {{-- Statistics Cards --}}
  <div class="container-fluid px-4 mb-5">
    <div class="row g-4">
      {{-- Total Collected --}}
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card collected">
          <div class="card-body">
            <div style="position: relative; z-index: 2;">
              <i data-lucide="check-circle" class="stat-icon"></i>
              <div class="stat-label">Total Collecté</div>
              <div class="stat-value">{{ number_format($totalCollected, 0) }} FCFA</div>
              <div class="stat-footer">Paiements reçus ✓</div>
            </div>
          </div>
        </div>
      </div>

      {{-- Outstanding Amount --}}
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card outstanding">
          <div class="card-body">
            <div style="position: relative; z-index: 2;">
              <i data-lucide="alert-circle" class="stat-icon"></i>
              <div class="stat-label">Montant Dû</div>
              <div class="stat-value">{{ number_format($totalOutstanding, 0) }} FCFA</div>
              <div class="stat-footer">À recouvrer !</div>
            </div>
          </div>
        </div>
      </div>

      {{-- Pending Payments --}}
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card pending">
          <div class="card-body">
            <div style="position: relative; z-index: 2;">
              <i data-lucide="clock" class="stat-icon"></i>
              <div class="stat-label">En Attente</div>
              <div class="stat-value">{{ $pendingCount }}</div>
              <div class="stat-footer">Paiements attendus</div>
            </div>
          </div>
        </div>
      </div>

      {{-- Collection Rate --}}
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card rate">
          <div class="card-body">
            <div style="position: relative; z-index: 2;">
              <i data-lucide="bar-chart-3" class="stat-icon"></i>
              <div class="stat-label">Taux de Collecte</div>
              <div class="stat-value">{{ $collectionRate }}%</div>
              <div class="stat-footer">Performance 📈</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Filters --}}
  <div class="container-fluid px-4 mb-4">
    <div class="filter-section">
      <form method="GET" class="row g-3">
        <div class="col-12 col-sm-6 col-md-3">
          <label style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">Recherche</label>
          <input type="text" name="search" class="form-control" placeholder="Nom de l'élève..." 
                 value="{{ request('search') }}">
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <label style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">Statut</label>
          <select name="status" class="form-select">
            <option value="">Tous les statuts</option>
            <option value="completed" @selected(request('status') == 'completed')>✓ Payé</option>
            <option value="pending" @selected(request('status') == 'pending')>⏳ En attente</option>
            <option value="failed" @selected(request('status') == 'failed')>✗ Échoué</option>
          </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
          <label style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin-bottom: 6px; display: block;">Mois</label>
          <select name="month" class="form-select">
            <option value="">Tous les mois</option>
            @for($i = 1; $i <= 12; $i++)
            <option value="{{ $i }}" @selected(request('month') == $i)>
              {{ \Carbon\Carbon::create(2024, $i)->format('F') }}
            </option>
            @endfor
          </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3" style="display: flex; align-items: flex-end;">
          <button type="submit" class="btn filter-btn w-100" style="background: var(--primary); color: white;">
            <i data-lucide="search" style="width: 14px; height: 14px; display: inline-block; margin-right: 6px; vertical-align: middle;"></i>Filtrer
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Payments Table --}}
  <div class="container-fluid px-4 mb-4">
    <div class="table-card">
      <div style="padding: 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
        <h5 style="margin: 0; font-weight: 700; color: #1f2937;">Historique des Paiements</h5>
        <button class="btn" style="background: linen; color: var(--primary); border-radius: 8px; font-weight: 600; border: none; padding: 8px 12px;">
          <i data-lucide="download" style="width: 14px; height: 14px; display: inline-block; margin-right: 6px; vertical-align: middle;"></i>Exporter
        </button>
      </div>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Élève</th>
              <th>Classe</th>
              <th>Montant</th>
              <th>Méthode</th>
              <th>Statut</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payments as $payment)
            <tr>
              <td>
                <strong style="color: #1f2937;">{{ $payment->student->user->name ?? 'N/A' }}</strong>
              </td>
              <td>
                <span style="background: #dbeafe; color: #1e40af; padding: 6px 10px; border-radius: 6px; font-weight: 600; font-size: 12px;">
                  {{ $payment->student->classe->name ?? 'N/A' }}
                </span>
              </td>
              <td style="font-weight: 700; color: #1f2937;">{{ number_format($payment->amount, 0) }} FCFA</td>
              <td>
                @if($payment->method === 'online')
                  <span style="color: #2563eb; font-weight: 600;">💳 En ligne</span>
                @elseif($payment->method === 'cash')
                  <span style="color: #059669; font-weight: 600;">💵 Espèces</span>
                @else
                  <span style="color: #7c3aed; font-weight: 600;">🏦 Virement</span>
                @endif
              </td>
              <td>
                @if($payment->status === 'completed')
                  <span class="badge-status completed">✓ Payé</span>
                @elseif($payment->status === 'pending')
                  <span class="badge-status pending">⏳ En attente</span>
                @else
                  <span class="badge-status failed">✗ Échoué</span>
                @endif
              </td>
              <td style="font-size: 12px; color: #6b7280;">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
              <td>
                <a href="{{ route('admin.payment.show', $payment) }}" class="btn btn-sm" style="background: #dbeafe; color: #1e40af; border-radius: 6px; font-weight: 600; padding: 6px 10px; border: none; font-size: 12px;">
                  <i data-lucide="eye" style="width: 13px; height: 13px; display: inline-block; margin-right: 4px; vertical-align: middle;"></i>Voir
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-5" style="color: #9ca3af;">
                <i data-lucide="inbox" style="width: 48px; height: 48px; display: block; margin: 0 auto 12px; opacity: 0.5;"></i>
                <strong style="color: #6b7280;">Aucun paiement trouvé</strong>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if($payments->hasPages())
      <div style="padding: 20px 16px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <small style="color: #6b7280; font-size: 12px;">
          Affichage <strong>{{ $payments->firstItem() }}</strong> à <strong>{{ $payments->lastItem() }}</strong> sur <strong>{{ $payments->total() }}</strong> paiements
        </small>
        <div>
          {{ $payments->links('pagination::bootstrap-4') }}
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

@push('scripts')
<script>
  // Initialize Lucide icons
  if (window.lucide) {
    lucide.createIcons();
  }
</script>
@endpush

@endsection
