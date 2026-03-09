{{--
    |--------------------------------------------------------------------------
    | parent/appointments/create.blade.php — Réservation de Rendez-Vous
    |--------------------------------------------------------------------------
    | Phase 7 — Section 8.3 — Système RDV Parent-Enseignant
    | Sélection enseignant → Créneaux disponibles → Confirmation
    --}}

@extends('layouts.app')

@section('title', app()->getLocale() === 'fr' ? 'Demander un Rendez-Vous' : 'Book an Appointment')

@push('styles')
<style>
/* ─── Teacher picker ─────────────────────────────────────────────────────── */
.teacher-picker { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:.75rem; }
.teacher-pick-card {
    border:1.5px solid var(--border); border-radius:var(--radius-lg);
    padding:1rem; text-align:center; cursor:pointer;
    transition:all .2s ease; background:var(--surface);
}
.teacher-pick-card:hover  { border-color:var(--primary); background:var(--primary-bg); transform:translateY(-2px); }
.teacher-pick-card.active { border-color:var(--primary); background:var(--primary-bg); box-shadow:0 0 0 3px rgba(13,148,136,.12); }
.teacher-pick-avatar {
    width:52px;height:52px;border-radius:50%;margin:0 auto .5rem;
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    color:#fff;font-weight:700;font-size:1.2rem;
    display:flex;align-items:center;justify-content:center;
}
.teacher-pick-name { font-weight:700;font-size:.85rem;color:var(--text-primary); }
.teacher-pick-role { font-size:.72rem;color:var(--text-muted); }

/* ─── Availability calendar ──────────────────────────────────────────────── */
.cal-week { display:grid; grid-template-columns:repeat(6,1fr); gap:.5rem; }
.cal-day { }
.cal-day-header {
    text-align:center; font-size:.73rem; font-weight:700; color:var(--text-secondary);
    text-transform:uppercase; letter-spacing:.4px; padding:.4rem;
    border-radius:8px 8px 0 0; background:var(--surface-2);
}
.time-slot {
    display:block; width:100%; text-align:center;
    padding:.4rem .25rem; margin-bottom:.35rem;
    border-radius:8px; font-size:.78rem; font-weight:600;
    border:1.5px solid var(--border); background:var(--surface);
    cursor:pointer; transition:all .15s ease;
}
.time-slot:hover  { border-color:var(--primary); background:var(--primary-bg); color:var(--primary); }
.time-slot.active { border-color:var(--primary); background:var(--primary); color:#fff; }
.time-slot.taken  { opacity:.35; cursor:not-allowed; text-decoration:line-through; pointer-events:none; }
.no-slots { text-align:center;font-size:.78rem;color:var(--text-muted);padding:.75rem 0; }

/* ─── Confirmation card ──────────────────────────────────────────────────── */
.rdv-confirm-card {
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    border-radius:var(--radius-lg); padding:1.5rem; color:#fff; text-align:center;
}
</style>
@endpush

@section('content')

@php
    $isFr     = app()->getLocale() === 'fr';
    $student  = $student ?? auth()->user()->student;
    $teachers = $teachers ?? collect();
    $days     = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    $daysEn   = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
@endphp

<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <i data-lucide="calendar-plus"></i>
        </div>
        <div>
            <h1 class="page-title">{{ $isFr ? 'Demander un Rendez-Vous' : 'Book an Appointment' }}</h1>
            <p class="page-subtitle text-muted">{{ $student?->user?->name }} — {{ $student?->classe?->name }}</p>
        </div>
    </div>
</div>

<div class="row gy-4">

    {{-- Left: Steps form --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">

                {{-- Step 1: Choose teacher --}}
                <div id="step-1">
                    <h5 class="fw-bold mb-3">
                        <span class="badge bg-primary me-2" style="font-size:.8rem">1</span>
                        {{ $isFr ? 'Choisir l\'enseignant' : 'Choose a teacher' }}
                    </h5>
                    <div class="teacher-picker mb-4">
                        @foreach($teachers as $teacher)
                        <div class="teacher-pick-card"
                             id="tp-{{ $teacher->id }}"
                             data-teacher="{{ $teacher->id }}"
                             onclick="selectTeacher({{ $teacher->id }}, '{{ addslashes($teacher->user->name ?? '') }}')">
                            <div class="teacher-pick-avatar">
                                {{ strtoupper(substr($teacher->user->name ?? 'T', 0, 1)) }}
                            </div>
                            <div class="teacher-pick-name">{{ $teacher->user->name }}</div>
                            <div class="teacher-pick-role">
                                @if($teacher->is_prof_principal)
                                    <span style="color:var(--primary)">⭐ {{ $isFr ? 'Prof. Principal' : 'Head Teacher' }}</span>
                                @else
                                    {{ $teacher->subjects->first()?->name ?? ($isFr ? 'Enseignant' : 'Teacher') }}
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Step 2: Slots (lazy-loaded) --}}
                    <div id="slots-section" style="display:none">
                        <h5 class="fw-bold mb-3">
                            <span class="badge bg-primary me-2" style="font-size:.8rem">2</span>
                            {{ $isFr ? 'Choisir un créneau' : 'Choose a time slot' }}
                        </h5>
                        <div id="slots-loading" class="text-center py-3 text-muted">
                            <div class="spinner-border spinner-border-sm me-2"></div>
                            {{ $isFr ? 'Chargement des disponibilités...' : 'Loading availability...' }}
                        </div>
                        <div id="cal-week" class="cal-week" style="display:none"></div>
                    </div>

                    {{-- Step 3: Notes --}}
                    <div id="notes-section" style="display:none;margin-top:1.5rem">
                        <h5 class="fw-bold mb-3">
                            <span class="badge bg-primary me-2" style="font-size:.8rem">3</span>
                            {{ $isFr ? 'Objet de la rencontre (optionnel)' : 'Meeting purpose (optional)' }}
                        </h5>
                        <textarea id="rdv-notes" class="form-control" rows="3"
                                  placeholder="{{ $isFr ? 'Ex: Suivi des notes en mathématiques, comportement en classe...' : 'E.g: Follow-up on math grades, classroom behavior...' }}"
                                  style="resize:none"></textarea>
                    </div>
                </div>

            </div>
            <div class="card-footer d-flex justify-content-end">
                <button class="btn btn-primary px-4" id="submit-rdv" disabled onclick="submitRdv()">
                    <i data-lucide="calendar-check" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Confirmer la demande' : 'Confirm request' }}
                </button>
            </div>
        </div>
    </div>

    {{-- Right: Summary --}}
    <div class="col-lg-4">
        <div class="card mb-3" id="summary-card" style="display:none">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ $isFr ? 'Récapitulatif' : 'Summary' }}</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div id="sum-avatar" class="teacher-pick-avatar" style="width:40px;height:40px;font-size:1rem;flex-shrink:0">?</div>
                    <div>
                        <div id="sum-teacher" class="fw-bold" style="font-size:.88rem">—</div>
                        <div id="sum-slot" style="font-size:.78rem;color:var(--text-muted)">—</div>
                    </div>
                </div>
                <hr>
                <div style="font-size:.82rem;color:var(--text-muted)">
                    <i data-lucide="user" style="width:14px" class="me-2"></i>{{ $student?->user?->name }}
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i data-lucide="info" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Informations' : 'Information' }}
                </h6>
                <ul style="font-size:.82rem;color:var(--text-muted);padding-left:1.2rem;margin:0">
                    <li class="mb-2">{{ $isFr ? 'La demande sera envoyée à l\'enseignant pour validation.' : 'The request will be sent to the teacher for approval.' }}</li>
                    <li class="mb-2">{{ $isFr ? 'Vous recevrez une notification par email et sur l\'application.' : 'You will receive a notification by email and in the app.' }}</li>
                    <li class="mb-2">{{ $isFr ? 'Un rappel vous sera envoyé 24h avant.' : 'A reminder will be sent 24h in advance.' }}</li>
                    <li>{{ $isFr ? 'En cas d\'annulation, prévenez au moins 2h à l\'avance.' : 'If cancelling, please notify at least 2h in advance.' }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const isFr = {{ app()->getLocale() === 'fr' ? 'true' : 'false' }};
let selectedTeacherId = null;
let selectedTeacherName = '';
let selectedSlot = null;

window.selectTeacher = async function(teacherId, name) {
    document.querySelectorAll('.teacher-pick-card').forEach(c => c.classList.remove('active'));
    document.getElementById(`tp-${teacherId}`)?.classList.add('active');
    selectedTeacherId = teacherId;
    selectedTeacherName = name;
    selectedSlot = null;

    document.getElementById('submit-rdv').disabled = true;
    document.getElementById('slots-section').style.display = '';
    document.getElementById('notes-section').style.display = '';
    document.getElementById('slots-loading').style.display = '';
    document.getElementById('cal-week').style.display = 'none';
    document.getElementById('summary-card').style.display = '';
    document.getElementById('sum-teacher').textContent = name;
    document.getElementById('sum-avatar').textContent = name.charAt(0).toUpperCase();
    document.getElementById('sum-slot').textContent = isFr ? 'Créneau non sélectionné' : 'No slot selected';

    // Load availability
    try {
        const res  = await fetch(`/parent/appointments/slots/${teacherId}`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        renderSlots(data.slots ?? []);
    } catch {
        document.getElementById('slots-loading').innerHTML = `<span class="text-danger">${isFr ? 'Erreur de chargement.' : 'Load error.'}</span>`;
    }
};

function renderSlots(slots) {
    document.getElementById('slots-loading').style.display = 'none';
    const cal = document.getElementById('cal-week');
    cal.style.display = 'grid';
    const daysLabels = isFr
        ? ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi']
        : ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    // Group slots by day
    const grouped = {};
    for (let i = 1; i <= 6; i++) grouped[i] = [];
    (slots || []).forEach(s => { if (grouped[s.day]) grouped[s.day].push(s); });

    cal.innerHTML = Object.entries(grouped).map(([day, daySlots]) => `
        <div class="cal-day">
            <div class="cal-day-header">${daysLabels[day - 1]}</div>
            ${daySlots.length === 0
                ? `<div class="no-slots">—</div>`
                : daySlots.map(s => `
                    <button class="time-slot ${s.taken ? 'taken' : ''}"
                            data-slot="${s.id}"
                            data-label="${s.label}"
                            onclick="selectSlot(${s.id}, '${daysLabels[day - 1]} ${s.label}')">
                        ${s.label}
                    </button>`).join('')}
        </div>`).join('');
}

window.selectSlot = function(slotId, label) {
    document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('active'));
    document.querySelector(`.time-slot[data-slot="${slotId}"]`)?.classList.add('active');
    selectedSlot = slotId;
    document.getElementById('sum-slot').textContent = label;
    document.getElementById('submit-rdv').disabled = false;
};

window.submitRdv = async function() {
    const btn = document.getElementById('submit-rdv');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + (isFr ? 'Envoi...' : 'Sending...');
    try {
        const res = await fetch('/parent/appointments', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({
                teacher_id:         selectedTeacherId,
                availability_slot:  selectedSlot,
                student_id:         {{ $student?->id ?? 'null' }},
                notes:              document.getElementById('rdv-notes').value,
            }),
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = '/parent/appointments?success=1';
        } else {
            alert(data.message ?? (isFr ? 'Erreur lors de la demande.' : 'Request error.'));
            btn.disabled = false;
            btn.innerHTML = isFr ? 'Confirmer la demande' : 'Confirm request';
        }
    } catch {
        btn.disabled = false;
    }
};
</script>
@endpush
