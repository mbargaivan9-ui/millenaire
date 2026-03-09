{{--
    | teacher/schedule/index.blade.php — Emploi du Temps Enseignant
    --}}

@extends('layouts.app')
@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Mon Emploi du Temps' : 'My Schedule');
@endphp
@section('title', $pageTitle)

@push('styles')
<style>
.timetable { width:100%; border-collapse:collapse; table-layout:fixed; }
.timetable th { background:var(--surface-2); font-size:.75rem; font-weight:700; text-align:center; padding:.65rem .5rem; border:1px solid var(--border); color:var(--text-muted); text-transform:uppercase; letter-spacing:.4px; }
.timetable th:first-child { width:80px; }
.timetable td { border:1px solid var(--border); vertical-align:top; padding:.5rem; height:70px; }
.timetable td:first-child { background:var(--surface-2); font-size:.75rem; font-weight:700; color:var(--text-muted); text-align:center; vertical-align:middle; }
.slot-block {
    background:linear-gradient(135deg, var(--primary), var(--primary-dark));
    color:#fff; border-radius:8px; padding:.4rem .6rem;
    font-size:.75rem; font-weight:700; height:100%;
    display:flex; flex-direction:column; justify-content:center;
}
.slot-block .slot-subject { font-size:.8rem; font-weight:800; margin-bottom:.15rem; }
.slot-block .slot-class   { font-size:.68rem; opacity:.85; }
.slot-block .slot-room    { font-size:.65rem; opacity:.75; }
</style>
@endpush

@section('content')
@php
    $isFr = app()->getLocale() === 'fr';
    $days = $isFr
        ? ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi']
        : ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    $timeSlots = ['07:30','08:30','09:30','10:30','11:30','12:30','13:30','14:30','15:30','16:30'];
@endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#f97316,#ea580c)"><i data-lucide="calendar"></i></div>
        <div>
            <h1 class="page-title">{{ $isFr ? 'Mon Emploi du Temps' : 'My Teaching Schedule' }}</h1>
            <p class="page-subtitle text-muted">{{ $isFr ? 'Semaine courante' : 'Current week' }}</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0" style="overflow-x:auto">
        <table class="timetable">
            <thead>
                <tr>
                    <th>{{ $isFr ? 'Heure' : 'Time' }}</th>
                    @foreach($days as $day)<th>{{ $day }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($timeSlots as $time)
                <tr>
                    <td>{{ $time }}</td>
                    @for($day = 1; $day <= 6; $day++)
                    @php
                        $slot = ($schedule ?? collect())->first(fn($s) => $s->day_of_week == $day && $s->start_time == $time);
                    @endphp
                    <td>
                        @if($slot)
                        <div class="slot-block">
                            <div class="slot-subject">{{ $slot->subject?->name }}</div>
                            <div class="slot-class">{{ $slot->class?->name }}</div>
                            <div class="slot-room">{{ $slot->room ?? ($isFr ? 'Salle TBD' : 'Room TBD') }}</div>
                        </div>
                        @endif
                    </td>
                    @endfor
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if(($schedule ?? collect())->isEmpty())
<div class="text-center py-5 text-muted">
    <i data-lucide="calendar-off" style="width:36px;opacity:.3;display:block;margin:0 auto 1rem"></i>
    <p>{{ $isFr ? "Aucun cours planifié. Contactez l'administrateur." : 'No classes scheduled. Contact the administrator.' }}</p>
</div>
@endif

@endsection
