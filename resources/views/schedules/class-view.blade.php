@extends('layouts.app')
@section('title', 'Emploi du Temps par Classe')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Emploi du Temps — {{ $classe->name ?? 'Classe' }}</h1>
        <a href="{{ route('schedules.export', ['class_id' => $classe->id ?? '']) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i>Exporter</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>Heure</th>
                            @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $day)
                            <th>{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($timeSlots ?? [] as $slot)
                        <tr>
                            <td class="fw-bold text-nowrap">{{ $slot }}</td>
                            @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $day)
                            @php $entry = $grid[$slot][$day] ?? null; @endphp
                            <td class="{{ $entry ? 'bg-light' : '' }}">
                                @if($entry)
                                <strong class="text-primary small">{{ $entry->subject->name ?? '—' }}</strong><br>
                                <span class="text-muted small">{{ $entry->teacher->user->name ?? '—' }}</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Aucun créneau défini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
