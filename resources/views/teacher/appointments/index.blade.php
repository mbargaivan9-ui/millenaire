{{--
    | teacher/appointments/index.blade.php — Rendez-Vous Enseignant
    | Confirmer / Refuser les RDV demandés par les parents
    --}}

@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Rendez-Vous' : 'Appointments');
@endphp

@section('title', $pageTitle)

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#ec4899,#db2777)"><i data-lucide="calendar-clock"></i></div>
        <div>
            <h1 class="page-title">{{ $isFr ? 'Rendez-Vous Parents' : 'Parent Appointments' }}</h1>
            <p class="page-subtitle text-muted">{{ $appointments->total() }} {{ $isFr ? 'rendez-vous' : 'appointments' }}</p>
        </div>
    </div>
</div>

{{-- Upcoming --}}
@if($upcoming->isNotEmpty())
<div class="card mb-4" style="border-color:#f59e0b">
    <div class="card-header" style="background:#fffbeb">
        <h6 class="card-title mb-0" style="color:#92400e">⏰ {{ $isFr ? 'Prochains rendez-vous' : 'Upcoming appointments' }}</h6>
    </div>
    <div class="card-body">
        <div class="row gy-2">
            @foreach($upcoming as $appt)
            <div class="col-md-4">
                <div style="border:1px solid var(--border);border-radius:10px;padding:.75rem;display:flex;gap:.75rem;align-items:center">
                    <div style="width:38px;height:38px;border-radius:50%;background:var(--primary-bg);color:var(--primary);font-weight:700;font-size:.85rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        {{ strtoupper(substr($appt->student?->user?->name ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:.83rem">{{ $appt->student?->user?->name }}</div>
                        <div style="font-size:.73rem;color:var(--text-muted)">{{ $appt->scheduled_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <span class="badge ms-auto {{ $appt->status === 'confirmed' ? 'bg-success' : 'bg-warning' }}" style="font-size:.65rem">
                        {{ $appt->status === 'confirmed' ? ($isFr ? 'Confirmé' : 'Confirmed') : ($isFr ? 'En attente' : 'Pending') }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- All appointments --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ $isFr ? 'Parent / Élève' : 'Parent / Student' }}</th>
                        <th>{{ $isFr ? 'Classe' : 'Class' }}</th>
                        <th>{{ $isFr ? 'Date & Heure' : 'Date & Time' }}</th>
                        <th>{{ $isFr ? 'Motif' : 'Reason' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Statut' : 'Status' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Actions' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appt)
                    <tr>
                        <td>
                            <div class="fw-semibold" style="font-size:.85rem">{{ $appt->student?->user?->name }}</div>
                            <div style="font-size:.73rem;color:var(--text-muted)">{{ $appt->student?->guardians?->first()?->user?->name ?? ($isFr ? 'Tuteur inconnu' : 'Unknown guardian') }}</div>
                        </td>
                        <td style="font-size:.83rem">{{ $appt->student?->classe?->name ?? '—' }}</td>
                        <td style="font-size:.82rem">{{ $appt->scheduled_at?->format('d/m/Y') }}<br><span style="color:var(--text-muted)">{{ $appt->scheduled_at?->format('H:i') }}</span></td>
                        <td style="font-size:.8rem;color:var(--text-muted)">{{ Str::limit($appt->notes ?? '—', 50) }}</td>
                        <td style="text-align:center">
                            @php
                                $statusMap = ['pending' => ['bg-warning', $isFr ? 'En attente' : 'Pending'], 'confirmed' => ['bg-success', $isFr ? 'Confirmé' : 'Confirmed'], 'cancelled' => ['bg-danger', $isFr ? 'Annulé' : 'Cancelled']];
                                [$cls, $lbl] = $statusMap[$appt->status] ?? ['bg-secondary', $appt->status];
                            @endphp
                            <span class="badge {{ $cls }}">{{ $lbl }}</span>
                        </td>
                        <td style="text-align:center">
                            @if($appt->status === 'pending')
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-xs btn-primary" onclick="respondAppt({{ $appt->id }}, 'confirmed')" title="{{ $isFr ? 'Confirmer' : 'Confirm' }}">
                                    <i data-lucide="check" style="width:12px"></i>
                                </button>
                                <button class="btn btn-xs btn-danger" onclick="respondAppt({{ $appt->id }}, 'cancelled')" title="{{ $isFr ? 'Refuser' : 'Reject' }}">
                                    <i data-lucide="x" style="width:12px"></i>
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">
                        <i data-lucide="calendar-off" style="width:32px;opacity:.3;display:block;margin:0 auto .75rem"></i>
                        {{ $isFr ? 'Aucun rendez-vous.' : 'No appointments.' }}
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $appointments->links() }}</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

async function respondAppt(id, status) {
    const note = status === 'cancelled'
        ? prompt('{{ $isFr ? 'Raison du refus (optionnel):' : 'Reason for rejection (optional):' }}')
        : null;

    const res = await fetch(`/teacher/appointments/${id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ status, note }),
    });

    if ((await res.json()).success) {
        location.reload();
    }
}
</script>
@endpush
