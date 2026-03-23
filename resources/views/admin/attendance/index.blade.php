@extends('layouts.app')
@section('title', 'Gestion des Absences des Enseignants')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Absences Enseignants</span>
    </div>
    <h1 class="page-title">Gestion des Absences des Enseignants</h1>
    <p class="page-subtitle">Gérer les absences des membres du personnel enseignant</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.attendance.create') }}" class="btn btn-primary">
      <i data-lucide="plus" style="width:14px;height:14px"></i>
      Ajouter Absence
    </a>
    <a href="{{ route('admin.attendance.report') }}" class="btn btn-secondary" style="margin-left:8px">
      <i data-lucide="bar-chart-2" style="width:14px;height:14px"></i>
      Rapport
    </a>
  </div>
</div>

{{-- Stats KPI Grid --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:20px">
  <div class="card kpi-card shadow-sm h-100">
    <div class="card-body">
      <div style="color:var(--text-muted);font-size:12px;margin-bottom:8px">Total d'enregistrements</div>
      <div style="font-size:24px;font-weight:700">{{ $stats['total_records'] ?? 0 }}</div>
    </div>
  </div>
  <div class="card kpi-card shadow-sm h-100">
    <div class="card-body">
      <div style="color:var(--text-muted);font-size:12px;margin-bottom:8px">Absences</div>
      <div style="font-size:24px;font-weight:700;color:var(--danger)">{{ $stats['absences'] ?? 0 }}</div>
    </div>
  </div>
  <div class="card kpi-card shadow-sm h-100">
    <div class="card-body">
      <div style="color:var(--text-muted);font-size:12px;margin-bottom:8px">Justifiées</div>
      <div style="font-size:24px;font-weight:700;color:var(--success)">{{ $stats['justified'] ?? 0 }}</div>
    </div>
  </div>
  <div class="card kpi-card shadow-sm h-100">
    <div class="card-body">
      <div style="color:var(--text-muted);font-size:12px;margin-bottom:8px">En attente d'approbation</div>
      <div style="font-size:24px;font-weight:700;color:var(--info)">{{ $stats['pending'] ?? 0 }}</div>
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
        <label class="form-label">Enseignant</label>
        <select class="form-control" name="teacher_id">
          <option value="">Tous les enseignants</option>
          @foreach($teachers ?? [] as $teacher)
            <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
              {{ $teacher->user?->name ?? $teacher->id }}
            </option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="form-label">Statut</label>
        <select class="form-control" name="status">
          <option value="">Tous les statuts</option>
          <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Présent</option>
          <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
          <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>En retard</option>
          <option value="justified" {{ request('status') === 'justified' ? 'selected' : '' }}>Justifié</option>
          <option value="medical_leave" {{ request('status') === 'medical_leave' ? 'selected' : '' }}>Congé maladie</option>
          <option value="authorized_leave" {{ request('status') === 'authorized_leave' ? 'selected' : '' }}>Congé autorisé</option>
        </select>
      </div>
      <div>
        <label class="form-label">Approbation</label>
        <select class="form-control" name="approval_status">
          <option value="">Tous les statuts</option>
          <option value="pending" {{ request('approval_status') === 'pending' ? 'selected' : '' }}>En attente</option>
          <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>Approuvé</option>
        </select>
      </div>
      <div>
        <label class="form-label">Date Début</label>
        <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
      </div>
      <div>
        <label class="form-label">Date Fin</label>
        <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
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

{{-- Attendance Table --}}
<div class="card">
  <div class="card-header">
    <i data-lucide="clock" style="width:16px;height:16px"></i>
    <span>Absences des Enseignants</span>
    <span style="margin-left:auto;font-size:12px;color:var(--text-muted)">
      {{ $attendances?->total() ?? 0 }} total
    </span>
  </div>
  <div class="card-body">
    <div style="overflow-x:auto">
      <table class="table">
        <thead>
          <tr>
            <th>Enseignant</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Raison</th>
            <th>Approuvé</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($attendances ?? [] as $attendance)
          <tr>
            <td>
              <div style="font-weight:600;font-size:13px">{{ $attendance->teacher?->user?->name ?? '-' }}</div>
              <small style="color:var(--text-muted)">{{ $attendance->teacher?->specialization ?? '-' }}</small>
            </td>
            <td>
              <div style="font-size:12px">{{ $attendance->date?->format('d/m/Y') ?? '-' }}</div>
            </td>
            <td>
              @php
                $statusMap = [
                  'present' => ['color' => 'var(--success)', 'label' => 'Présent'],
                  'absent' => ['color' => 'var(--danger)', 'label' => 'Absent'],
                  'late' => ['color' => 'var(--warning)', 'label' => 'En retard'],
                  'justified' => ['color' => 'var(--info)', 'label' => 'Justifié'],
                  'medical_leave' => ['color' => '#ff9800', 'label' => 'Congé maladie'],
                  'authorized_leave' => ['color' => '#2196f3', 'label' => 'Congé autorisé'],
                ];
                $status = $statusMap[$attendance->status] ?? ['color' => 'var(--text-muted)', 'label' => $attendance->status];
              @endphp
              <span style="background:{{ $status['color'] }}20;color:{{ $status['color'] }};padding:4px 8px;border-radius:4px;
                           font-size:11px;font-weight:600">
                {{ $status['label'] }}
              </span>
            </td>
            <td>
              <small style="color:var(--text-muted)">{{ Str::limit($attendance->reason ?? '—', 35) }}</small>
            </td>
            <td>
              @if($attendance->is_approved)
                <span style="background:var(--success-bg);color:var(--success);padding:4px 8px;border-radius:4px;
                             font-size:11px;font-weight:600;display:inline-block">
                  <i data-lucide="check" style="width:11px;height:11px;display:inline;margin-right:4px"></i>
                  Oui
                </span>
              @else
                <span style="background:var(--warning-bg);color:var(--warning);padding:4px 8px;border-radius:4px;
                             font-size:11px;font-weight:600;display:inline-block">
                  <i data-lucide="clock" style="width:11px;height:11px;display:inline;margin-right:4px"></i>
                  En attente
                </span>
              @endif
            </td>
            <td style="text-align:right">
              <div style="display:flex;gap:6px;justify-content:flex-end">
                <a href="{{ route('admin.attendance.edit', $attendance) }}" class="btn btn-sm" title="Éditer"
                   style="background:var(--primary-bg);color:var(--primary)">
                  <i data-lucide="edit-2" style="width:13px;height:13px"></i>
                </a>
                <form method="POST" action="{{ route('admin.attendance.destroy', $attendance) }}" style="display:inline"
                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette absence ?')">
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
              <div style="color:var(--text-muted);font-size:13px">Aucune absence enregistrée</div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($attendances?->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
      <small style="color:var(--text-muted);font-size:12px">
        Affichage {{ $attendances->firstItem() }} à {{ $attendances->lastItem() }} sur {{ $attendances->total() }} absences
      </small>
      <div>
        {!! $attendances->links('pagination::simple-tailwind') !!}
      </div>
    </div>
    @endif
  </div>
</div>

@endsection


