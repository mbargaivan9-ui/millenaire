@extends('layouts.app')

@section('title', app()->getLocale() === 'fr' ? 'Mes Reçus' : 'My Receipts')

@php $isFr = app()->getLocale() === 'fr'; @endphp

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="page-header mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="page-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb)">
                    <i data-lucide="file-text"></i>
                </div>
                <div>
                    <h1 class="page-title">{{ $isFr ? 'Mes Reçus de Paiement' : 'My Payment Receipts' }}</h1>
                    <p class="page-subtitle text-muted">{{ $isFr ? 'Consultez et téléchargez vos reçus' : 'View and download your receipts' }}</p>
                </div>
            </div>
            <a href="{{ route('parent.payments.index') }}" class="btn btn-primary btn-sm">
                <i data-lucide="arrow-left" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Retour au Paiement' : 'Back to Payments' }}
            </a>
        </div>
    </div>

    {{-- Receipts Table --}}
    <div class="card">
        <div class="card-header">
            <i data-lucide="file-text" style="width:16px;height:16px"></i>
            <span>{{ $isFr ? 'Reçus' : 'Receipts' }}</span>
            <span style="margin-left:auto;font-size:12px;color:var(--text-muted)">
                {{ $receipts->total() }} {{ $isFr ? 'total' : 'total' }}
            </span>
        </div>
        <div class="card-body">
            @if($receipts->count() > 0)
            <div style="overflow-x:auto">
                <table class="table">
                    <thead style="background:var(--surface-secondary)">
                        <tr>
                            <th style="width:150px">{{ $isFr ? 'N° Reçu' : 'Receipt #' }}</th>
                            <th style="width:120px">{{ $isFr ? 'Montant' : 'Amount' }}</th>
                            <th>{{ $isFr ? 'Étudiant' : 'Student' }}</th>
                            <th style="width:120px">{{ $isFr ? 'Date' : 'Date' }}</th>
                            <th style="width:80px">{{ $isFr ? 'Statut' : 'Status' }}</th>
                            <th style="width:120px">{{ $isFr ? 'Actions' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receipts as $receipt)
                        <tr>
                            <td>
                                <span style="font-family:monospace;background:var(--surface-secondary);padding:.2rem .5rem;border-radius:4px;font-size:.85rem;font-weight:600">
                                    {{ $receipt->receipt_number }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight:700;color:var(--primary)">
                                    {{ number_format($receipt->payment->amount ?? 0, 0, ',', ' ') }} XAF
                                </span>
                            </td>
                            <td>
                                @if($receipt->payment->student)
                                    <span style="font-weight:500">{{ $receipt->payment->student->user->name }}</span>
                                    <br>
                                    <span style="font-size:.8rem;color:var(--text-muted)">{{ $receipt->payment->student->classe->name ?? 'N/A' }}</span>
                                @else
                                    <span style="color:var(--text-muted)">{{ $isFr ? 'Non disponible' : 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size:.85rem">
                                    {{ $receipt->created_at?->format('d/m/Y') }}<br>
                                    <span style="color:var(--text-muted);font-size:.75rem">{{ $receipt->created_at?->format('H:i') }}</span>
                                </span>
                            </td>
                            <td>
                                @php
                                    $status = $receipt->payment->status ?? 'pending';
                                    $badgeClass = match($status) {
                                        'success' => 'bg-success',
                                        'completed' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'failed' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}" style="font-size:.75rem">
                                    {{ $receipt->payment->getStatusLabel() ?? ucfirst($status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1" style="align-items:center">
                                    <a href="{{ route('parent.payments.receipt', $receipt->payment) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="{{ $isFr ? 'Voir' : 'View' }}"
                                       style="padding:.35rem .6rem;font-size:.75rem">
                                        <i data-lucide="eye" style="width:12px;height:12px"></i>
                                    </a>
                                    <a href="{{ route('parent.payments.download', $receipt) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="{{ $isFr ? 'Télécharger' : 'Download' }}"
                                       style="padding:.35rem .6rem;font-size:.75rem">
                                        <i data-lucide="download" style="width:12px;height:12px"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($receipts->hasPages())
            <div style="margin-top:2rem;display:flex;justify-content:center">
                {{ $receipts->links() }}
            </div>
            @endif

            @else
            <div style="text-align:center;padding:3rem 1rem;color:var(--text-muted)">
                <i data-lucide="inbox" style="width:48px;height:48px;margin-bottom:1rem;opacity:.5"></i>
                <p style="font-size:1.1rem;font-weight:600">{{ $isFr ? 'Aucun reçu trouvé' : 'No receipts found' }}</p>
                <p style="font-size:.9rem;margin-top:.5rem">
                    {{ $isFr ? 'Effectuez un paiement pour générer un reçu' : 'Make a payment to generate a receipt' }}
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-reload icon rendering
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
@endpush
@endsection
