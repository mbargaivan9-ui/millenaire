<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification de reçu — Millenaire Connect</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f4f8; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .verify-card { background: #fff; border-radius: 20px; padding: 40px; max-width: 480px; width: 100%; box-shadow: 0 8px 32px rgba(0,0,0,.1); text-align: center; }
        .icon { font-size: 4rem; display: block; margin-bottom: 16px; }
        h2 { font-size: 1.5rem; font-weight: 800; margin-bottom: 8px; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0; text-align: left; font-size: .9rem; }
        .info-label { color: #888; }
        .info-value { font-weight: 600; }
    </style>
</head>
<body>
<div class="verify-card">

    @if($valid && $payment)
        <span class="icon">✅</span>
        <h2 style="color:#198754">Reçu Authentique</h2>
        <p style="color:#555; margin-bottom: 24px">Ce reçu de paiement est valide et a été émis par Millenaire Connect.</p>

        <div style="background:#f8f9fa; border-radius:12px; padding:20px; margin-bottom: 24px;">
            <div class="info-row">
                <span class="info-label">N° Reçu</span>
                <span class="info-value">{{ $receipt->receipt_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Montant</span>
                <span class="info-value" style="color:#198754; font-size:1.1rem">{{ number_format($payment->amount, 0, ',', ' ') }} XAF</span>
            </div>
            <div class="info-row">
                <span class="info-label">Opérateur</span>
                <span class="info-value">{{ strtoupper($payment->operator) }}</span>
            </div>
            @if($payment->student)
            <div class="info-row">
                <span class="info-label">Élève</span>
                <span class="info-value">{{ $payment->student->user->name }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Type</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</span>
            </div>
            <div class="info-row" style="border:none">
                <span class="info-label">Date</span>
                <span class="info-value">{{ $payment->completed_at?->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div style="font-size:.8rem; color:#888">
            🔒 Vérifié par Millenaire Connect — {{ now()->format('d/m/Y H:i') }}
        </div>

    @else
        <span class="icon">❌</span>
        <h2 style="color:#dc3545">Reçu Invalide</h2>
        <p style="color:#555">Ce QR code ne correspond à aucun paiement confirmé dans notre système. Il peut être falsifié ou expiré.</p>
        <div style="margin-top:24px; font-size:.8rem; color:#888">Millenaire Connect — Vérification de documents</div>
    @endif

</div>
</body>
</html>
