<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; color:#0f172a; background:#fff; }
    .page { padding: 16mm 20mm; max-width: 148mm; margin: 0 auto; }

    .receipt-header { text-align:center; border-bottom: 2px solid #0d9488; padding-bottom: 14px; margin-bottom: 14px; }
    .receipt-logo   { font-size:28px; margin-bottom:6px; }
    .school-name    { font-size:14px; font-weight:bold; color:#0d9488; margin-bottom:2px; }
    .school-sub     { font-size:9px; color:#94a3b8; }

    .receipt-title  { font-size:13px; font-weight:bold; text-align:center; text-transform:uppercase; letter-spacing:.8px; margin:14px 0; color:#0f172a; }

    .info-table     { width:100%; border-collapse:collapse; margin-bottom:14px; }
    .info-table td  { padding:5px 0; border-bottom:1px solid #f1f5f9; font-size:10px; }
    .info-table td:first-child { color:#64748b; width:45%; font-weight:600; }
    .info-table td:last-child  { font-weight:700; color:#0f172a; }

    .amount-box {
        background:linear-gradient(135deg,#0d9488,#14b8a6);
        border-radius:10px; padding:14px; text-align:center; color:#fff; margin-bottom:14px;
    }
    .amount-label { font-size:9px; opacity:.8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; }
    .amount-value { font-size:22px; font-weight:900; }

    .status-badge { display:inline-block; padding:4px 14px; border-radius:20px; font-size:10px; font-weight:700; }
    .status-success { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }

    .footer { margin-top:16px; padding-top:10px; border-top:1px solid #e2e8f0; text-align:center; font-size:8.5px; color:#94a3b8; }
    .qr-section { text-align:center; margin-top:10px; }
    .qr-label   { font-size:8px; color:#94a3b8; margin-top:4px; }
</style>
</head>
<body>
<div class="page">

@php
    $settings = \App\Models\EstablishmentSetting::getInstance();
    $isFr = true; // receipts always bilingual
@endphp

<div class="receipt-header">
    <div class="receipt-logo">🏫</div>
    <div class="school-name">{{ $settings->platform_name ?? 'Collège Millénaire Bilingue' }}</div>
    <div class="school-sub">{{ $settings->address }} · {{ $settings->phone }}</div>
</div>

<div class="receipt-title">{{ $isFr ? 'Reçu de Paiement / Payment Receipt' : 'Payment Receipt' }}</div>

<div style="text-align:center;margin-bottom:12px">
    <span class="status-badge status-success">✓ {{ $isFr ? 'Paiement Confirmé / Payment Confirmed' : 'Payment Confirmed' }}</span>
</div>

<div class="amount-box">
    <div class="amount-label">{{ $isFr ? 'Montant payé / Amount paid' : 'Amount paid' }}</div>
    <div class="amount-value">XAF {{ number_format($payment->amount, 0, ',', ' ') }}</div>
</div>

<table class="info-table">
    <tr>
        <td>{{ $isFr ? 'N° Transaction' : 'Transaction ID' }}</td>
        <td style="font-family:monospace;font-size:9px">{{ $payment->transaction_ref }}</td>
    </tr>
    <tr>
        <td>{{ $isFr ? 'Opérateur' : 'Operator' }}</td>
        <td>{{ $payment->operator === 'orange' ? '🟠 Orange Money' : '🟡 MTN MoMo' }}</td>
    </tr>
    <tr>
        <td>{{ $isFr ? 'Numéro' : 'Phone' }}</td>
        <td>+237 {{ $payment->phone_number }}</td>
    </tr>
    <tr>
        <td>{{ $isFr ? 'Élève / Student' : 'Student' }}</td>
        <td>{{ $payment->student?->user?->name }} ({{ $payment->student?->matricule }})</td>
    </tr>
    <tr>
        <td>{{ $isFr ? 'Classe / Class' : 'Class' }}</td>
        <td>{{ $payment->student?->classe?->name }}</td>
    </tr>
    <tr>
        <td>{{ $isFr ? 'Type de frais' : 'Fee type' }}</td>
        <td>{{ $payment->fee_type ?? ($isFr ? 'Frais de Scolarité' : 'School Fees') }}</td>
    </tr>
    <tr>
        <td>{{ $isFr ? 'Date & Heure' : 'Date & Time' }}</td>
        <td>{{ $payment->created_at?->format('d/m/Y à H:i') }}</td>
    </tr>
    <tr>
        <td>{{ $isFr ? 'Payé par' : 'Paid by' }}</td>
        <td>{{ $payment->payer_name ?? auth()->user()->name }}</td>
    </tr>
</table>

@if($payment->transaction_ref)
<div class="qr-section">
    {!! QrCode::size(80)->generate(route('payment.verify', $payment->transaction_ref)) !!}
    <div class="qr-label">{{ $isFr ? 'Scannez pour vérifier l\'authenticité de ce reçu' : 'Scan to verify receipt authenticity' }}</div>
</div>
@endif

<div class="footer">
    <p>{{ $settings->platform_name }} — {{ $settings->address }}</p>
    <p style="margin-top:4px">{{ $isFr ? 'Ce reçu est un document officiel. Conservez-le précieusement.' : 'This receipt is an official document. Keep it safely.' }}</p>
    <p style="margin-top:4px">{{ $isFr ? 'Généré le' : 'Generated on' }} {{ now()->format('d/m/Y à H:i') }}</p>
</div>

</div>
</body>
</html>
