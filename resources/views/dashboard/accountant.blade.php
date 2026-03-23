@extends('layouts.app')
@section('title', 'Tableau de Bord Comptable')
@section('content')
<style>
  .accountant-header {
    background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);
    color: white;
    padding: 40px 0;
    margin: -20px -15px 0;
    border-radius: 0 0 20px 20px;
  }

  .kpi-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
  }

  .kpi-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
  }

  .kpi-card.success {
    background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);
  }

  .kpi-card.warning {
    background: linear-gradient(135deg, #f5a623 0%, #ffb366 100%);
  }

  .kpi-card.info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  }

  .kpi-card .card-body {
    color: white;
    text-align: center;
    padding: 24px;
  }

  .kpi-value {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 12px 0;
    letter-spacing: -0.5px;
  }

  .kpi-label {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    opacity: 0.9;
  }

  .data-table th {
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 11px;
    color: #374151;
    padding: 14px;
  }

  .data-table tbody tr:hover {
    background: #f9fafb;
  }

  .data-table tbody td {
    padding: 14px;
    border-bottom: 1px solid #e5e7eb;
  }
</style>

<div class="container-fluid">
  {{-- Header --}}
  <div class="accountant-header mb-5">
    <div class="container-fluid px-4">
      <h1 style="font-weight: 700; font-size: 2rem; margin-bottom: 8px;">
        <i data-lucide="calculator" style="width: 32px; height: 32px; display: inline-block; margin-right: 12px; vertical-align: middle;"></i>
        Espace Comptabilité
      </h1>
      <p style="margin: 0; opacity: 0.9; font-size: 15px;">Gestion financière centralisée de l'établissement</p>
    </div>
  </div>

  {{-- KPI Cards --}}
  <div class="container-fluid px-4 mb-5">
    <div class="row g-4">
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card kpi-card success">
          <div class="card-body">
            <div class="kpi-label">💰 Total Collecté</div>
            <div class="kpi-value">{{ number_format($totalCollected ?? 0, 0, ',', ' ') }} FCFA</div>
            <small style="opacity: 0.9;">Montant encaissé</small>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card kpi-card warning">
          <div class="card-body">
            <div class="kpi-label">⏳ En Attente</div>
            <div class="kpi-value">{{ number_format($pending ?? 0, 0, ',', ' ') }} FCFA</div>
            <small style="opacity: 0.9;">À recouvrer</small>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card kpi-card info">
          <div class="card-body">
            <div class="kpi-label">📊 Taux de Collecte</div>
            <div class="kpi-value">{{ $collectionRate ?? 0 }}%</div>
            <small style="opacity: 0.9;">Performance</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Tables Section --}}
  <div class="container-fluid px-4 mb-4">
    <div class="row g-4">
      {{-- Recent Payments --}}
      <div class="col-lg-8">
        <div class="card border-0 rounded-3" style="box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
          <div style="padding: 20px; border-bottom: 1px solid #e5e7eb;">
            <h5 style="margin: 0; font-weight: 700;">
              <i data-lucide="history" style="width: 18px; height: 18px; display: inline-block; margin-right: 8px; vertical-align: middle;"></i>
              Derniers Paiements
            </h5>
          </div>
          <div class="table-responsive">
            <table class="table data-table mb-0">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Élève</th>
                  <th>Montant</th>
                  <th>Méthode</th>
                  <th>Statut</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentPayments ?? [] as $p)
                <tr>
                  <td style="font-size: 12px;">{{ $p->created_at->format('d/m/Y') }}</td>
                  <td style="font-weight: 600; color: #1f2937;">{{ $p->student->user->name ?? '—' }}</td>
                  <td><strong style="color: #1f2937;">{{ number_format($p->amount, 0, ',', ' ') }} FCFA</strong></td>
                  <td>
                    @if($p->method === 'online') 💳 En ligne
                    @elseif($p->method === 'cash') 💵 Espèces
                    @else 🏦 Virement
                    @endif
                  </td>
                  <td>
                    @if($p->status === 'completed')
                      <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">✓ Payé</span>
                    @else
                      <span style="background: #fef08a; color: #854d0e; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">⏳ En attente</span>
                    @endif
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center py-4" style="color: #9ca3af;">Aucun paiement récent</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Payment Methods Stats --}}
      <div class="col-lg-4">
        <div class="card border-0 rounded-3" style="box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
          <div style="padding: 20px; border-bottom: 1px solid #e5e7eb;">
            <h5 style="margin: 0; font-weight: 700;">
              <i data-lucide="bar-chart-3" style="width: 18px; height: 18px; display: inline-block; margin-right: 8px; vertical-align: middle;"></i>
              Modes de Paiement
            </h5>
          </div>
          <div style="padding: 20px;">
            @if($paymentMethodStats)
              @foreach($paymentMethodStats as $method => $count)
              <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                  <span style="font-weight: 600; color: #374151;">
                    @if($method === 'online') 💳 En ligne
                    @elseif($method === 'cash') 💵 Espèces
                    @else 🏦 Virement
                    @endif
                  </span>
                  <strong style="color: #1f2937;">{{ $count }}</strong>
                </div>
                <div style="background: #e5e7eb; border-radius: 8px; height: 8px; overflow: hidden;">
                  <div style="background: linear-gradient(90deg, #10b981 0%, #14b8a6 100%); height: 100%; width: {{ optional(collect($paymentMethodStats)->max()) && optional(collect($paymentMethodStats)->max()) > 0 ? ($count / optional(collect($paymentMethodStats)->max()) * 100) : 0 }}%;"></div>
                </div>
              </div>
              @endforeach
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  if (window.lucide) {
    lucide.createIcons();
  }
</script>
@endpush
@endsection
