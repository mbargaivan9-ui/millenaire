@extends('layouts.app')
@section('title', 'Gestion des Frais Scolaires')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Frais</span>
    </div>
    <h1 class="page-title">Gestion des Frais Scolaires</h1>
    <p class="page-subtitle">Gérer les frais scolaires de l'établissement</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.fees.create') }}" class="btn btn-primary">
      <i data-lucide="plus" style="width:14px;height:14px"></i>
      Nouveau Frais
    </a>
    <a href="{{ route('admin.fees.settings') }}" class="btn btn-secondary" style="margin-left:8px">
      <i data-lucide="settings" style="width:14px;height:14px"></i>
      Paramètres
    </a>
  </div>
</div>

{{-- Stats KPI Grid --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:20px">
  <div class="card">
    <div class="card-body">
      <div style="color:var(--text-muted);font-size:12px;margin-bottom:8px">Frais Actifs</div>
      <div style="font-size:24px;font-weight:700">{{ $fees?->where('status', 'active')->count() ?? 0 }}</div>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <div style="color:var(--text-muted);font-size:12px;margin-bottom:8px">Montant Total</div>
      <div style="font-size:24px;font-weight:700">{{ number_format($fees?->where('status', 'active')->sum('amount') ?? 0, 0) }} FCFA</div>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <div style="color:var(--text-muted);font-size:12px;margin-bottom:8px">Frais Obligatoires</div>
      <div style="font-size:24px;font-weight:700">{{ $fees?->where('is_mandatory', true)->count() ?? 0 }}</div>
    </div>
  </div>
</div>

{{-- Success Message --}}
@if($message = session('success'))
<div style="background:var(--success-bg);border:1px solid var(--success);border-radius:8px;padding:12px 16px;
            margin-bottom:20px;font-size:13px;color:var(--success);display:flex;align-items:center;gap:10px">
  <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0"></i>
  <div>{{ $message }}</div>
</div>
@endif

{{-- Filters Card --}}
<div class="card mb-20">
  <div class="card-header">
    <i data-lucide="filter" style="width:16px;height:16px"></i>
    <span>Filtres</span>
  </div>
  <div class="card-body">
    <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;align-items:flex-end">
      <div>
        <label class="form-label">Recherche</label>
        <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
      </div>
      <div>
        <label class="form-label">Statut</label>
        <select class="form-control" name="status">
          <option value="">Tous les statuts</option>
          <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
          <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
        </select>
      </div>
      <div>
        <button type="submit" class="btn btn-primary w-100">
          <i data-lucide="search" style="width:13px;height:13px"></i>
          Chercher
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Fees Table --}}
<div class="card">
  <div class="card-header">
    <i data-lucide="file-text" style="width:16px;height:16px"></i>
    <span>Frais</span>
    <span style="margin-left:auto;font-size:12px;color:var(--text-muted)">
      {{ $fees?->total() ?? 0 }} total
    </span>
  </div>
  <div class="card-body">
    <div style="overflow-x:auto">
      <table class="table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Montant</th>
            <th>Statut</th>
            <th>Obligatoire</th>
            <th>Date d'Échéance</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($fees ?? [] as $fee)
          <tr>
            <td>
              <div style="font-weight:600;font-size:13px">{{ $fee->name ?? '-' }}</div>
            </td>
            <td>
              <div style="font-size:12px">{{ number_format($fee->amount ?? 0, 0) }} FCFA</div>
            </td>
            <td>
              @php
                $statusColor = $fee->status === 'active' ? 'var(--success)' : 'var(--text-muted)';
              @endphp
              <span style="background:{{ $statusColor }}20;color:{{ $statusColor }};padding:4px 8px;border-radius:4px;
                           font-size:11px;font-weight:600">
                {{ ucfirst($fee->status ?? 'inactive') }}
              </span>
            </td>
            <td>
              <span style="background:{{ $fee?->is_mandatory ? 'var(--danger)' : 'var(--border)' }}20;color:{{ $fee?->is_mandatory ? 'var(--danger)' : 'var(--text-muted)' }};
                           padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600">
                {{ $fee?->is_mandatory ? 'Oui' : 'Non' }}
              </span>
            </td>
            <td>
              <div style="font-size:12px">{{ $fee->due_date ? \Carbon\Carbon::parse($fee->due_date)->format('d/m/Y') : '-' }}</div>
            </td>
            <td style="text-align:right">
              <div style="display:flex;gap:6px;justify-content:flex-end">
                <a href="{{ route('admin.fees.edit', $fee) }}" class="btn btn-sm" title="Éditer"
                   style="background:var(--primary-bg);color:var(--primary)">
                  <i data-lucide="edit-2" style="width:13px;height:13px"></i>
                </a>
                <form method="POST" action="{{ route('admin.fees.destroy', $fee) }}" style="display:inline"
                      onsubmit="return confirm('Êtes-vous sûr ?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm" title="Supprimer"
                          style="background:var(--danger-bg);color:var(--danger)">
                    <i data-lucide="trash-2" style="width:13px;height:13px"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" style="text-align:center;padding:40px 20px">
              <i data-lucide="inbox" style="width:40px;height:40px;color:var(--text-muted);margin-bottom:16px;display:block"></i>
              <div style="color:var(--text-muted);font-size:13px">Aucun frais trouvé</div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($fees?->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
      <small style="color:var(--text-muted);font-size:12px">
        Affichage {{ $fees->firstItem() }} à {{ $fees->lastItem() }} sur {{ $fees->total() }} frais
      </small>
      <div>
        {!! $fees->links('pagination::simple-tailwind') !!}
      </div>
    </div>
    @endif
  </div>
</div>

@endsection


