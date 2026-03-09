{{--
    |--------------------------------------------------------------------------
    | payment/mobile-money.blade.php — Interface Paiement Mobile Money Premium
    |--------------------------------------------------------------------------
    | Phase 10 — Section 11.1 — Interface Paiement Haute Gamme
    | Orange Money + MTN MoMo — Processus Step-by-Step animé
    --}}

@extends('layouts.app')

@section('title', app()->getLocale() === 'fr' ? 'Paiement Mobile Money' : 'Mobile Money Payment')

@push('styles')
<style>
/* ─── Payment Container ───────────────────────────────────────────────────── */
.payment-container { max-width: 600px; margin: 0 auto; }

/* ─── Step Progress Bar ──────────────────────────────────────────────────── */
.step-progress {
    display: flex; align-items: center;
    margin-bottom: 2rem; position: relative;
}
.step-progress::before {
    content: '';
    position: absolute; top: 20px; left: 40px; right: 40px; height: 2px;
    background: var(--border); z-index: 0;
}
.step-item {
    display: flex; flex-direction: column; align-items: center;
    flex: 1; position: relative; z-index: 1;
}
.step-circle {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; font-weight: 700;
    border: 2px solid var(--border);
    background: var(--surface);
    color: var(--text-muted);
    transition: all .3s ease;
}
.step-circle.active   { border-color: var(--primary); background: var(--primary); color: #fff; box-shadow: 0 0 0 4px rgba(13,148,136,.15); }
.step-circle.done     { border-color: #10b981; background: #10b981; color: #fff; }
.step-label { font-size: .7rem; color: var(--text-muted); margin-top: .4rem; text-align: center; font-weight: 600; }
.step-item.active .step-label { color: var(--primary); }
.step-item.done .step-label   { color: #10b981; }

/* ─── Step Panels ─────────────────────────────────────────────────────────── */
.step-panel { display: none; animation: fadeSlide .3s ease; }
.step-panel.active { display: block; }
@keyframes fadeSlide { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }

/* ─── Operator Cards ─────────────────────────────────────────────────────── */
.operator-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.operator-card {
    border: 2px solid var(--border); border-radius: var(--radius-lg);
    padding: 1.5rem; text-align: center; cursor: pointer;
    transition: all .25s ease; position: relative; overflow: hidden;
}
.operator-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.operator-card.selected { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.operator-card.orange.selected { border-color: #FF6600; background: #fff8f0; }
.operator-card.mtn.selected    { border-color: #FFCC00; background: #fffdf0; }

.operator-logo {
    width: 80px; height: 80px; border-radius: 50%; margin: 0 auto .75rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; font-weight: 900;
}
.operator-logo.orange { background: #FF6600; color: white; }
.operator-logo.mtn    { background: #FFCC00; color: #333; }

.operator-name { font-weight: 800; font-size: 1.1rem; margin-bottom: .25rem; }
.operator-desc { font-size: .8rem; color: var(--text-muted); }
.operator-check {
    position: absolute; top: 10px; right: 10px;
    width: 24px; height: 24px; border-radius: 50%;
    background: #10b981; color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; opacity: 0; transition: opacity .2s ease;
}
.operator-card.selected .operator-check { opacity: 1; }

/* ─── Amount Display ─────────────────────────────────────────────────────── */
.amount-card {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border-radius: var(--radius-lg); padding: 2rem; text-align: center;
    color: white; margin-bottom: 1.5rem;
}
.amount-label { font-size: .9rem; opacity: .85; margin-bottom: .5rem; }
.amount-value { font-size: 2.5rem; font-weight: 900; letter-spacing: -1px; }
.amount-currency { font-size: 1.2rem; opacity: .85; }

/* ─── Phone Input ────────────────────────────────────────────────────────── */
.phone-input-wrap {
    display: flex; border: 2px solid var(--border); border-radius: var(--radius-md);
    overflow: hidden; transition: border-color .2s ease;
}
.phone-input-wrap:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
.phone-prefix {
    background: var(--surface-2); padding: .75rem 1rem;
    font-weight: 700; color: var(--text-secondary); border-right: 1px solid var(--border);
    display: flex; align-items: center; gap: .5rem; white-space: nowrap;
}
.phone-number-input {
    border: none; outline: none; flex: 1; padding: .75rem 1rem;
    font-size: 1.1rem; font-weight: 600; letter-spacing: 1px;
    background: transparent;
}

/* ─── Confirmation Summary ───────────────────────────────────────────────── */
.confirm-summary {
    background: var(--surface-2); border-radius: var(--radius-md);
    padding: 1.25rem;
}
.confirm-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: .6rem 0; border-bottom: 1px solid var(--border-light);
}
.confirm-row:last-child { border: none; }
.confirm-label { font-size: .85rem; color: var(--text-muted); }
.confirm-value { font-weight: 700; color: var(--text-primary); }

/* ─── Processing Animation ───────────────────────────────────────────────── */
.processing-container { text-align: center; padding: 2rem 0; }
.processing-ring {
    width: 80px; height: 80px; border-radius: 50%;
    border: 5px solid var(--primary-hover);
    border-top-color: var(--primary);
    animation: spin 1s linear infinite; margin: 0 auto 1.5rem;
}
.processing-steps { text-align: left; max-width: 300px; margin: 1.5rem auto 0; }
.proc-step {
    display: flex; align-items: center; gap: .75rem;
    padding: .5rem; border-radius: 8px; margin-bottom: .5rem;
    font-size: .85rem; transition: all .3s ease;
}
.proc-step.done    { color: #10b981; background: #ecfdf5; }
.proc-step.active  { color: var(--primary); background: var(--primary-bg); font-weight: 600; }
.proc-step.pending { color: var(--text-muted); }
.proc-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.proc-dot.done    { background: #10b981; }
.proc-dot.active  { background: var(--primary); animation: pulse .8s infinite; }
.proc-dot.pending { background: var(--border); }
@keyframes spin  { to { transform: rotate(360deg); } }
@keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.3; } }

/* ─── Success / Error Screens ────────────────────────────────────────────── */
.result-screen { text-align: center; padding: 2rem 0; }
.result-icon { font-size: 4rem; margin-bottom: 1rem; }
.result-title { font-size: 1.4rem; font-weight: 800; margin-bottom: .5rem; }

/* ─── Security Badge ─────────────────────────────────────────────────────── */
.security-badge {
    display: flex; align-items: center; gap: .5rem;
    background: #ecfdf5; border: 1px solid #86efac;
    border-radius: 8px; padding: .5rem .9rem;
    font-size: .78rem; color: #166534; font-weight: 600;
    margin-top: 1rem;
}
</style>
@endpush

@section('content')

@php
    $isFr       = app()->getLocale() === 'fr';
    $student    = $student ?? auth()->user()->student;
    $invoice    = $invoice ?? null;
    $amount     = $amount ?? $invoice?->amount_due ?? 0;
    $feeType    = $feeType ?? ($isFr ? 'Frais de Scolarité' : 'School Fees');
@endphp

<div class="payment-container">

    {{-- ─── Header ──────────────────────────────────────────────────────────── --}}
    <div class="page-header mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
                <i data-lucide="smartphone"></i>
            </div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Paiement Mobile Money' : 'Mobile Money Payment' }}</h1>
                <p class="page-subtitle text-muted">{{ $feeType }} — {{ $student?->user?->name }}</p>
            </div>
        </div>
    </div>

    {{-- ─── Step Progress ───────────────────────────────────────────────────── --}}
    <div class="step-progress mb-4">
        @foreach([
            ['icon' => '1', 'label_fr' => 'Opérateur',     'label_en' => 'Operator'],
            ['icon' => '2', 'label_fr' => 'Numéro',         'label_en' => 'Number'],
            ['icon' => '3', 'label_fr' => 'Confirmation',   'label_en' => 'Confirm'],
            ['icon' => '4', 'label_fr' => 'Traitement',     'label_en' => 'Processing'],
            ['icon' => '5', 'label_fr' => 'Reçu',           'label_en' => 'Receipt'],
        ] as $i => $step)
        <div class="step-item" id="step-item-{{ $i + 1 }}">
            <div class="step-circle {{ $i === 0 ? 'active' : '' }}" id="step-circle-{{ $i + 1 }}">
                {{ $step['icon'] }}
            </div>
            <span class="step-label">{{ $isFr ? $step['label_fr'] : $step['label_en'] }}</span>
        </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body p-4">

            {{-- ══════════ ÉTAPE 1: Sélection Opérateur ══════════ --}}
            <div class="step-panel active" id="panel-1">
                <h5 class="fw-bold mb-1">{{ $isFr ? 'Choisir votre opérateur' : 'Choose your operator' }}</h5>
                <p class="text-muted mb-3" style="font-size:.85rem">
                    {{ $isFr ? 'Sélectionnez le réseau avec lequel vous souhaitez payer.' : 'Select the network you want to pay with.' }}
                </p>

                {{-- Amount display --}}
                <div class="amount-card mb-4">
                    <div class="amount-label">{{ $isFr ? 'Montant à payer' : 'Amount to pay' }}</div>
                    <div class="amount-value">
                        <span class="amount-currency">XAF</span>
                        {{ number_format($amount, 0, ',', ' ') }}
                    </div>
                    <div style="opacity:.75;font-size:.83rem;margin-top:.5rem">{{ $feeType }}</div>
                </div>

                <div class="operator-cards">
                    {{-- Orange Money --}}
                    <div class="operator-card orange" id="op-orange" onclick="selectOperator('orange')">
                        <div class="operator-check">✓</div>
                        <div class="operator-logo orange">O</div>
                        <div class="operator-name">Orange Money</div>
                        <div class="operator-desc">{{ $isFr ? 'Réseau Orange Cameroun' : 'Orange Cameroon network' }}</div>
                    </div>
                    {{-- MTN MoMo --}}
                    <div class="operator-card mtn" id="op-mtn" onclick="selectOperator('mtn')">
                        <div class="operator-check">✓</div>
                        <div class="operator-logo mtn">M</div>
                        <div class="operator-name">MTN MoMo</div>
                        <div class="operator-desc">{{ $isFr ? 'Réseau MTN Cameroun' : 'MTN Cameroon network' }}</div>
                    </div>
                </div>

                <div class="security-badge mt-3">
                    <i data-lucide="shield-check" style="width:16px;color:#16a34a"></i>
                    {{ $isFr ? 'Transaction sécurisée SSL/TLS — Vos données sont protégées' : 'Secure SSL/TLS transaction — Your data is protected' }}
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button class="btn btn-primary px-4" id="btn-step1" disabled onclick="goToStep(2)">
                        {{ $isFr ? 'Continuer' : 'Continue' }}
                        <i data-lucide="arrow-right" style="width:16px" class="ms-2"></i>
                    </button>
                </div>
            </div>

            {{-- ══════════ ÉTAPE 2: Saisie Numéro ══════════ --}}
            <div class="step-panel" id="panel-2">
                <h5 class="fw-bold mb-1">{{ $isFr ? 'Votre numéro de téléphone' : 'Your phone number' }}</h5>
                <p class="text-muted mb-3" style="font-size:.85rem">
                    {{ $isFr ? 'Saisissez le numéro associé à votre compte Mobile Money.' : 'Enter the phone number linked to your Mobile Money account.' }}
                </p>

                <div class="mb-4">
                    <label class="form-label fw-semibold mb-2">{{ $isFr ? 'Numéro de téléphone' : 'Phone number' }}</label>
                    <div class="phone-input-wrap">
                        <div class="phone-prefix">
                            <span id="op-flag">📱</span>
                            🇨🇲 +237
                        </div>
                        <input type="tel" id="phone-input" class="phone-number-input"
                               placeholder="6XX XXX XXX"
                               maxlength="9" inputmode="numeric"
                               oninput="validatePhone(this)">
                    </div>
                    <div id="phone-error" class="text-danger mt-1" style="font-size:.8rem;display:none">
                        {{ $isFr ? 'Format invalide. Ex: 655 123 456 (Orange) ou 670 123 456 (MTN)' : 'Invalid format. Ex: 655 123 456 (Orange) or 670 123 456 (MTN)' }}
                    </div>
                </div>

                <div class="confirm-summary">
                    <div class="confirm-row">
                        <span class="confirm-label">{{ $isFr ? 'Opérateur' : 'Operator' }}</span>
                        <span class="confirm-value" id="summary-operator">—</span>
                    </div>
                    <div class="confirm-row">
                        <span class="confirm-label">{{ $isFr ? 'Montant' : 'Amount' }}</span>
                        <span class="confirm-value" style="color:var(--primary)">XAF {{ number_format($amount, 0, ',', ' ') }}</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-light" onclick="goToStep(1)">
                        <i data-lucide="arrow-left" style="width:16px" class="me-1"></i>
                        {{ $isFr ? 'Retour' : 'Back' }}
                    </button>
                    <button class="btn btn-primary px-4" id="btn-step2" disabled onclick="goToStep(3)">
                        {{ $isFr ? 'Continuer' : 'Continue' }}
                        <i data-lucide="arrow-right" style="width:16px" class="ms-2"></i>
                    </button>
                </div>
            </div>

            {{-- ══════════ ÉTAPE 3: Confirmation ══════════ --}}
            <div class="step-panel" id="panel-3">
                <h5 class="fw-bold mb-1">{{ $isFr ? 'Confirmer votre paiement' : 'Confirm your payment' }}</h5>
                <p class="text-muted mb-3" style="font-size:.85rem">
                    {{ $isFr ? 'Vérifiez les informations avant de lancer le paiement.' : 'Check the details before initiating payment.' }}
                </p>

                <div class="confirm-summary">
                    <div class="confirm-row">
                        <span class="confirm-label">{{ $isFr ? 'Élève' : 'Student' }}</span>
                        <span class="confirm-value">{{ $student?->user?->name }}</span>
                    </div>
                    <div class="confirm-row">
                        <span class="confirm-label">{{ $isFr ? 'Frais concernés' : 'Fee type' }}</span>
                        <span class="confirm-value">{{ $feeType }}</span>
                    </div>
                    <div class="confirm-row">
                        <span class="confirm-label">{{ $isFr ? 'Opérateur' : 'Operator' }}</span>
                        <span class="confirm-value" id="confirm-op">—</span>
                    </div>
                    <div class="confirm-row">
                        <span class="confirm-label">{{ $isFr ? 'Numéro' : 'Phone' }}</span>
                        <span class="confirm-value" id="confirm-phone">—</span>
                    </div>
                    <div class="confirm-row">
                        <span class="confirm-label" style="font-size:1rem;font-weight:700">{{ $isFr ? 'Total à payer' : 'Total amount' }}</span>
                        <span class="confirm-value" style="font-size:1.3rem;color:var(--primary)">
                            XAF {{ number_format($amount, 0, ',', ' ') }}
                        </span>
                    </div>
                </div>

                <div class="alert alert-warning d-flex gap-2 mt-3" style="font-size:.83rem">
                    <i data-lucide="alert-triangle" style="width:18px;flex-shrink:0;margin-top:2px"></i>
                    {{ $isFr
                        ? 'Après confirmation, vous recevrez un code USSD sur votre téléphone pour valider le paiement.'
                        : 'After confirmation, you will receive a USSD code on your phone to validate the payment.' }}
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-light" onclick="goToStep(2)">
                        <i data-lucide="arrow-left" style="width:16px" class="me-1"></i>
                        {{ $isFr ? 'Retour' : 'Back' }}
                    </button>
                    <button class="btn btn-primary px-4" onclick="initiatePayment()">
                        <i data-lucide="send" style="width:16px" class="me-2"></i>
                        {{ $isFr ? 'Lancer le paiement' : 'Initiate payment' }}
                    </button>
                </div>
            </div>

            {{-- ══════════ ÉTAPE 4: Traitement ══════════ --}}
            <div class="step-panel" id="panel-4">
                <div class="processing-container">
                    <div class="processing-ring"></div>
                    <h5 class="fw-bold mb-1">{{ $isFr ? 'Traitement en cours...' : 'Processing...' }}</h5>
                    <p class="text-muted" style="font-size:.85rem">
                        {{ $isFr ? 'Veuillez patienter. Ne fermez pas cette page.' : 'Please wait. Do not close this page.' }}
                    </p>
                    <div class="processing-steps">
                        <div class="proc-step active" id="ps-1">
                            <div class="proc-dot active"></div>
                            {{ $isFr ? 'Envoi de la demande...' : 'Sending request...' }}
                        </div>
                        <div class="proc-step pending" id="ps-2">
                            <div class="proc-dot pending"></div>
                            {{ $isFr ? 'Attente confirmation USSD...' : 'Waiting for USSD confirmation...' }}
                        </div>
                        <div class="proc-step pending" id="ps-3">
                            <div class="proc-dot pending"></div>
                            {{ $isFr ? 'Vérification du paiement...' : 'Verifying payment...' }}
                        </div>
                        <div class="proc-step pending" id="ps-4">
                            <div class="proc-dot pending"></div>
                            {{ $isFr ? 'Génération du reçu...' : 'Generating receipt...' }}
                        </div>
                    </div>
                    <p class="text-muted mt-3" style="font-size:.78rem">
                        <i data-lucide="clock" style="width:14px" class="me-1"></i>
                        {{ $isFr ? 'Vérification toutes les 3 secondes. Timeout: 2 minutes.' : 'Checking every 3 seconds. Timeout: 2 minutes.' }}
                    </p>
                </div>
            </div>

            {{-- ══════════ ÉTAPE 5: Reçu / Résultat ══════════ --}}
            <div class="step-panel" id="panel-5">
                {{-- Success --}}
                <div id="result-success" style="display:none">
                    <div class="result-screen">
                        <div class="result-icon">✅</div>
                        <div class="result-title" style="color:#10b981">
                            {{ $isFr ? 'Paiement confirmé !' : 'Payment confirmed!' }}
                        </div>
                        <p class="text-muted mb-3">
                            {{ $isFr ? 'Votre paiement a été enregistré avec succès.' : 'Your payment has been recorded successfully.' }}
                        </p>
                        <div class="confirm-summary text-start">
                            <div class="confirm-row">
                                <span class="confirm-label">{{ $isFr ? 'N° Transaction' : 'Transaction ID' }}</span>
                                <span class="confirm-value" id="receipt-txn" style="font-family:monospace">—</span>
                            </div>
                            <div class="confirm-row">
                                <span class="confirm-label">{{ $isFr ? 'Montant payé' : 'Amount paid' }}</span>
                                <span class="confirm-value" style="color:#10b981">XAF {{ number_format($amount, 0, ',', ' ') }}</span>
                            </div>
                            <div class="confirm-row">
                                <span class="confirm-label">{{ $isFr ? 'Date & Heure' : 'Date & Time' }}</span>
                                <span class="confirm-value" id="receipt-date">—</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-4">
                            <a href="#" id="download-receipt" class="btn btn-primary">
                                <i data-lucide="download" style="width:14px" class="me-1"></i>
                                {{ $isFr ? 'Télécharger le reçu PDF' : 'Download PDF receipt' }}
                            </a>
                            <a href="{{ route('parent.dashboard') }}" class="btn btn-light">
                                {{ $isFr ? 'Retour au tableau de bord' : 'Back to dashboard' }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Failure --}}
                <div id="result-error" style="display:none">
                    <div class="result-screen">
                        <div class="result-icon">❌</div>
                        <div class="result-title" style="color:#ef4444">
                            {{ $isFr ? 'Paiement échoué' : 'Payment failed' }}
                        </div>
                        <p class="text-muted mb-3" id="error-message">
                            {{ $isFr ? 'Le paiement n\'a pas pu être complété.' : 'The payment could not be completed.' }}
                        </p>
                        <div class="d-flex gap-2 justify-content-center mt-4">
                            <button class="btn btn-primary" onclick="resetPayment()">
                                <i data-lucide="refresh-cw" style="width:14px" class="me-1"></i>
                                {{ $isFr ? 'Réessayer' : 'Try again' }}
                            </button>
                            <a href="{{ route('parent.dashboard') }}" class="btn btn-light">
                                {{ $isFr ? 'Annuler' : 'Cancel' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/**
 * Mobile Money Payment — JavaScript
 * Phase 10 · Section 11.1
 * Step-by-step process, polling, sandbox simulation
 */
(function () {
'use strict';

const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const AMOUNT    = {{ $amount }};
const STUDENT_ID = {{ $student?->id ?? 0 }};
const INVOICE_ID = {{ $invoice?->id ?? 0 }};
let selectedOperator = null;
let phoneNumber      = null;
let transactionRef   = null;
let pollInterval     = null;
let pollCount        = 0;
const MAX_POLLS      = 40; // 40 × 3s = 2 minutes

// ─── Step navigation ──────────────────────────────────────────────────────────
window.goToStep = function (step) {
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    document.getElementById(`panel-${step}`)?.classList.add('active');

    document.querySelectorAll('.step-item').forEach((item, i) => {
        const circle = document.getElementById(`step-circle-${i + 1}`);
        item.classList.remove('active', 'done');
        circle.classList.remove('active', 'done');
        if (i + 1 < step)  { item.classList.add('done'); circle.classList.add('done'); circle.textContent = '✓'; }
        if (i + 1 === step) { item.classList.add('active'); circle.classList.add('active'); }
    });
};

// ─── Select operator ──────────────────────────────────────────────────────────
window.selectOperator = function (op) {
    selectedOperator = op;
    document.querySelectorAll('.operator-card').forEach(c => c.classList.remove('selected'));
    document.getElementById(`op-${op}`)?.classList.add('selected');
    document.getElementById('btn-step1').disabled = false;
    document.getElementById('summary-operator').textContent = op === 'orange' ? 'Orange Money' : 'MTN MoMo';
    document.getElementById('op-flag').textContent = op === 'orange' ? '🟠' : '🟡';
};

// ─── Validate Cameroon phone number ───────────────────────────────────────────
window.validatePhone = function (inp) {
    const val = inp.value.replace(/\s/g, '');
    const orangePrefixes = ['655','656','657','658','659','699'];
    const mtnPrefixes    = ['670','671','672','673','674','675','676','677','678','679','650','651','652','653','654'];
    const allValid = [...orangePrefixes, ...mtnPrefixes];

    const isValid = val.length === 9 && allValid.some(p => val.startsWith(p));
    const errEl   = document.getElementById('phone-error');

    errEl.style.display = (!isValid && val.length > 0) ? '' : 'none';
    document.getElementById('btn-step2').disabled = !isValid;
    if (isValid) phoneNumber = val;
};

// ─── Initiate payment ─────────────────────────────────────────────────────────
window.initiatePayment = async function () {
    // Update confirm panel values
    document.getElementById('confirm-op').textContent    = selectedOperator === 'orange' ? 'Orange Money' : 'MTN MoMo';
    document.getElementById('confirm-phone').textContent = `+237 ${phoneNumber}`;

    goToStep(4);

    // Step 1: Sending
    await sleep(1500);
    setProcStep(1, 'done'); setProcStep(2, 'active');

    try {
        const res = await fetch('{{ route('payment.initiate') }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' },
            body: JSON.stringify({
                operator:   selectedOperator,
                phone:      phoneNumber,
                amount:     AMOUNT,
                student_id: STUDENT_ID,
                invoice_id: INVOICE_ID,
            }),
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message ?? 'Payment initiation failed');

        transactionRef = data.transaction_ref;

        // Step 2: Waiting USSD
        await sleep(2000);
        setProcStep(2, 'done'); setProcStep(3, 'active');

        // Start polling
        startPolling(transactionRef);

    } catch (err) {
        showError(err.message);
    }
};

// ─── Poll payment status ──────────────────────────────────────────────────────
function startPolling(ref) {
    pollCount    = 0;
    pollInterval = setInterval(async () => {
        pollCount++;
        if (pollCount > MAX_POLLS) {
            clearInterval(pollInterval);
            showError('{{ $isFr ? 'Délai dépassé. Veuillez réessayer ou vérifier votre téléphone.' : 'Timeout. Please retry or check your phone.' }}');
            return;
        }
        try {
            const res  = await fetch(`/payment/status/${ref}`, { headers: { 'Accept':'application/json' } });
            const data = await res.json();
            if (data.status === 'success') {
                clearInterval(pollInterval);
                setProcStep(3, 'done'); setProcStep(4, 'active');
                await sleep(1000);
                setProcStep(4, 'done');
                await sleep(500);
                showSuccess(data);
            } else if (data.status === 'failed') {
                clearInterval(pollInterval);
                showError(data.message ?? 'Payment declined');
            }
            // else: still pending — keep polling
        } catch { /* network error, keep polling */ }
    }, 3000);
}

// ─── UI helpers ───────────────────────────────────────────────────────────────
function setProcStep(n, state) {
    const el  = document.getElementById(`ps-${n}`);
    const dot = el?.querySelector('.proc-dot');
    if (!el) return;
    el.className  = `proc-step ${state}`;
    dot.className = `proc-dot ${state}`;
}

function showSuccess(data) {
    document.getElementById('receipt-txn').textContent  = data.transaction_ref ?? '—';
    document.getElementById('receipt-date').textContent = new Date().toLocaleString();
    if (data.receipt_url) {
        document.getElementById('download-receipt').href = data.receipt_url;
    }
    document.getElementById('result-success').style.display = '';
    document.getElementById('result-error').style.display   = 'none';
    goToStep(5);
}

function showError(msg) {
    document.getElementById('error-message').textContent = msg;
    document.getElementById('result-success').style.display = 'none';
    document.getElementById('result-error').style.display   = '';
    goToStep(5);
}

window.resetPayment = function () { goToStep(1); };

function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

// ─── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});

})();
</script>
@endpush
