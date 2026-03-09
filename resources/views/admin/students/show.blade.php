{{-- admin/students/show.blade.php --}}
@extends('layouts.app')
@section('title', $student->user->name)
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.students.index') }}" class="btn btn-light btn-sm"><i data-lucide="arrow-left" style="width:14px"></i></a>
            <div>
                <h1 class="page-title">{{ $student->user->display_name ?? $student->user->name }}</h1>
                <p class="page-subtitle text-muted">{{ $student->matricule }} · {{ $student->classe?->name }}</p>
            </div>
        </div>
        <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-primary btn-sm">
            <i data-lucide="edit-2" style="width:14px" class="me-1"></i>{{ $isFr ? 'Modifier' : 'Edit' }}
        </a>
    </div>
</div>

<div class="row gy-4">
    {{-- Info card --}}
    <div class="col-lg-4">
        <div class="card text-center py-4 mb-4">
            <div class="card-body">
                <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;font-size:1.8rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                    {{ strtoupper(substr($student->user->name, 0, 1)) }}
                </div>
                <div class="fw-bold mb-1">{{ $student->user->display_name ?? $student->user->name }}</div>
                <div style="font-size:.78rem;color:var(--text-muted)">{{ $student->user->email }}</div>
                <div class="mt-3 text-start" style="font-size:.82rem">
                    @foreach(['Matricule' => $student->matricule, ($isFr?'Classe':'Class') => $student->classe?->name, 'Section' => $student->classe?->section, ($isFr?'Tuteur':'Guardian') => $student->guardian?->user?->name] as $lbl => $val)
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--text-muted)">{{ $lbl }}</span>
                        <strong>{{ $val ?? '—' }}</strong>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Payment status --}}
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">{{ $isFr ? 'Paiements' : 'Payments' }}</h6></div>
            <div class="card-body p-0">
                @forelse($student->payments->take(5) as $pay)
                <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
                    <div>
                        <div style="font-size:.8rem;font-weight:600">XAF {{ number_format($pay->amount) }}</div>
                        <div style="font-size:.7rem;color:var(--text-muted)">{{ $pay->created_at?->format('d/m/Y') }}</div>
                    </div>
                    @if($pay->status === 'success')
                    <span class="badge bg-success" style="font-size:.65rem">✓</span>
                    @else
                    <span class="badge bg-warning" style="font-size:.65rem">{{ $pay->status }}</span>
                    @endif
                </div>
                @empty
                <p class="text-muted p-3 mb-0" style="font-size:.82rem">{{ $isFr ? 'Aucun paiement.' : 'No payments.' }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Grades & absences --}}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="card-title mb-0">{{ $isFr ? 'Notes récentes' : 'Recent grades' }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>{{ $isFr ? 'Matière' : 'Subject' }}</th><th style="text-align:center">{{ $isFr ? 'Note' : 'Grade' }}</th><th>Trimestre</th><th>{{ $isFr ? 'Date' : 'Date' }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse($student->marks->take(10) as $mark)
                        @php $s = (float)$mark->score; $c = $s>=16?'#10b981':($s>=13?'#3b82f6':($s>=10?'#f59e0b':'#ef4444')); @endphp
                        <tr>
                            <td style="font-size:.83rem">{{ $mark->subject?->name }}</td>
                            <td style="text-align:center"><span style="background:{{ $c }}22;color:{{ $c }};padding:.18rem .55rem;border-radius:12px;font-size:.76rem;font-weight:700">{{ number_format($s,2) }}/20</span></td>
                            <td style="font-size:.78rem;color:var(--text-muted)">T{{ $mark->term }}-S{{ $mark->sequence }}</td>
                            <td style="font-size:.75rem;color:var(--text-muted)">{{ $mark->updated_at?->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted">{{ $isFr ? 'Aucune note.' : 'No grades.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">{{ $isFr ? 'Absences' : 'Absences' }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>{{ $isFr ? 'Date' : 'Date' }}</th><th>{{ $isFr ? 'Matière' : 'Subject' }}</th><th style="text-align:center">{{ $isFr ? 'Statut' : 'Status' }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse($student->absences->take(10) as $abs)
                        <tr>
                            <td style="font-size:.83rem">{{ $abs->date?->format('d/m/Y') }}</td>
                            <td style="font-size:.83rem">{{ $abs->subject?->name ?? $isFr ? 'Général' : 'General' }}</td>
                            <td style="text-align:center">
                                @php $map = ['present'=>'bg-success','absent'=>'bg-danger','late'=>'bg-warning','excused'=>'bg-info']; @endphp
                                <span class="badge {{ $map[$abs->status] ?? 'bg-secondary' }}" style="font-size:.65rem">{{ $abs->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-3 text-muted">{{ $isFr ? 'Aucune absence.' : 'No absences.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection


