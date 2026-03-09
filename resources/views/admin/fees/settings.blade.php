@extends('layouts.app')
@section('title', 'Paramètres des Frais Scolaires')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <span><a href="{{ route('admin.fees.index') }}">Frais</a></span>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Paramètres</span>
    </div>
    <h1 class="page-title">Paramètres des Frais Scolaires</h1>
    <p class="page-subtitle">Configurer les paramètres de gestion des frais</p>
  </div>
</div>

{{-- Settings Form --}}
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Configuration des Paramètres</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.fees.updateSettings') }}" method="POST">
      @csrf

      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px">
        {{-- Délai de Paiement --}}
        <div>
          <label for="payment_deadline" class="form-label">Délai de Paiement (jours)</label>
          <input
            type="number"
            id="payment_deadline"
            name="payment_deadline"
            class="form-control @error('payment_deadline') is-invalid @enderror"
            value="{{ old('payment_deadline', $settings->payment_deadline ?? 30) }}"
            min="1"
            required
          />
          @error('payment_deadline')
            <div class="form-error">{{ $message }}</div>
          @enderror
          <small class="form-text">Nombre de jours accordés pour le paiement des frais</small>
        </div>

        {{-- Pourcentage de Pénalité --}}
        <div>
          <label for="late_payment_percentage" class="form-label">Pénalité de Retard (%)</label>
          <input
            type="number"
            id="late_payment_percentage"
            name="late_payment_percentage"
            class="form-control @error('late_payment_percentage') is-invalid @enderror"
            value="{{ old('late_payment_percentage', $settings->late_payment_percentage ?? 5) }}"
            min="0"
            max="100"
            step="0.01"
            required
          />
          @error('late_payment_percentage')
            <div class="form-error">{{ $message }}</div>
          @enderror
          <small class="form-text">Pourcentage appliqué en cas de paiement tardif</small>
        </div>
      </div>

      {{-- Options de Paiement --}}
      <div style="margin-top:30px;padding-top:20px;border-top:1px solid var(--border-color)">
        <h4 style="margin-bottom:15px">Options de Paiement</h4>

        <div style="display:flex;align-items:center;margin-bottom:20px">
          <input
            type="checkbox"
            id="allow_installments"
            name="allow_installments"
            class="form-checkbox"
            value="1"
            {{ old('allow_installments', $settings->allow_installments ?? false) ? 'checked' : '' }}
          />
          <label for="allow_installments" style="margin-left:8px;margin-bottom:0;cursor:pointer">
            <strong>Autoriser les paiements par tranches</strong>
          </label>
        </div>

        {{-- Max Installments (shown if enabled) --}}
        <div id="installments-section" style="{{ old('allow_installments', $settings->allow_installments ?? false) ? '' : 'display:none' }}">
          <div style="max-width:300px">
            <label for="max_installments" class="form-label">Nombre Maximum de Tranches</label>
            <input
              type="number"
              id="max_installments"
              name="max_installments"
              class="form-control @error('max_installments') is-invalid @enderror"
              value="{{ old('max_installments', $settings->max_installments ?? 3) }}"
              min="2"
            />
            @error('max_installments')
              <div class="form-error">{{ $message }}</div>
            @enderror
            <small class="form-text">Nombre de tranches autorisées pour le paiement</small>
          </div>
        </div>
      </div>

      {{-- Actions --}}
      <div style="margin-top:30px;display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">
          <i data-lucide="save" style="width:14px;height:14px"></i>
          Enregistrer
        </button>
        <a href="{{ route('admin.fees.index') }}" class="btn btn-secondary">
          <i data-lucide="x" style="width:14px;height:14px"></i>
          Annuler
        </a>
      </div>
    </form>
  </div>
</div>

<script>
  const allowInstallments = document.getElementById('allow_installments');
  const installmentsSection = document.getElementById('installments-section');

  allowInstallments.addEventListener('change', function() {
    if (this.checked) {
      installmentsSection.style.display = 'block';
    } else {
      installmentsSection.style.display = 'none';
    }
  });
</script>

@endsection


