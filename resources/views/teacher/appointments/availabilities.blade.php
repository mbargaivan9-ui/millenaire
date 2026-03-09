{{--
    | teacher/appointments/availabilities.blade.php
    | Gestion des créneaux de disponibilité pour les RDV parents
    --}}

@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Mes disponibilités' : 'My Availabilities');
@endphp

@section('title', $pageTitle)

@push('styles')
<style>
.day-col { flex: 1; min-width: 130px; }
.day-header {
    background: var(--primary); color: #fff;
    text-align: center; padding: .5rem;
    border-radius: 8px 8px 0 0;
    font-size: .8rem; font-weight: 700;
}
.slot-pill {
    display: flex; align-items: center; justify-content: space-between;
    background: #ecfdf5; border: 1px solid #a7f3d0;
    border-radius: 7px; padding: .3rem .6rem;
    margin-bottom: .35rem; font-size: .78rem;
}
.add-slot-btn {
    width: 100%; padding: .35rem; border: 1.5px dashed var(--border);
    border-radius: 7px; background: transparent; cursor: pointer;
    color: var(--text-muted); font-size: .75rem;
    transition: all .15s ease; margin-top: .35rem;
}
.add-slot-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-bg); }
</style>
@endpush

@section('content')
@php
    $isFr = app()->getLocale() === 'fr';
    $days = $isFr
        ? ['1' => 'Lundi', '2' => 'Mardi', '3' => 'Mercredi', '4' => 'Jeudi', '5' => 'Vendredi', '6' => 'Samedi']
        : ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday'];
    $byDay = $availabilities->groupBy('day_of_week');
@endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('teacher.appointments') }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px"></i>
        </a>
        <div class="page-icon" style="background:linear-gradient(135deg,#0d9488,#14b8a6)">
            <i data-lucide="calendar-clock"></i>
        </div>
        <div>
            <h1 class="page-title">{{ $isFr ? 'Mes disponibilités RDV' : 'My appointment availability' }}</h1>
            <p class="page-subtitle text-muted">{{ $isFr ? 'Définissez vos créneaux disponibles pour les parents.' : 'Set the time slots parents can book with you.' }}</p>
        </div>
    </div>
</div>

<div class="alert alert-info mb-4">
    <i data-lucide="info" style="width:16px" class="me-2"></i>
    {{ $isFr ? 'Les créneaux que vous définissez ici seront proposés aux parents lors de la prise de rendez-vous.' : 'The slots you set here will be offered to parents when booking appointments.' }}
</div>

{{-- Weekly grid --}}
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0"><i data-lucide="calendar" style="width:16px" class="me-2"></i>{{ $isFr ? 'Planning hebdomadaire' : 'Weekly schedule' }}</h6>
    </div>
    <div class="card-body">
        <div class="d-flex gap-3 flex-wrap">
            @foreach($days as $dayNum => $dayLabel)
            <div class="day-col">
                <div class="day-header">{{ $dayLabel }}</div>
                <div style="border: 1px solid var(--border); border-top: none; border-radius: 0 0 8px 8px; padding: .6rem; min-height: 80px;">
                    {{-- Existing slots --}}
                    @foreach($byDay->get($dayNum, collect()) as $slot)
                    <div class="slot-pill" id="slot-{{ $slot->id }}">
                        <span>{{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }}</span>
                        <button type="button" class="btn btn-xs" style="background:none;border:none;color:#ef4444;padding:0 0 0 .25rem;cursor:pointer"
                                onclick="deleteSlot({{ $slot->id }})">×</button>
                    </div>
                    @endforeach

                    {{-- Add slot button --}}
                    <button type="button" class="add-slot-btn" onclick="showAddSlot({{ $dayNum }}, this)">
                        + {{ $isFr ? 'Ajouter' : 'Add slot' }}
                    </button>

                    {{-- Inline add form (hidden) --}}
                    <div id="add-form-{{ $dayNum }}" style="display:none;margin-top:.4rem">
                        <div class="d-flex gap-1 align-items-center flex-wrap" style="font-size:.75rem">
                            <input type="time" id="start-{{ $dayNum }}" class="form-control form-control-sm" value="08:00" style="width:80px">
                            <span style="color:var(--text-muted)">→</span>
                            <input type="time" id="end-{{ $dayNum }}" class="form-control form-control-sm" value="08:30" style="width:80px">
                        </div>
                        <div class="d-flex gap-1 mt-1">
                            <button type="button" class="btn btn-xs btn-primary" onclick="saveSlot({{ $dayNum }})">✓</button>
                            <button type="button" class="btn btn-xs btn-light" onclick="cancelAdd({{ $dayNum }})">✕</button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">{{ $isFr ? 'Créneaux actuels' : 'Current slots' }}</h6>
        @if($availabilities->isEmpty())
        <p class="text-muted" style="font-size:.85rem">{{ $isFr ? 'Aucun créneau défini. Cliquez sur "+ Ajouter" dans le planning.' : 'No slots defined. Click "+ Add" in the schedule above.' }}</p>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ $isFr ? 'Jour' : 'Day' }}</th>
                        <th>{{ $isFr ? 'Début' : 'Start' }}</th>
                        <th>{{ $isFr ? 'Fin' : 'End' }}</th>
                        <th>{{ $isFr ? 'Statut' : 'Status' }}</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($availabilities->sortBy(['day_of_week', 'start_time']) as $slot)
                    <tr id="row-{{ $slot->id }}">
                        <td class="fw-semibold">{{ $days[$slot->day_of_week] ?? "Jour {$slot->day_of_week}" }}</td>
                        <td>{{ substr($slot->start_time, 0, 5) }}</td>
                        <td>{{ substr($slot->end_time, 0, 5) }}</td>
                        <td>
                            @if($slot->is_active)
                            <span class="badge bg-success">{{ $isFr ? 'Actif' : 'Active' }}</span>
                            @else
                            <span class="badge bg-secondary">{{ $isFr ? 'Inactif' : 'Inactive' }}</span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            <button class="btn btn-xs btn-danger" onclick="deleteSlot({{ $slot->id }})">
                                <i data-lucide="trash-2" style="width:12px"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

window.showAddSlot = function(day, btn) {
    document.getElementById(`add-form-${day}`).style.display = '';
    btn.style.display = 'none';
};

window.cancelAdd = function(day) {
    document.getElementById(`add-form-${day}`).style.display = 'none';
    document.querySelector(`[onclick="showAddSlot(${day}, this)"]`).style.display = '';
};

window.saveSlot = async function(day) {
    const start = document.getElementById(`start-${day}`).value;
    const end   = document.getElementById(`end-${day}`).value;
    if (!start || !end || start >= end) {
        alert('{{ $isFr ? "Horaires invalides." : "Invalid times." }}');
        return;
    }

    const res  = await fetch('{{ route("teacher.availabilities.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ day_of_week: day, start_time: start, end_time: end }),
    });
    const data = await res.json();
    if (data.success) {
        // Add pill to grid
        const pill = document.createElement('div');
        pill.className = 'slot-pill';
        pill.id = `slot-${data.availability.id}`;
        pill.innerHTML = `<span>${start.slice(0,5)}–${end.slice(0,5)}</span><button type="button" class="btn btn-xs" style="background:none;border:none;color:#ef4444;padding:0 0 0 .25rem;cursor:pointer" onclick="deleteSlot(${data.availability.id})">×</button>`;

        const container = document.querySelector(`[onclick="showAddSlot(${day}, this)"]`).parentElement;
        container.insertBefore(pill, document.getElementById(`add-form-${day}`));
        cancelAdd(day);
        window.location.reload(); // refresh table
    } else {
        alert('{{ $isFr ? "Erreur lors de l\'ajout." : "Error adding slot." }}');
    }
};

window.deleteSlot = async function(id) {
    if (!confirm('{{ $isFr ? "Supprimer ce créneau ?" : "Delete this slot?" }}')) return;
    const res = await fetch(`/teacher/availabilities/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    });
    const data = await res.json();
    if (data.success) {
        document.getElementById(`slot-${id}`)?.remove();
        document.getElementById(`row-${id}`)?.remove();
    }
};
</script>
@endpush
