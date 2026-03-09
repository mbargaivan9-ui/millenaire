@extends('layouts.app')
@section('title', 'Gestion des Horaires')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Horaires</span>
    </div>
    <h1 class="page-title">Gestion des Horaires</h1>
    <p class="page-subtitle">Gérer les horaires des cours</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.schedule.create') }}" class="btn btn-primary">
      <i data-lucide="plus" style="width:14px;height:14px"></i>
      Nouvel Horaire
    </a>
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
        <label class="form-label">Classe</label>
        <select class="form-control" name="classe_id">
          <option value="">Toutes les classes</option>
          @foreach($classes ?? [] as $classe)
            <option value="{{ $classe->id }}" {{ request('classe_id') == $classe->id ? 'selected' : '' }}>
              {{ $classe->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div>
        <button type="submit" class="btn btn-primary w-100">
          <i data-lucide="search" style="width:13px;height:13px"></i>
          Filtrer
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Schedules Grid --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px">
  @forelse($schedules ?? [] as $schedule)
    <div class="card">
      <div class="card-header">
        @php
          $dayIcon = match($schedule->day_of_week ?? 'monday') {
            'monday' => 'calendar',
            'tuesday' => 'calendar',
            'wednesday' => 'calendar',
            'thursday' => 'calendar',
            'friday' => 'calendar',
            'saturday' => 'calendar',
            'sunday' => 'calendar',
            default => 'calendar'
          };
        @endphp
        <i data-lucide="{{ $dayIcon }}" style="width:16px;height:16px"></i>
        <span>{{ $schedule->day_of_week }}</span>
      </div>
      <div class="card-body">
        <h3 style="margin:0 0 16px 0;font-size:15px;font-weight:600">{{ $schedule->subject?->name ?? '—' }}</h3>
        
        <div style="display:grid;gap:12px;font-size:13px">
          <div style="padding:8px;background:var(--text-bg);border-radius:6px">
            <div style="color:var(--text-muted);font-size:11px;margin-bottom:2px">Classe</div>
            <div style="font-weight:600">{{ $schedule->classe?->name ?? '—' }}</div>
          </div>
          
          <div style="padding:8px;background:var(--text-bg);border-radius:6px">
            <div style="color:var(--text-muted);font-size:11px;margin-bottom:2px">Enseignant</div>
            <div style="font-weight:600">{{ $schedule->teacher?->user?->name ?? '—' }}</div>
          </div>
          
          <div style="padding:8px;background:var(--text-bg);border-radius:6px">
            <div style="color:var(--text-muted);font-size:11px;margin-bottom:2px">Horaire</div>
            <div style="font-weight:600">
              <i data-lucide="clock" style="width:12px;height:12px;margin-right:4px"></i>
              {{ \Carbon\Carbon::createFromFormat('H:i', $schedule->start_time ?? '00:00')->format('H:i') }}
              —
              {{ \Carbon\Carbon::createFromFormat('H:i', $schedule->end_time ?? '00:00')->format('H:i') }}
            </div>
          </div>
          
          @if($schedule->room)
            <div style="padding:8px;background:var(--text-bg);border-radius:6px">
              <div style="color:var(--text-muted);font-size:11px;margin-bottom:2px">Salle</div>
              <div style="font-weight:600">{{ $schedule->room }}</div>
            </div>
          @endif
        </div>
        
        <div style="display:flex;gap:8px;margin-top:16px">
          <a href="{{ route('admin.schedule.edit', $schedule) }}" class="btn btn-sm" style="flex:1;background:var(--primary-bg);color:var(--primary)">
            <i data-lucide="edit-2" style="width:13px;height:13px"></i>
            Éditer
          </a>
          <form method="POST" action="{{ route('admin.schedule.destroy', $schedule) }}" style="display:inline;flex:1"
                onsubmit="return confirm('Êtes-vous sûr ?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm w-100" style="background:var(--danger-bg);color:var(--danger)">
              <i data-lucide="trash-2" style="width:13px;height:13px"></i>
              Supprimer
            </button>
          </form>
        </div>
      </div>
    </div>
  @empty
    <div style="grid-column:1/-1;display:flex;align-items:center;justify-content:center;min-height:400px;color:var(--text-muted)">
      <div style="text-align:center">
        <i data-lucide="inbox" style="width:40px;height:40px;margin-bottom:16px;display:block;color:var(--text-muted)"></i>
        <div style="font-size:13px;margin-bottom:16px">Aucun horaire créé</div>
        <a href="{{ route('admin.schedule.create') }}" class="btn btn-primary">
          Créer un horaire
        </a>
      </div>
    </div>
  @endforelse
</div>

{{-- Pagination --}}
@if($schedules?->hasPages())
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:30px;padding-top:20px;border-top:1px solid var(--border)">
  <small style="color:var(--text-muted);font-size:12px">
    Affichage {{ $schedules->firstItem() }} à {{ $schedules->lastItem() }} sur {{ $schedules->total() }} horaires
  </small>
  <div>
    {!! $schedules->links('pagination::simple-tailwind') !!}
  </div>
</div>
@endif

@endsection


