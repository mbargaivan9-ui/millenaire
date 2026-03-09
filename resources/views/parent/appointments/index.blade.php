{{--
    | parent/appointments/index.blade.php — Mes Rendez-vous
    --}}

@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Mes Rendez-vous' : 'My Appointments')

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#0d9488,#14b8a6)">
                <i data-lucide="calendar-check"></i>
            </div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Mes Rendez-vous' : 'My Appointments' }}</h1>
                <p class="page-subtitle text-muted">{{ $isFr ? 'Historique et RDV à venir' : 'History and upcoming meetings' }}</p>
            </div>
        </div>
        <a href="{{ route('parent.appointments.create') }}" class="btn btn-primary btn-sm">
            <i data-lucide="calendar-plus" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Prendre rendez-vous' : 'Book appointment' }}
        </a>
    </div>
</div>

{{-- Upcoming appointments --}}
@php $upcoming = $appointments->where('scheduled_at', '>', now())->whereIn('status', ['pending','confirmed'])->sortBy('scheduled_at'); @endphp
@if($upcoming->isNotEmpty())
<div class="card mb-4" style="border-color: var(--primary)">
    <div class="card-header" style="background: var(--primary-bg)">
        <h6 class="card-title mb-0" style="color: var(--primary)">
            <i data-lucide="clock" style="width:16px" class="me-2"></i>
            {{ $isFr ? 'Rendez-vous à venir' : 'Upcoming appointments' }}
        </h6>
    </div>
    <div class="card-body p-0">
        @foreach($upcoming as $appt)
        <div class="d-flex align-items-center gap-3 p-3 border-bottom">
            <div style="width:48px;height:48px;border-radius:12px;background:var(--primary-bg);display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0">
                <span style="font-size:1rem;font-weight:900;color:var(--primary);line-height:1">{{ $appt->scheduled_at?->format('d') }}</span>
                <span style="font-size:.62rem;color:var(--text-muted);font-weight:700;text-transform:uppercase">{{ $appt->scheduled_at?->locale($isFr?'fr':'en')->isoFormat('MMM') }}</span>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold" style="font-size:.88rem">{{ $appt->teacher?->user?->name }}</div>
                <div style="font-size:.77rem;color:var(--text-muted)">
                    {{ $appt->scheduled_at?->format('H:i') }} · {{ $appt->student?->user?->name }}
                </div>
                @if($appt->notes)
                <div style="font-size:.74rem;color:var(--text-muted);margin-top:.2rem;font-style:italic">"{{ Str::limit($appt->notes, 60) }}"</div>
                @endif
            </div>
            <div>
                @if($appt->status === 'confirmed')
                <span class="badge bg-success">✓ {{ $isFr ? 'Confirmé' : 'Confirmed' }}</span>
                @else
                <span class="badge bg-warning">{{ $isFr ? 'En attente' : 'Pending' }}</span>
                @endif
            </div>
            <form method="POST" action="{{ route('parent.appointments.destroy', $appt->id) }}"
                  onsubmit="return confirm('{{ $isFr ? 'Annuler ce rendez-vous ?' : 'Cancel this appointment?' }}')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-xs btn-light" style="color:#ef4444">
                    {{ $isFr ? 'Annuler' : 'Cancel' }}
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- All appointments --}}
<div class="card">
    <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="list" style="width:16px" class="me-2"></i>{{ $isFr ? 'Tous les rendez-vous' : 'All appointments' }}</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ $isFr ? 'Enseignant' : 'Teacher' }}</th>
                        <th>{{ $isFr ? 'Élève' : 'Student' }}</th>
                        <th>{{ $isFr ? 'Date & Heure' : 'Date & Time' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Statut' : 'Status' }}</th>
                        <th>{{ $isFr ? 'Note enseignant' : "Teacher's note" }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appt)
                    <tr>
                        <td class="fw-semibold" style="font-size:.85rem">{{ $appt->teacher?->user?->name }}</td>
                        <td style="font-size:.83rem">{{ $appt->student?->user?->name }}</td>
                        <td style="font-size:.82rem">
                            {{ $appt->scheduled_at?->locale($isFr?'fr':'en')->isoFormat('ddd D MMM, H:mm') }}
                        </td>
                        <td style="text-align:center">
                            @php
                                $badgeMap = [
                                    'pending'   => ['bg-warning',  $isFr ? 'En attente' : 'Pending'],
                                    'confirmed' => ['bg-success',  $isFr ? 'Confirmé'   : 'Confirmed'],
                                    'cancelled' => ['bg-danger',   $isFr ? 'Annulé'     : 'Cancelled'],
                                    'completed' => ['bg-secondary',$isFr ? 'Terminé'    : 'Completed'],
                                ];
                                [$bg, $label] = $badgeMap[$appt->status] ?? ['bg-secondary', $appt->status];
                            @endphp
                            <span class="badge {{ $bg }}">{{ $label }}</span>
                        </td>
                        <td style="font-size:.78rem;color:var(--text-muted);font-style:italic">
                            {{ $appt->teacher_note ? Str::limit($appt->teacher_note, 50) : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i data-lucide="calendar" style="width:32px;opacity:.3;display:block;margin:0 auto .75rem"></i>
                            {{ $isFr ? 'Aucun rendez-vous.' : 'No appointments yet.' }}<br>
                            <a href="{{ route('parent.appointments.create') }}" class="btn btn-primary btn-sm mt-2">
                                {{ $isFr ? 'Prendre rendez-vous' : 'Book one now' }}
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
