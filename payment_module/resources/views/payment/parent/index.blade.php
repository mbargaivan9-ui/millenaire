@extends('layouts.app')

@section('title', 'Paiement Scolarité — School Pay')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/payment/schoolpay.css') }}">
@endpush

@section('content')

{{-- ═══════════════════════════════════════════════════════
     SCHOOL PAY — Interface Parent
     Design héritant du design system Millénaire Connect
     Primary: #0d9488 | Dark sidebar | Plus Jakarta Sans
═══════════════════════════════════════════════════════ --}}

<div class="sp-wrap" id="schoolpay-app">

  {{-- ─── En-tête de page ──────────────────────────────── --}}
  <div class="sp-page-header">
    <div class="sp-page-header__left">
      <div class="sp-page-header__icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
      </div>
      <div>
        <h1 class="sp-page-header__title">Paiement Scolarité</h1>
        <p class="sp-page-header__sub">Orange Money &amp; MTN Mobile Money — Sécurisé &amp; Instantané</p>
      </div>
    </div>
    <div class="sp-page-header__badges">
      <span class="sp-badge sp-badge--live">
        <span class="sp-badge__dot"></span>
        Sandbox Actif
      </span>
      <span class="sp-badge sp-badge--secure">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        SSL 256-bit
      </span>
    </div>
  </div>

  {{-- ─── Barre d'étapes ──────────────────────────────── --}}
  <div class="sp-steps" id="sp-steps">
    @foreach([
      ['num'=>1,'icon'=>'user','label'=>'Élève','sub'=>'Identification'],
      ['num'=>2,'icon'=>'dollar-sign','label'=>'Montant','sub'=>'Tranche & frais'],
      ['num'=>3,'icon'=>'smartphone','label'=>'Opérateur','sub'=>'Orange / MTN'],
      ['num'=>4,'icon'=>'check-circle','label'=>'Confirmation','sub'=>'Vérification'],
    ] as $i => $s)
      <div class="sp-step" id="sp-step-{{ $s['num'] }}" data-step="{{ $s['num'] }}">
        <div class="sp-step__num">
          <span class="sp-step__num-val">{{ $s['num'] }}</span>
          <svg class="sp-step__check" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="sp-step__info">
          <span class="sp-step__label">{{ $s['label'] }}</span>
          <span class="sp-step__sub">{{ $s['sub'] }}</span>
        </div>
      </div>
      @if($i < 3)<div class="sp-step__div"></div>@endif
    @endforeach
  </div>

  {{-- ─── Corps principal ─────────────────────────────── --}}
  <div class="sp-body">
    <div class="sp-main">

      {{-- ════ ÉTAPE 1 : IDENTIFICATION ════ --}}
      <div class="sp-panel" id="sp-panel-1">
        <div class="sp-panel__header">
          <div class="sp-panel__icon sp-panel__icon--teal">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <div>
            <h2 class="sp-panel__title">Identification de l'élève</h2>
            <p class="sp-panel__sub">Sélectionnez l'élève pour lequel vous souhaitez régler les frais</p>
          </div>
        </div>

        <div class="sp-field-grid">
          {{-- Sélection de l'enfant --}}
          <div class="sp-field sp-field--full">
            <label class="sp-label">Élève</label>
            <div class="sp-select-wrap">
              <select id="sp-student-select" class="sp-select">
                <option value="">— Sélectionnez un élève —</option>
                @foreach($students as $student)
                  <option value="{{ $student->id }}"
                    data-name="{{ $student->user?->name }}"
                    data-classe="{{ $student->classe?->name }}"
                    data-fee="{{ $student->classe?->annual_fee ?? 0 }}"
                    {{ optional($preStudent)->id === $student->id ? 'selected' : '' }}>
                    {{ $student->user?->name }} — {{ $student->classe?->name }}
                  </option>
                @endforeach
              </select>
              <svg class="sp-select-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
          </div>

          {{-- Nom du parent --}}
          <div class="sp-field">
            <label class="sp-label">Nom du parent / tuteur</label>
            <input type="text" id="sp-parent-name" class="sp-input"
              value="{{ auth()->user()->name }}" readonly>
          </div>

          {{-- Motif --}}
          <div class="sp-field">
            <label class="sp-label">Type de frais</label>
            <div class="sp-select-wrap">
              <select id="sp-fee-type" class="sp-select">
                <option value="Frais de scolarité">Frais de scolarité</option>
                <option value="Frais d'examen">Frais d'examen</option>
                <option value="Frais d'inscription">Frais d'inscription</option>
                <option value="Frais d'uniforme">Frais d'uniforme</option>
                <option value="Frais divers">Frais divers</option>
              </select>
              <svg class="sp-select-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
          </div>
        </div>

        {{-- Carte élève sélectionné --}}
        <div id="sp-student-card" class="sp-student-card" style="display:none">
          <div class="sp-student-card__avatar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          </div>
          <div class="sp-student-card__info">
            <div class="sp-student-card__name" id="sc-name">—</div>
            <div class="sp-student-card__classe" id="sc-classe">—</div>
          </div>
          <div class="sp-student-card__fee">
            <div class="sp-student-card__fee-label">Frais annuels</div>
            <div class="sp-student-card__fee-val" id="sc-fee">—</div>
          </div>
          <div class="sp-student-card__status sp-badge sp-badge--ok">
            <span class="sp-badge__dot"></span>Solvable
          </div>
        </div>

        <div class="sp-hint">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
          Les frais sont calculés automatiquement selon la classe de l'élève.
        </div>

        <div class="sp-panel__actions">
          <button class="sp-btn sp-btn--primary" id="sp-next-1" disabled>
            Continuer <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
      </div>

      {{-- ════ ÉTAPE 2 : MONTANT ════ --}}
      <div class="sp-panel" id="sp-panel-2" style="display:none">
        <div class="sp-panel__header">
          <div class="sp-panel__icon sp-panel__icon--teal">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="1" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div>
            <h2 class="sp-panel__title">Sélection du montant</h2>
            <p class="sp-panel__sub">Choisissez la tranche ou entrez un montant personnalisé</p>
          </div>
        </div>

        {{-- Grille de tranches --}}
        <div class="sp-tranche-grid" id="sp-tranche-grid">
          {{-- Généré dynamiquement par JS --}}
        </div>

        <div class="sp-divider">
          <span>ou montant libre</span>
        </div>

        {{-- Montant personnalisé --}}
        <div class="sp-field">
          <label class="sp-label">Montant personnalisé (FCFA)</label>
          <div class="sp-amount-wrap">
            <span class="sp-amount-prefix">FCFA</span>
            <input type="number" id="sp-custom-amount" class="sp-input sp-input--amount"
              placeholder="Ex: 150 000" min="500" max="5000000">
          </div>
        </div>

        <div class="sp-panel__actions sp-panel__actions--split">
          <button class="sp-btn sp-btn--ghost" id="sp-back-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg>
            Retour
          </button>
          <button class="sp-btn sp-btn--primary" id="sp-next-2" disabled>
            Continuer <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
      </div>

      {{-- ════ ÉTAPE 3 : OPÉRATEUR ════ --}}
      <div class="sp-panel" id="sp-panel-3" style="display:none">
        <div class="sp-panel__header">
          <div class="sp-panel__icon sp-panel__icon--teal">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="18" x="5" y="3" rx="1"/><rect width="7" height="9" x="12" y="12" rx="1"/></svg>
          </div>
          <div>
            <h2 class="sp-panel__title">Choisissez votre opérateur</h2>
            <p class="sp-panel__sub">Sélectionnez le service Mobile Money pour effectuer le paiement</p>
          </div>
        </div>

        <div class="sp-provider-grid">
          {{-- Orange Money --}}
          <div class="sp-provider" id="sp-prov-orange" data-provider="orange">
            <div class="sp-provider__logo sp-provider__logo--orange">
              <svg viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg" width="52" height="52">
                <circle cx="40" cy="40" r="38" fill="rgba(255,255,255,0.2)"/>
                <circle cx="40" cy="40" r="26" fill="rgba(255,255,255,0.95)"/>
                <text x="40" y="50" text-anchor="middle" font-family="Arial Black" font-weight="900" font-size="28" fill="#FF6600">O</text>
                <path d="M8 40 Q40 10 72 40" stroke="rgba(255,255,255,0.7)" stroke-width="3" fill="none" stroke-linecap="round"/>
                <path d="M8 40 Q40 70 72 40" stroke="rgba(255,255,255,0.7)" stroke-width="3" fill="none" stroke-linecap="round"/>
              </svg>
            </div>
            <div class="sp-provider__info">
              <div class="sp-provider__name">Orange Money</div>
              <div class="sp-provider__desc">Réseau Orange Cameroun<br>Paiement instantané</div>
              <div class="sp-provider__nums">
                <span class="sp-num-tag">69X XXX XXX</span>
              </div>
            </div>
            <div class="sp-provider__check">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
          </div>

          {{-- MTN MoMo --}}
          <div class="sp-provider" id="sp-prov-mtn" data-provider="mtn">
            <div class="sp-provider__logo sp-provider__logo--mtn">
              <svg viewBox="0 0 90 36" xmlns="http://www.w3.org/2000/svg" width="64" height="26">
                <text x="0" y="30" font-family="Arial Black" font-weight="900" font-size="34" fill="#1A1A1A" letter-spacing="-1">MTN</text>
              </svg>
            </div>
            <div class="sp-provider__info">
              <div class="sp-provider__name">MTN Mobile Money</div>
              <div class="sp-provider__desc">Réseau MTN Cameroun<br>Mobile Money sécurisé</div>
              <div class="sp-provider__nums">
                <span class="sp-num-tag sp-num-tag--mtn">67X / 65X XXX XXX</span>
              </div>
            </div>
            <div class="sp-provider__check">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
          </div>
        </div>

        {{-- Numéro de téléphone --}}
        <div class="sp-field" id="sp-phone-field" style="display:none">
          <label class="sp-label" id="sp-phone-label">Numéro Orange Money</label>
          <div class="sp-phone-wrap">
            <div class="sp-phone-prefix">
              <span class="sp-phone-flag">🇨🇲</span>
              <span class="sp-phone-code">+237</span>
            </div>
            <input type="tel" id="sp-phone" class="sp-input sp-input--phone"
              placeholder="6XXXXXXXX" maxlength="9" autocomplete="tel">
          </div>
          <div class="sp-phone-hint" id="sp-phone-hint">Entrez votre numéro sans le +237</div>
        </div>

        <div class="sp-panel__actions sp-panel__actions--split">
          <button class="sp-btn sp-btn--ghost" id="sp-back-3">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg>
            Retour
          </button>
          <button class="sp-btn sp-btn--primary" id="sp-next-3" disabled>
            Vérifier <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
      </div>

      {{-- ════ ÉTAPE 4 : CONFIRMATION ════ --}}
      <div class="sp-panel" id="sp-panel-4" style="display:none">
        <div class="sp-panel__header">
          <div class="sp-panel__icon sp-panel__icon--teal">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </div>
          <div>
            <h2 class="sp-panel__title">Confirmation du paiement</h2>
            <p class="sp-panel__sub">Vérifiez tous les détails avant de procéder</p>
          </div>
        </div>

        {{-- Récapitulatif --}}
        <div class="sp-confirm-table" id="sp-confirm-table">
          {{-- Rempli dynamiquement par JS --}}
        </div>

        {{-- Avertissement sécurité --}}
        <div class="sp-security-notice">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <div>
            <strong>Paiement sécurisé</strong> — Le montant sera directement crédité sur le compte
            <strong>{{ config('app.name') }}</strong>. Transaction chiffrée SSL 256-bit.
            Vous recevrez une confirmation par SMS.
          </div>
        </div>

        <div class="sp-panel__actions sp-panel__actions--split">
          <button class="sp-btn sp-btn--ghost" id="sp-back-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg>
            Retour
          </button>
          <button class="sp-btn sp-btn--pay" id="sp-pay-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
            <span id="sp-pay-label">Payer maintenant</span>
          </button>
        </div>
      </div>

    </div>{{-- end sp-main --}}

    {{-- ─── SIDEBAR : Résumé ──────────────────────────── --}}
    <aside class="sp-sidebar">

      {{-- Récapitulatif --}}
      <div class="sp-summary-card">
        <h3 class="sp-summary-card__title">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Récapitulatif
        </h3>

        <div class="sp-summary-rows" id="sp-summary-rows">
          <div class="sp-summary-row"><span>Élève</span><span id="ss-student">—</span></div>
          <div class="sp-summary-row"><span>Classe</span><span id="ss-classe">—</span></div>
          <div class="sp-summary-row"><span>Tranche</span><span id="ss-tranche">—</span></div>
          <div class="sp-summary-row"><span>Sous-total</span><span id="ss-amount">—</span></div>
          <div class="sp-summary-row"><span>Frais (1.5%)</span><span id="ss-fees">—</span></div>
        </div>

        <div class="sp-summary-total" id="sp-summary-total">
          <span>Total à payer</span>
          <span id="ss-total" class="sp-summary-total__val">—</span>
        </div>

        <div class="sp-summary-provider" id="sp-summary-provider" style="display:none">
          <div class="sp-summary-provider__dot" id="sp-prov-dot"></div>
          <span id="sp-prov-label">—</span>
          <span id="sp-prov-phone" class="sp-summary-provider__phone">—</span>
        </div>
      </div>

      {{-- Garanties --}}
      <div class="sp-guarantees">
        <h4 class="sp-guarantees__title">🛡️ Garanties & Sécurité</h4>
        <ul class="sp-guarantees__list">
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
            Virement direct compte école
          </li>
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
            Cryptage SSL 256-bit
          </li>
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
            Reçu PDF instantané
          </li>
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
            Notification SMS confirmée
          </li>
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
            Sandbox de test actif
          </li>
        </ul>
      </div>

      {{-- Derniers paiements --}}
      @if($recentPayments->isNotEmpty())
      <div class="sp-recent">
        <h4 class="sp-recent__title">Paiements récents</h4>
        @foreach($recentPayments as $p)
        <div class="sp-recent-item">
          <div class="sp-recent-item__op sp-recent-item__op--{{ $p->operator }}">
            {{ $p->operator === 'orange' ? 'OM' : 'MTN' }}
          </div>
          <div class="sp-recent-item__info">
            <div class="sp-recent-item__name">{{ $p->student?->user?->name ?? 'N/A' }}</div>
            <div class="sp-recent-item__date">{{ $p->created_at->format('d/m/Y') }}</div>
          </div>
          <div class="sp-recent-item__amount sp-recent-item__amount--{{ $p->status_color }}">
            {{ number_format($p->amount, 0, ',', ' ') }} F
          </div>
        </div>
        @endforeach
      </div>
      @endif

    </aside>

  </div>{{-- end sp-body --}}

</div>{{-- end schoolpay-app --}}

{{-- ══════════════ MODAL USSD ══════════════ --}}
<div class="sp-modal-overlay" id="sp-modal" style="display:none">
  <div class="sp-modal">
    <div class="sp-modal__header" id="sp-modal-header">
      <div class="sp-modal__logo" id="sp-modal-logo"></div>
      <div>
        <h3 class="sp-modal__title">Paiement en cours…</h3>
        <p class="sp-modal__sub" id="sp-modal-sub">Validation en attente</p>
      </div>
    </div>

    <div class="sp-modal__progress">
      <div class="sp-progress-bar">
        <div class="sp-progress-fill" id="sp-progress-fill"></div>
      </div>
      <div class="sp-modal__timer">
        Expiration dans <strong id="sp-timer">60</strong>s
      </div>
    </div>

    <div class="sp-modal__ussd">
      <div class="sp-ussd-label">Code USSD de validation</div>
      <div class="sp-ussd-code">
        <code id="sp-ussd-code" class="sp-ussd-code__val">—</code>
        <button class="sp-ussd-copy" id="sp-ussd-copy" title="Copier">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
        </button>
      </div>
    </div>

    <ol class="sp-modal__steps" id="sp-modal-steps">
      <li data-step-idx="0">Composez le code USSD ci-dessus</li>
      <li data-step-idx="1">Entrez votre code PIN</li>
      <li data-step-idx="2">Confirmez la transaction</li>
      <li data-step-idx="3">Attendez la validation</li>
    </ol>

    <div class="sp-sandbox-notice">
      🔒 Mode Sandbox — Simulation sans débit réel.<br>
      En production : clés API opérateurs requises.
    </div>
  </div>
</div>

{{-- ══════════════ MODAL SUCCÈS ══════════════ --}}
<div class="sp-modal-overlay" id="sp-success-modal" style="display:none">
  <div class="sp-modal sp-modal--success">
    <div class="sp-success-icon">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="40" height="40"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <h3 class="sp-modal__title sp-modal__title--success">Paiement Confirmé !</h3>
    <p class="sp-modal__sub" id="sp-success-msg">Votre paiement a été effectué avec succès.</p>

    <div class="sp-success-details" id="sp-success-details"></div>

    <div class="sp-modal__actions">
      <a id="sp-receipt-link" href="#" class="sp-btn sp-btn--primary sp-btn--full" target="_blank">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        Voir le reçu
      </a>
      <button class="sp-btn sp-btn--ghost sp-btn--full" onclick="location.reload()">
        Nouveau paiement
      </button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
const SP_CSRF     = '{{ csrf_token() }}';
const SP_INITIATE = '{{ route("schoolpay.parent.initiate") }}';
const SP_FEES_URL = '{{ url("parent/schoolpay/student") }}';
const SP_POLL_URL = '{{ url("parent/schoolpay/poll") }}';
</script>
<script src="{{ asset('js/payment/schoolpay.js') }}"></script>
@endpush
