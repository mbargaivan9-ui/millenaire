{{--
    |--------------------------------------------------------------------------
    | parent/dashboard.blade.php — Tableau de Bord Parent
    |--------------------------------------------------------------------------
    | Phase 7 — Espace Parents
    | Multi-comptes enfants, monitoring temps réel, bulletins, absences, paiements
    --}}

@extends('layouts.app')

@section('title', app()->getLocale() === 'fr' ? 'Espace Parent' : 'Parent Dashboard')

@push('styles')
<style>
/* ─── Child Selector ──────────────────────────────────────────────────────── */
.child-selector {
    display: flex; gap: .75rem; flex-wrap: wrap; margin-bottom: 1.5rem;
}
.child-card {
    display: flex; align-items: center; gap: .75rem;
    padding: .75rem 1.25rem; border-radius: var(--radius-lg);
    border: 2px solid var(--border);
    cursor: pointer; transition: all .2s ease;
    background: var(--surface);
}
.child-card:hover { border-color: var(--primary); background: var(--primary-bg); }
.child-card.active { border-color: var(--primary); background: var(--primary-bg); box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
.child-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.child-name { font-weight: 700; font-size: .9rem; color: var(--text-primary); }
.child-class { font-size: .75rem; color: var(--text-muted); }

/* ─── Alert threshold badge ───────────────────────────────────────────────── */
.alert-low { background: #fef2f2; border-left: 3px solid #ef4444; border-radius: 8px; padding: .75rem 1rem; }
.alert-info-custom { background: var(--primary-bg); border-left: 3px solid var(--primary); border-radius: 8px; padding: .75rem 1rem; }

/* ─── Recent grades mini table ───────────────────────────────────────────── */
.grade-chip {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .6rem; border-radius: 20px;
    font-size: .78rem; font-weight: 700;
}

/* ─── RDV button ─────────────────────────────────────────────────────────── */
.rdv-card {
    border: 1px solid var(--border); border-radius: var(--radius);
    padding: .75rem; display: flex; align-items: center;
    gap: .75rem; margin-bottom: .75rem;
    transition: box-shadow .2s ease;
}
.rdv-card:hover { box-shadow: var(--shadow-md); }
</style>
@endpush

@section('content')

@php
    $isFr     = app()->getLocale() === 'fr';
    $guardian = auth()->user()->guardian ?? null;
    $children = $children ?? collect();
    $selected = $selectedChild ?? $children->first();
    $notes    = $recentGrades ?? collect();
    $absences = $recentAbsences ?? collect();
    $payments = $pendingPayments ?? collect();
    $bulletins= $bulletins ?? collect();
@endphp

{{-- ─── Page Header ─────────────────────────────────────────────────────────── --}}
<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
            <i data-lucide="users"></i>
        </div>
        <div>
            <h1 class="page-title">{{ $isFr ? 'Espace Parent' : 'Parent Space' }}</h1>
            <p class="page-subtitle text-muted">
                {{ $isFr ? 'Bienvenue,' : 'Welcome,' }} {{ auth()->user()->display_name ?? auth()->user()->name }}
            </p>
        </div>

        {{-- Real-time notification bell --}}
        <div class="ms-auto">
            <span class="badge bg-danger" id="rt-alert" style="display:none">
                <i data-lucide="alert-circle" style="width:12px"></i>
                <span id="rt-alert-text"></span>
            </span>
        </div>
    </div>
</div>

{{-- ─── Child Selector ──────────────────────────────────────────────────────── --}}
@if($children->count() > 1)
<div class="child-selector">
    @foreach($children as $child)
    <a href="{{ route('parent.dashboard', ['child_id' => $child->id]) }}"
       class="child-card {{ $selected?->id === $child->id ? 'active' : '' }}">
        <div class="child-avatar">
            {{ strtoupper(substr($child->user->name ?? 'E', 0, 1)) }}
        </div>
        <div>
            <div class="child-name">{{ $child->user->display_name ?? $child->user->name }}</div>
            <div class="child-class">{{ $child->classe?->name }}</div>
        </div>
        @if($child->id === $selected?->id)
        <i data-lucide="check-circle" style="width:18px;color:var(--primary)"></i>
        @endif
    </a>
    @endforeach
</div>
@endif

@if($selected)

{{-- ─── KPI Cards ────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @php
        $moyenne = $bulletinData['moyenne'] ?? null;
        $rang    = $bulletinData['rang'] ?? null;
        $absencesCount = $absences->count();
        $pendingAmt = $payments->sum('amount_due');
    @endphp

    {{-- Overall Average --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? 'Moyenne Générale' : 'Overall Average' }}</p>
                        <h2 class="fw-bold mb-0">{{ $moyenne !== null ? number_format((float)$moyenne, 2) : '—' }}<small style="font-size:0.6em">/20</small></h2>
                    </div>
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">📊</div>
                </div>
                <div class="mt-2 small text-muted">{{ $isFr ? 'Performance' : 'Performance' }}</div>
            </div>
        </div>
    </div>

    {{-- Class Rank --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? 'Rang dans la Classe' : 'Class Rank' }}</p>
                        <h2 class="fw-bold mb-0">{{ $rang ? $rang . 'e' : '—' }}</h2>
                    </div>
                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning">🏆</div>
                </div>
                <div class="mt-2 small text-muted">{{ $isFr ? 'Classement' : 'Ranking' }}</div>
            </div>
        </div>
    </div>

    {{-- Absences --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? 'Absences (mois)' : 'Absences (month)' }}</p>
                        <h2 class="fw-bold mb-0 {{ $absencesCount > 3 ? 'text-danger' : 'text-success' }}">{{ $absencesCount }}</h2>
                    </div>
                    <div class="kpi-icon {{ $absencesCount > 3 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }}">📅</div>
                </div>
                <div class="mt-2 small text-muted">{{ $isFr ? 'Ce mois-ci' : 'This month' }}</div>
            </div>
        </div>
    </div>

    {{-- Pending Fees --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? 'Frais en attente' : 'Pending fees' }}</p>
                        <h2 class="fw-bold mb-0 {{ $pendingAmt > 0 ? 'text-warning' : 'text-success' }}">
                            {{ $pendingAmt > 0 ? number_format($pendingAmt, 0) : '✅' }}
                        </h2>
                    </div>
                    <div class="kpi-icon {{ $pendingAmt > 0 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success' }}">💰</div>
                </div>
                <div class="mt-2 small text-muted">{{ $pendingAmt > 0 ? 'FCFA' : ($isFr ? 'Payé' : 'Paid') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Content Grid ─────────────────────────────────────────────────────────── --}}
<div class="row gy-4">

    {{-- Left Column: Bulletins + Notes récentes --}}
    <div class="col-lg-8">

        {{-- Bulletins publiés --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">
                    <i data-lucide="file-text" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Bulletins Scolaires' : 'Report Cards' }}
                </h6>
            </div>
            <div class="card-body p-0">
                @forelse($bulletins as $bulletin)
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:40px;height:40px;border-radius:10px;background:var(--primary-bg);display:flex;align-items:center;justify-content:center">
                            <i data-lucide="file-text" style="width:18px;color:var(--primary)"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="font-size:.88rem">
                                {{ $isFr ? 'Trimestre ' . $bulletin->term : 'Term ' . $bulletin->term }}
                                · {{ $isFr ? 'Séquence ' . $bulletin->sequence : 'Sequence ' . $bulletin->sequence }}
                            </div>
                            <div class="text-muted" style="font-size:.75rem">
                                {{ $isFr ? 'Publié le' : 'Published' }} {{ $bulletin->published_at?->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('parent.bulletin.show', $bulletin->id) }}" class="btn btn-sm btn-light">
                            <i data-lucide="eye" style="width:14px" class="me-1"></i>
                            {{ $isFr ? 'Voir' : 'View' }}
                        </a>
                        <a href="{{ route('parent.bulletin.pdf', $bulletin->id) }}" class="btn btn-sm btn-primary" target="_blank">
                            <i data-lucide="download" style="width:14px"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i data-lucide="file-text" style="width:32px;opacity:.3"></i>
                    <p class="mt-2 mb-0" style="font-size:.85rem">{{ $isFr ? 'Aucun bulletin disponible.' : 'No report cards available.' }}</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Notes récentes --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-lucide="pen-tool" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Notes Récentes' : 'Recent Grades' }}
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ $isFr ? 'Matière' : 'Subject' }}</th>
                                <th style="text-align:center">{{ $isFr ? 'Note' : 'Grade' }}</th>
                                <th>{{ $isFr ? 'Enseignant' : 'Teacher' }}</th>
                                <th>{{ $isFr ? 'Date' : 'Date' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notes as $mark)
                            @php
                                $score = (float)$mark->score;
                                $color = $score >= 16 ? '#10b981' : ($score >= 13 ? '#3b82f6' : ($score >= 10 ? '#f59e0b' : '#ef4444'));
                            @endphp
                            <tr>
                                <td class="fw-semibold" style="font-size:.85rem">{{ $mark->subject?->name }}</td>
                                <td style="text-align:center">
                                    <span class="grade-chip" style="background:{{ $color }}22;color:{{ $color }}">
                                        {{ number_format($score, 2) }}/20
                                    </span>
                                </td>
                                <td style="font-size:.82rem;color:var(--text-muted)">{{ $mark->teacher?->user?->name }}</td>
                                <td style="font-size:.78rem;color:var(--text-muted)">{{ $mark->updated_at?->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">{{ $isFr ? 'Aucune note.' : 'No grades yet.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Absences + Paiements + RDV --}}
    <div class="col-lg-4">

        {{-- Alert paiement en attente --}}
        @if($pendingAmt > 0)
        <div class="alert-low mb-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i data-lucide="alert-triangle" style="width:16px;color:#ef4444"></i>
                <strong style="font-size:.88rem;color:#dc2626">{{ $isFr ? 'Paiement requis' : 'Payment required' }}</strong>
            </div>
            <p class="mb-2" style="font-size:.82rem">
                {{ $isFr ? 'Des frais sont en attente de règlement.' : 'Some fees are pending payment.' }}
            </p>
            <a href="{{ route('payment.mobile-money', ['student_id' => $selected->id]) }}" class="btn btn-danger btn-sm w-100">
                <i data-lucide="smartphone" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Payer maintenant — XAF ' . number_format($pendingAmt, 0, ',', ' ') : 'Pay now — XAF ' . number_format($pendingAmt, 0, ',', ' ') }}
            </a>
        </div>
        @endif

        {{-- Absences --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-lucide="calendar-x" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Absences Récentes' : 'Recent Absences' }}
                </h6>
            </div>
            <div class="card-body">
                @forelse($absences->take(5) as $absence)
                <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded-3" style="background:var(--surface-2)">
                    <i data-lucide="{{ $absence->justified ? 'check-circle' : 'alert-circle' }}"
                       style="width:16px;color:{{ $absence->justified ? '#10b981' : '#ef4444' }};flex-shrink:0"></i>
                    <div class="flex-grow-1">
                        <div style="font-size:.82rem;font-weight:600">{{ $absence->date?->format('d/m/Y') }}</div>
                        <div style="font-size:.72rem;color:var(--text-muted)">
                            {{ $absence->subject?->name ?? ($isFr ? 'Toute la journée' : 'All day') }}
                            @if($absence->justified)
                            · <span style="color:#10b981">{{ $isFr ? 'Justifiée' : 'Justified' }}</span>
                            @else
                            · <span style="color:#ef4444">{{ $isFr ? 'Non justifiée' : 'Unjustified' }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted mb-0" style="font-size:.83rem">
                    ✅ {{ $isFr ? 'Aucune absence ce mois-ci.' : 'No absences this month.' }}
                </p>
                @endforelse
            </div>
        </div>

        {{-- RDV avec enseignants --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">
                    <i data-lucide="calendar-check" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Prendre Rendez-Vous' : 'Book Appointment' }}
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:.82rem">
                    {{ $isFr ? 'Demandez un RDV avec un enseignant ou le professeur principal.' : 'Request an appointment with a teacher or head teacher.' }}
                </p>
                <a href="{{ route('parent.appointments.create', ['student_id' => $selected->id]) }}"
                   class="btn btn-primary w-100">
                    <i data-lucide="plus" style="width:14px" class="me-1"></i>
                    {{ $isFr ? 'Demander un Rendez-Vous' : 'Request an Appointment' }}
                </a>

                @if($upcomingAppointments->isNotEmpty() ?? false)
                <div class="mt-3">
                    <h6 class="fw-semibold" style="font-size:.82rem;color:var(--text-muted)">
                        {{ $isFr ? 'À VENIR' : 'UPCOMING' }}
                    </h6>
                    @foreach($upcomingAppointments ?? [] as $apt)
                    <div class="rdv-card">
                        <div style="width:36px;height:36px;border-radius:8px;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i data-lucide="calendar" style="width:16px;color:var(--primary)"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate" style="font-size:.82rem">{{ $apt->teacher?->user?->name }}</div>
                            <div style="font-size:.72rem;color:var(--text-muted)">{{ $apt->scheduled_at?->format('d/m/Y H:i') }}</div>
                        </div>
                        <span class="badge {{ $apt->status === 'confirmed' ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size:.68rem">
                            {{ $apt->status === 'confirmed' ? ($isFr ? 'Confirmé' : 'Confirmed') : ($isFr ? 'En attente' : 'Pending') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

@else
{{-- No child linked --}}
<div class="card">
    <div class="card-body text-center py-5">
        <i data-lucide="users" style="width:48px;opacity:.3;color:var(--text-muted)"></i>
        <h5 class="mt-3 text-muted">{{ $isFr ? 'Aucun enfant rattaché à votre compte.' : 'No child linked to your account.' }}</h5>
        <p class="text-muted" style="font-size:.88rem">
            {{ $isFr ? 'Contactez l\'administration pour lier votre compte à celui de votre enfant.' : 'Contact administration to link your account to your child\'s.' }}
        </p>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Real-time notifications — Absence & Notes alertes
@if(isset($selected))
if (typeof window.Echo !== 'undefined') {
    // Listen for child absence in real-time
    window.Echo.private('guardian.{{ auth()->id() }}')
        .listen('StudentAbsenceRecorded', (data) => {
            const alertEl = document.getElementById('rt-alert');
            const alertText = document.getElementById('rt-alert-text');
            if (alertEl && data.student_id === {{ $selected->id }}) {
                alertEl.style.display = '';
                alertText.textContent = '{{ $isFr ? 'Absence signalée' : 'Absence recorded' }}';
                // Show toast
                showToast('warning', `{{ $isFr ? 'Absence enregistrée pour' : 'Absence recorded for' }} ${data.student_name}`);
            }
        })
        .listen('BulletinPublished', (data) => {
            showToast('success', '{{ $isFr ? 'Nouveau bulletin disponible !' : 'New report card available!' }}');
        });
}
@endif

function showToast(type, message) {
    const colors = { success:'#10b981', warning:'#f59e0b', error:'#ef4444', info:'#3b82f6' };
    const toast = document.createElement('div');
    toast.style.cssText = `position:fixed;top:20px;right:20px;z-index:9999;background:${colors[type]||colors.info};color:#fff;padding:.9rem 1.4rem;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,.15);font-weight:600;font-size:.88rem;max-width:320px;animation:slideInRight .3s ease`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.animation='fadeOut .3s ease forwards'; setTimeout(()=>toast.remove(),300); }, 5000);
}
</script>
@endpush
