{{-- payment/receipt.blade.php --}}
@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Reçu de paiement' : 'Payment Receipt')
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
                <i data-lucide="receipt"></i>
            </div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Reçu de Paiement' : 'Payment Receipt' }}</h1>
                <p class="page-subtitle text-muted">{{ $payment->transaction_ref }}</p>
            </div>
        </div>
        <a href="{{ route('payment.receipt.pdf', $payment->transaction_ref) }}"
           class="btn btn-primary btn-sm">
            <i data-lucide="download" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Télécharger PDF' : 'Download PDF' }}
        </a>
    </div>
</div>

<div class="row justify-content-center">
<div class="col-md-6">

{{-- Receipt card --}}
<div class="card" style="border-radius:var(--radius-lg);box-shadow:var(--shadow-lg)">
    {{-- Header --}}
    <div style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));padding:2rem;text-align:center;border-radius:var(--radius-lg) var(--radius-lg) 0 0;color:#fff">
        <div style="font-size:2.5rem;margin-bottom:.5rem">
            {{ $payment->operator === 'orange' ? '🟠' : '💛' }}
        </div>
        <div style="font-size:2rem;font-weight:900;letter-spacing:-1px">
            XAF {{ number_format($payment->amount, 0, ',', ' ') }}
        </div>
        <div style="opacity:.8;font-size:.88rem;margin-top:.3rem">
            {{ $isFr ? 'Frais de scolarité réglés' : 'School fees paid' }}
        </div>
    </div>

    {{-- Status badge --}}
    <div style="text-align:center;margin:-14px 0 0">
        @if($payment->status === 'success')
        <span style="background:#ecfdf5;color:#059669;border:2px solid #a7f3d0;border-radius:20px;padding:.35rem 1.2rem;font-size:.82rem;font-weight:700;display:inline-block">
            ✓ {{ $isFr ? 'Paiement confirmé' : 'Payment confirmed' }}
        </span>
        @elseif($payment->status === 'pending')
        <span style="background:#fffbeb;color:#d97706;border:2px solid #fde68a;border-radius:20px;padding:.35rem 1.2rem;font-size:.82rem;font-weight:700;display:inline-block">
            ⏳ {{ $isFr ? 'En attente' : 'Pending' }}
        </span>
        @else
        <span style="background:#fef2f2;color:#dc2626;border:2px solid #fca5a5;border-radius:20px;padding:.35rem 1.2rem;font-size:.82rem;font-weight:700;display:inline-block">
            ✗ {{ $isFr ? 'Échec' : 'Failed' }}
        </span>
        @endif
    </div>

    {{-- Details table --}}
    <div class="card-body">
        <div style="background:var(--surface-2);border-radius:10px;padding:1rem;margin-top:.5rem">
            @foreach([
                [$isFr ? 'Référence' : 'Reference',        $payment->transaction_ref],
                [$isFr ? 'Opérateur' : 'Operator',         ucfirst($payment->operator) . ' Money'],
                [$isFr ? 'Téléphone' : 'Phone',            $payment->phone_number],
                [$isFr ? 'Élève' : 'Student',              $payment->student?->user?->name ?? '—'],
                ['Matricule',                               $payment->student?->matricule ?? '—'],
                [$isFr ? 'Classe' : 'Class',               $payment->student?->classe?->name ?? '—'],
                [$isFr ? 'Type de frais' : 'Fee type',     $payment->description ?? $isFr ? 'Frais scolaires' : 'School fees'],
                [$isFr ? 'Date' : 'Date',                  $payment->confirmed_at?->format('d/m/Y H:i') ?? $payment->created_at?->format('d/m/Y H:i')],
            ] as [$lbl, $val])
            <div class="d-flex justify-content-between mb-2 pb-2" style="border-bottom:1px solid var(--border-light);font-size:.84rem">
                <span style="color:var(--text-muted)">{{ $lbl }}</span>
                <span class="fw-semibold text-end" style="max-width:60%;word-break:break-all">{{ $val }}</span>
            </div>
            @endforeach
        </div>

        <p class="text-center mt-3 mb-0" style="font-size:.72rem;color:var(--text-muted)">
            {{ $isFr
                ? 'Ce reçu constitue une preuve de paiement officielle.'
                : 'This receipt constitutes official proof of payment.' }}
        </p>
    </div>
</div>

<div class="text-center mt-3">
    <a href="{{ route('parent.dashboard') }}" class="btn btn-light">
        {{ $isFr ? '← Retour au tableau de bord' : '← Back to dashboard' }}
    </a>
</div>

</div>
</div>

@endsection
