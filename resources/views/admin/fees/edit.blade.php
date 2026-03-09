@extends('layouts.app')
@section('title', 'Éditer Frais')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.fees.index') }}" style="color:var(--primary);text-decoration:none">Frais</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Éditer</span>
    </div>
    <h1 class="page-title">Éditer Frais</h1>
    <p class="page-subtitle">Modifier le frais</p>
  </div>
</div>

<div style="max-width:700px">
  <div class="card">
    <div class="card-header">
      <i data-lucide="edit" style="width:16px;height:16px"></i>
      <span>Éditer Frais</span>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.fees.update', $fee) }}">
        @csrf @method('PUT')

        <div style="margin-bottom:20px">
          <label class="form-label">Nom du Frais *</label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                 value="{{ old('name', $fee->name) }}" required>
          @error('name')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Montant (FCFA) *</label>
          <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                 min="0" step="100" value="{{ old('amount', $fee->amount) }}" required>
          @error('amount')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3">{{ old('description', $fee->description) }}</textarea>
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Date d'Échéance</label>
          <input type="date" name="due_date" class="form-control"
                 value="{{ old('due_date', $fee->due_date?->format('Y-m-d')) }}">
        </div>

        <div style="margin-bottom:20px">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
            <input type="checkbox" name="is_mandatory" value="1" 
                   {{ old('is_mandatory', $fee->is_mandatory) ? 'checked' : '' }} style="width:18px;height:18px">
            <span>Frais Obligatoire</span>
          </label>
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Statut *</label>
          <select name="status" class="form-control @error('status') is-invalid @enderror" required>
            <option value="active" {{ old('status', $fee->status) === 'active' ? 'selected' : '' }}>Actif</option>
            <option value="inactive" {{ old('status', $fee->status) === 'inactive' ? 'selected' : '' }}>Inactif</option>
          </select>
          @error('status')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="display:flex;gap:12px">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="save" style="width:14px;height:14px"></i>
            Mettre à jour
          </button>
          <a href="{{ route('admin.fees.index') }}" class="btn btn-secondary">
            Annuler
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection


