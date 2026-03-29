/**
 * SchoolPay — JavaScript
 * Flow: Étape 1 → 2 → 3 → 4 → Modal USSD → Polling → Succès
 */

(function () {
  'use strict';

  /* ─── État global ─────────────────────────────────────────────── */
  const state = {
    step: 1,
    studentId: null, studentName: null, studentClasse: null, annualFee: 0,
    tranche: null, trancheLabel: null, amount: 0, fees: 0, total: 0,
    provider: null, phone: null,
    feeType: 'Frais de scolarité',
    tranches: [],
    transactionRef: null,
    pollingTimer: null,
    countdownTimer: null,
    pollInterval: null,
  };

  /* ─── Helpers ────────────────────────────────────────────────── */
  const fmt = n => new Intl.NumberFormat('fr-CM').format(n) + ' FCFA';
  const $ = id => document.getElementById(id);
  const on = (id, ev, fn) => { const el = $(id); if (el) el.addEventListener(ev, fn); };

  async function post(url, data) {
    const r = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': SP_CSRF },
      body: JSON.stringify(data),
    });
    return r.json();
  }

  async function get(url) {
    const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    return r.json();
  }

  /* ─── Navigation entre étapes ────────────────────────────────── */
  function goTo(n) {
    // Cacher tous les panels
    [1, 2, 3, 4].forEach(i => {
      const p = $(`sp-panel-${i}`);
      if (p) p.style.display = i === n ? 'block' : 'none';
    });

    // Mettre à jour la barre d'étapes
    document.querySelectorAll('.sp-step').forEach(el => {
      const s = parseInt(el.dataset.step);
      el.classList.toggle('active', s === n);
      el.classList.toggle('done', s < n);
    });

    state.step = n;
    updateSummary();
  }

  /* ─── Étape 1 : Identification ───────────────────────────────── */
  const studentSelect = $('sp-student-select');
  if (studentSelect) {
    studentSelect.addEventListener('change', async function () {
      const opt = this.options[this.selectedIndex];
      const id  = this.value;

      if (!id) {
        $('sp-student-card').style.display = 'none';
        $('sp-next-1').disabled = true;
        return;
      }

      state.studentId    = id;
      state.studentName  = opt.dataset.name;
      state.studentClasse= opt.dataset.classe;
      state.annualFee    = parseInt(opt.dataset.fee) || 0;

      // Afficher la carte élève
      $('sc-name').textContent   = state.studentName;
      $('sc-classe').textContent = state.studentClasse;
      $('sc-fee').textContent    = fmt(state.annualFee);
      $('sp-student-card').style.display = 'flex';

      // Charger les tranches via API
      if (state.annualFee > 0) {
        try {
          const data = await get(`${SP_FEES_URL}/${id}/fees`);
          if (data.tranches) {
            state.tranches = data.tranches;
            renderTranches(data.tranches);
          }
        } catch (e) {
          // Fallback local
          state.tranches = computeTranchesLocal(state.annualFee);
          renderTranches(state.tranches);
        }
      }

      $('sp-next-1').disabled = false;
    });

    // Déclencher si pré-sélectionné
    if (studentSelect.value) studentSelect.dispatchEvent(new Event('change'));
  }

  on('sp-fee-type', 'change', function () { state.feeType = this.value; });
  on('sp-next-1', 'click', () => goTo(2));

  /* ─── Étape 2 : Montant ──────────────────────────────────────── */
  function computeTranchesLocal(fee) {
    return [
      { id: 'T1',   label: '1ère Tranche', pct: 0.40, amount: Math.round(fee * 0.40) },
      { id: 'T2',   label: '2ème Tranche', pct: 0.35, amount: Math.round(fee * 0.35) },
      { id: 'T3',   label: '3ème Tranche', pct: 0.25, amount: Math.round(fee * 0.25) },
      { id: 'FULL', label: 'Intégrale',    pct: 1.00, amount: fee },
    ];
  }

  function renderTranches(tranches) {
    const grid = $('sp-tranche-grid');
    if (!grid) return;
    grid.innerHTML = '';
    tranches.forEach(t => {
      const div = document.createElement('div');
      div.className = 'sp-tranche';
      div.dataset.id     = t.id;
      div.dataset.amount = t.amount;
      div.dataset.label  = t.label;
      div.innerHTML = `
        <div class="sp-tranche__label">${t.label}</div>
        <div class="sp-tranche__amount">${fmt(t.amount)}</div>
        <div class="sp-tranche__pct">${Math.round(t.pct * 100)}% des frais annuels</div>
      `;
      div.addEventListener('click', () => selectTranche(div, t));
      grid.appendChild(div);
    });
  }

  function selectTranche(el, t) {
    document.querySelectorAll('.sp-tranche').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    state.tranche      = t.id;
    state.trancheLabel = t.label;
    setAmount(t.amount);
    if ($('sp-custom-amount')) $('sp-custom-amount').value = '';
    $('sp-next-2').disabled = false;
  }

  function setAmount(amt) {
    state.amount = amt;
    state.fees   = Math.round(amt * 0.015);
    state.total  = amt + state.fees;
    updateSummary();
  }

  const customAmt = $('sp-custom-amount');
  if (customAmt) {
    customAmt.addEventListener('input', function () {
      const val = parseInt(this.value) || 0;
      if (val >= 500) {
        document.querySelectorAll('.sp-tranche').forEach(c => c.classList.remove('selected'));
        state.tranche      = null;
        state.trancheLabel = 'Personnalisé';
        setAmount(val);
        $('sp-next-2').disabled = false;
      } else {
        $('sp-next-2').disabled = !state.tranche;
      }
    });
  }

  on('sp-back-2',  'click', () => goTo(1));
  on('sp-next-2',  'click', () => goTo(3));

  /* ─── Étape 3 : Opérateur ────────────────────────────────────── */
  document.querySelectorAll('.sp-provider').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.sp-provider').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      state.provider = card.dataset.provider;

      const label = state.provider === 'orange' ? 'Orange Money (69X)' : 'MTN Mobile Money (67X / 65X)';
      const ph    = $('sp-phone-field');
      const lbl   = $('sp-phone-label');
      const hint  = $('sp-phone-hint');

      if (ph) ph.style.display = 'block';
      if (lbl) lbl.textContent = `Numéro ${state.provider === 'orange' ? 'Orange Money' : 'MTN MoMo'}`;
      if (hint) hint.textContent = state.provider === 'orange' ? 'Numéros Orange : 69X XXX XXX' : 'Numéros MTN : 67X / 65X XXX XXX';

      validatePhone();
      updateSummary();
    });
  });

  const phoneInput = $('sp-phone');
  if (phoneInput) {
    phoneInput.addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '').slice(0, 9);
      state.phone = this.value;
      validatePhone();
      updateSummary();
    });
  }

  function validatePhone() {
    const valid = state.provider && state.phone && state.phone.length === 9 && state.phone.startsWith('6');
    $('sp-next-3').disabled = !valid;
  }

  on('sp-back-3', 'click', () => goTo(2));
  on('sp-next-3', 'click', () => goTo(4));

  /* ─── Étape 4 : Confirmation ────────────────────────────────── */
  function renderConfirmation() {
    const table = $('sp-confirm-table');
    if (!table) return;

    const rows = [
      ['Élève',           state.studentName],
      ['Classe',          state.studentClasse],
      ['Type de frais',   state.feeType],
      ['Tranche',         state.trancheLabel || 'Personnalisé'],
      ['Sous-total',      fmt(state.amount)],
      ['Frais service (1.5%)', fmt(state.fees)],
      ['Opérateur',       state.provider === 'orange' ? '🟠 Orange Money' : '🟡 MTN Mobile Money'],
      ['Numéro débité',   '+237 ' + state.phone],
    ];

    table.innerHTML = rows.map(([k, v]) => `
      <div class="sp-confirm-row">
        <span class="sp-confirm-row__key">${k}</span>
        <span class="sp-confirm-row__val">${v}</span>
      </div>
    `).join('') + `
      <div class="sp-confirm-row sp-confirm-row--total">
        <span class="sp-confirm-row__key">TOTAL À PAYER</span>
        <span class="sp-confirm-row__val">${fmt(state.total)}</span>
      </div>
    `;

    // Mettre à jour le bouton de paiement
    const payBtn = $('sp-pay-btn');
    if (payBtn) {
      payBtn.dataset.provider = state.provider;
      $('sp-pay-label').textContent = `Payer ${fmt(state.total)}`;
    }
  }

  on('sp-back-4', 'click', () => goTo(3));

  const origGoTo4 = goTo;
  document.querySelectorAll('[id^="sp-next-3"]').forEach(btn => {
    btn.addEventListener('click', renderConfirmation);
  });

  // Déclencher le rendu quand on arrive à l'étape 4
  const _goTo = goTo;

  on('sp-pay-btn', 'click', startPayment);

  /* ─── Paiement ────────────────────────────────────────────────── */
  async function startPayment() {
    const btn = $('sp-pay-btn');
    if (btn) btn.disabled = true;

    // Afficher le modal USSD
    showUssdModal();

    try {
      const result = await post(SP_INITIATE, {
        operator:   state.provider,
        phone:      state.phone,
        amount:     state.amount,
        student_id: state.studentId,
        fee_type:   state.feeType,
        tranche:    state.trancheLabel,
      });

      if (!result.success) {
        hideModal('sp-modal');
        showAlert('error', result.message || 'Erreur lors de l\'initiation');
        if (btn) btn.disabled = false;
        return;
      }

      state.transactionRef = result.transaction_ref;

      // Mettre à jour le code USSD
      const ussdCode = state.provider === 'orange'
        ? `#150*4*1*${state.phone}*${state.total}#`
        : `*126*${state.phone}*${state.total}#`;

      const codeEl = $('sp-ussd-code');
      if (codeEl) codeEl.textContent = ussdCode;

      // Démarrer le comptdown & polling
      startCountdown(60);
      startPolling(result.transaction_ref);

    } catch (e) {
      hideModal('sp-modal');
      showAlert('error', 'Erreur réseau. Veuillez réessayer.');
      if (btn) btn.disabled = false;
    }
  }

  /* ─── Modal USSD ─────────────────────────────────────────────── */
  function showUssdModal() {
    const modal  = $('sp-modal');
    const header = $('sp-modal-header');
    const sub    = $('sp-modal-sub');
    const logo   = $('sp-modal-logo');
    const fill   = $('sp-progress-fill');

    if (modal)  modal.style.display = 'flex';
    if (header) header.dataset.provider = state.provider;

    // Logo opérateur dans le modal
    if (logo) {
      logo.className = `sp-modal__logo sp-modal__logo--${state.provider}`;
      if (state.provider === 'orange') {
        logo.innerHTML = `<svg viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg" width="32" height="32">
          <circle cx="40" cy="40" r="36" fill="rgba(255,255,255,.15)"/>
          <circle cx="40" cy="40" r="24" fill="rgba(255,255,255,.9)"/>
          <text x="40" y="48" text-anchor="middle" font-family="Arial Black" font-weight="900" font-size="22" fill="#FF6600">O</text>
        </svg>`;
      } else {
        logo.innerHTML = `<svg viewBox="0 0 70 28" xmlns="http://www.w3.org/2000/svg" width="44" height="18">
          <text x="0" y="22" font-family="Arial Black" font-weight="900" font-size="24" fill="#1A1A1A">MTN</text>
        </svg>`;
      }
    }

    if (sub)  sub.textContent = state.provider === 'orange' ? 'Orange Money — Simulation Sandbox' : 'MTN Mobile Money — Simulation Sandbox';
    if (fill) fill.className = `sp-progress-fill sp-progress-fill--${state.provider}`;

    // Mettre à jour les instructions USSD selon l'opérateur
    const steps = $('sp-modal-steps');
    if (steps) {
      const instructions = state.provider === 'orange' ? [
        'Composez le code USSD ci-dessus',
        'Entrez votre code PIN Orange Money',
        'Confirmez la transaction',
        'Attendez la validation automatique',
      ] : [
        'Composez le code USSD ci-dessus',
        'Tapez 1 pour Payer une facture',
        'Entrez votre code PIN MoMo',
        'Attendez la confirmation SMS',
      ];
      steps.innerHTML = instructions.map(s => `<li>${s}</li>`).join('');
    }
  }

  function hideModal(id) {
    const m = $(id);
    if (m) m.style.display = 'none';
  }

  /* ─── Countdown ─────────────────────────────────────────────── */
  function startCountdown(seconds) {
    let remaining = seconds;
    const timerEl = $('sp-timer');
    const fillEl  = $('sp-progress-fill');

    clearInterval(state.countdownTimer);
    state.countdownTimer = setInterval(() => {
      remaining--;
      if (timerEl) timerEl.textContent = remaining;
      if (fillEl)  fillEl.style.width = ((seconds - remaining) / seconds * 100) + '%';

      // Animer les étapes USSD
      const steps = document.querySelectorAll('.sp-modal__steps li');
      const elapsed = seconds - remaining;
      steps.forEach((li, i) => {
        if (elapsed > (i + 1) * (seconds / (steps.length + 1))) li.classList.add('done');
      });

      if (remaining <= 0) {
        clearInterval(state.countdownTimer);
        clearInterval(state.pollInterval);
        hideModal('sp-modal');
        showAlert('warning', 'Délai expiré. Veuillez réessayer.');
        $('sp-pay-btn').disabled = false;
      }
    }, 1000);
  }

  /* ─── Polling ────────────────────────────────────────────────── */
  function startPolling(ref) {
    clearInterval(state.pollInterval);
    state.pollInterval = setInterval(async () => {
      try {
        const r = await get(`${SP_POLL_URL}/${ref}`);

        if (r.status === 'success') {
          clearInterval(state.pollInterval);
          clearInterval(state.countdownTimer);
          hideModal('sp-modal');
          showSuccessModal(r.receipt_url);
        } else if (r.status === 'failed') {
          clearInterval(state.pollInterval);
          clearInterval(state.countdownTimer);
          hideModal('sp-modal');
          showAlert('error', r.message || 'Paiement refusé par l\'opérateur.');
          $('sp-pay-btn').disabled = false;
        }
        // 'pending' → on continue de poller
      } catch (e) {
        // Erreur réseau → continuer
      }
    }, 2000);
  }

  /* ─── Modal Succès ───────────────────────────────────────────── */
  function showSuccessModal(receiptUrl) {
    const modal = $('sp-success-modal');
    const details = $('sp-success-details');
    const link    = $('sp-receipt-link');

    if (details) {
      details.innerHTML = [
        ['Référence',     state.transactionRef],
        ['Élève',         state.studentName],
        ['Montant payé',  fmt(state.total)],
        ['Opérateur',     state.provider === 'orange' ? 'Orange Money' : 'MTN MoMo'],
        ['Numéro',        '+237 ' + state.phone],
        ['Statut',        '✅ Validé'],
      ].map(([k, v]) => `
        <div class="sp-success-detail-row">
          <span class="k">${k}</span>
          <span class="v">${v}</span>
        </div>
      `).join('');
    }

    if (link && receiptUrl) link.href = receiptUrl;
    if (modal) modal.style.display = 'flex';
  }

  /* ─── Copier code USSD ───────────────────────────────────────── */
  on('sp-ussd-copy', 'click', function () {
    const code = $('sp-ussd-code')?.textContent;
    if (code && navigator.clipboard) {
      navigator.clipboard.writeText(code);
      this.textContent = '✓ Copié';
      setTimeout(() => this.textContent = 'Copier', 2000);
    }
  });

  /* ─── Résumé sidebar ─────────────────────────────────────────── */
  function updateSummary() {
    setText('ss-student', state.studentName || '—');
    setText('ss-classe',  state.studentClasse || '—');
    setText('ss-tranche', state.trancheLabel || '—');
    setText('ss-amount',  state.amount ? fmt(state.amount) : '—');
    setText('ss-fees',    state.fees ? fmt(state.fees) : '—');
    setText('ss-total',   state.total ? fmt(state.total) : '—');

    const totalEl = $('sp-summary-total');
    if (totalEl) {
      totalEl.style.background = state.provider === 'orange' ? 'rgba(255,102,0,0.06)' :
                                  state.provider === 'mtn'    ? 'rgba(255,204,0,0.06)' : '';
    }
    const totalVal = $('ss-total');
    if (totalVal) {
      totalVal.style.color = state.provider === 'orange' ? 'var(--om-color)' :
                              state.provider === 'mtn'    ? '#A67C00' : 'var(--sp-primary)';
    }

    const provEl   = $('sp-summary-provider');
    const provDot  = $('sp-prov-dot');
    const provLbl  = $('sp-prov-label');
    const provPh   = $('sp-prov-phone');

    if (state.provider) {
      if (provEl)  provEl.style.display = 'flex';
      if (provDot) provDot.style.background = state.provider === 'orange' ? 'var(--om-color)' : 'var(--mtn-color)';
      if (provLbl) provLbl.textContent = state.provider === 'orange' ? 'Orange Money' : 'MTN MoMo';
      if (provPh)  provPh.textContent  = state.phone ? '+237 ' + state.phone : '—';
    }
  }

  function setText(id, val) { const el = $(id); if (el) el.textContent = val; }

  /* ─── Alertes ────────────────────────────────────────────────── */
  function showAlert(type, msg) {
    const colors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
    const icons  = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

    const toast = document.createElement('div');
    toast.style.cssText = `
      position:fixed; bottom:24px; right:24px; z-index:2000;
      background:var(--sp-surface); border:1px solid ${colors[type]};
      border-left:4px solid ${colors[type]};
      border-radius:10px; padding:14px 18px;
      box-shadow:0 10px 30px rgba(0,0,0,.15);
      font-size:14px; font-weight:600; color:var(--sp-text);
      max-width:360px; display:flex; align-items:center; gap:10px;
      animation: slideToast .3s ease;
    `;
    toast.innerHTML = `<span>${icons[type]}</span><span>${msg}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
  }

  /* ─── Init ────────────────────────────────────────────────────── */
  goTo(1);

})();
