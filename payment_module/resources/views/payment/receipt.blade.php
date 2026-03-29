@extends('layouts.app')
@section('title', 'Reçu de paiement — ' . $payment->transaction_ref)
@push('styles')
<link rel="stylesheet" href="{{ asset('css/payment/schoolpay.css') }}">
<style>
.receipt-wrap { max-width: 520px; margin: 0 auto; }
.receipt-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-xl);
}
.receipt-header {
  padding: 28px 28px 20px;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: #fff; text-align: center; position: relative;
}
.receipt-header__logo { font-size: 32px; margin-bottom: 8px; }
.receipt-header__school { font-size: 18px; font-weight: 800; letter-spacing: -.3px; }
.receipt-header__title { font-size: 13px; opacity: .85; margin-top: 4px; }
.receipt-status {
  margin: 20px 28px; padding: 16px;
  background: rgba(16,185,129,.08); border: 1px solid rgba(16,185,129,.2);
  border-radius: 12px; text-align: center;
}
.receipt-status__icon { font-size: 36px; margin-bottom: 8px; }
.receipt-status__label { font-size: 20px; font-weight: 800; color: var(--success); }
.receipt-status__amount { font-size: 28px; font-weight: 800; margin-top: 6px; }
.receipt-op {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  margin-top: 10px;
}
.receipt-op__badge {
  display: flex; align-items: center; gap: 8px; padding: 7px 14px;
  border-radius: 100px; font-size: 13px; font-weight: 700;
}
.receipt-op__badge--orange { background: rgba(255,102,0,.1); color: #FF6600; }
.receipt-op__badge--mtn    { background: rgba(255,204,0,.15); color: #A67C00; }

.receipt-details { padding: 0 28px 10px; }
.receipt-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 11px 0; border-bottom: 1px dashed var(--border);
  font-size: 13px;
}
.receipt-row:last-child { border-bottom: none; }
.receipt-row__key { color: var(--text-secondary); }
.receipt-row__val { font-weight: 600; text-align: right; max-width: 60%; }

.receipt-footer {
  margin: 0 28px 28px;
  padding: 14px; background: var(--surface-2); border-radius: 12px;
  text-align: center; font-size: 11px; color: var(--text-muted);
  line-height: 1.7;
}
.receipt-actions { padding: 0 28px 28px; display: flex; gap: 10px; }
</style>
@endpush

@section('content')
<div class="receipt-wrap">
  <div class="receipt-card">

    {{-- Header --}}
    <div class="receipt-header">
      <div class="receipt-header__logo">🎓</div>
      <div class="receipt-header__school">{{ config('app.name') }}</div>
      <div class="receipt-header__title">Reçu de Paiement Scolaire</div>
    </div>

    {{-- Statut & Montant --}}
    <div class="receipt-status">
      <div class="receipt-status__icon">✅</div>
      <div class="receipt-status__label">Paiement Confirmé</div>
      <div class="receipt-status__amount">
        {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
      </div>
      <div class="receipt-op">
        <span class="receipt-op__badge receipt-op__badge--{{ $payment->operator }}">
          {{ $payment->operator === 'orange' ? '🟠 Orange Money' : '🟡 MTN MoMo' }}
        </span>
      </div>
    </div>

    {{-- Détails --}}
    <div class="receipt-details">
      @foreach([
        ['N° Reçu',           $payment->receipt_number ?? $payment->transaction_ref],
        ['Référence transaction', $payment->transaction_ref],
        ['Élève',             $payment->student?->user?->name ?? 'N/A'],
        ['Classe',            $payment->student?->classe?->name ?? 'N/A'],
        ['Type de frais',     $payment->fee_type ?? 'Frais scolaires'],
        ['Tranche',           $payment->tranche ?? '—'],
        ['Sous-total',        number_format($payment->amount, 0, ',', ' ') . ' FCFA'],
        ['Frais de service',  number_format($payment->fees, 0, ',', ' ') . ' FCFA'],
        ['TOTAL PAYÉ',        number_format($payment->total_amount, 0, ',', ' ') . ' FCFA'],
        ['Opérateur',         $payment->operator_label],
        ['N° Mobile Money',   $payment->phone],
        ['Ref. opérateur',    $payment->operator_txn_id ?? '—'],
        ['Date & Heure',      $payment->completed_at?->format('d/m/Y à H:i') ?? '—'],
        ['Payé par',          $payment->payer?->name ?? 'N/A'],
        ['Mode',              $payment->is_sandbox ? '🧪 Simulation Sandbox' : '✅ Production'],
      ] as [$k,$v])
      <div class="receipt-row">
        <span class="receipt-row__key">{{ $k }}</span>
        <span class="receipt-row__val">{{ $v }}</span>
      </div>
      @endforeach
    </div>

    {{-- Footer --}}
    <div class="receipt-footer">
      Ce reçu certifie que le paiement a été effectué et enregistré<br>
      dans le système de gestion de <strong>{{ config('app.name') }}</strong>.<br>
      Conservez ce document pour vos archives.
    </div>

    {{-- Actions --}}
    <div class="receipt-actions">
      <button onclick="window.print()" class="sp-btn sp-btn--ghost" style="flex:1">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
        Imprimer
      </button>
      <a href="{{ route('schoolpay.parent.index') }}" class="sp-btn sp-btn--primary" style="flex:1">
        Nouveau paiement
      </a>
    </div>

  </div>
</div>
@endsection
