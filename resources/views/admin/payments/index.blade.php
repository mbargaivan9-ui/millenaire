@extends('layouts.app')

@section('title', 'Gestion des Paiements')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Paiements</span>
    </div>
    <h1 class="page-title">Gestion des Paiements</h1>
    <p class="page-subtitle">Suivi des paiements par les familles</p>
  </div>
</div>

{{-- Payments Table --}}
<div class="card">
  <div class="card-header">
    <i data-lucide="credit-card" style="width:16px;height:16px"></i>
    <span>Paiements</span>
    <span style="margin-left:auto;font-size:12px;color:var(--text-muted)">
      {{ $payments?->total() ?? 0 }} total
    </span>
  </div>
  <div class="card-body">
    <div style="overflow-x:auto">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Élève</th>
            <th>Frais</th>
            <th>Montant</th>
            <th>Statut</th>
            <th style="text-align:right">Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse($payments ?? [] as $p)
          <tr>
            <td>
              <div style="font-size:11px;color:var(--text-muted);font-weight:600">#{{ $p->id }}</div>
            </td>
            <td>
              <div style="font-weight:600;font-size:13px">{{ $p->student?->user?->name ?? '-' }}</div>
            </td>
            <td>
              <div style="font-size:12px">{{ $p->fee?->name ?? '-' }}</div>
            </td>
            <td>
              <div style="font-weight:600;font-size:13px">{{ number_format($p->amount ?? 0, 0) }} CFA</div>
            </td>
            <td>
              @php
                $statusColor = match($p->status ?? 'pending') {
                  'paid' => 'var(--success)',
                  'pending' => 'var(--warning)',
                  'overdue' => 'var(--danger)',
                  default => 'var(--text-muted)'
                };
                $statusLabel = match($p->status ?? 'pending') {
                  'paid' => 'Payé',
                  'pending' => 'En attente',
                  'overdue' => 'Retard',
                  default => '-'
                };
              @endphp
              <span style="background:{{ $statusColor }}20;color:{{ $statusColor }};padding:4px 8px;border-radius:4px;
                           font-size:11px;font-weight:600">
                {{ $statusLabel }}
              </span>
            </td>
            <td style="text-align:right">
              <div style="font-size:11px;color:var(--text-muted)">
                {{ $p->created_at?->format('d/m/Y') ?? '-' }}
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" style="text-align:center;padding:40px 20px">
              <i data-lucide="inbox" style="width:40px;height:40px;color:var(--text-muted);margin-bottom:16px;display:block"></i>
              <div style="color:var(--text-muted);font-size:13px">Aucun paiement</div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($payments?->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
      <small style="color:var(--text-muted);font-size:12px">
        Affichage {{ $payments->firstItem() }} à {{ $payments->lastItem() }} sur {{ $payments->total() }} paiements
      </small>
      <div>
        {!! $payments->links('pagination::simple-tailwind') !!}
      </div>
    </div>
    @endif
  </div>
</div>

@endsection


