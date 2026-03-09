{{--
    |--------------------------------------------------------------------------
    | teacher/bulletin/ocr-wizard.blade.php — Digitaliseur de Bulletin OCR
    |--------------------------------------------------------------------------
    | Phase 5 — Section 6.2 — Digitaliseur OCR de Bulletins
    | Wizard 7 étapes: Upload → Détection → Mapping zones → Aperçu → Validation
    --}}

@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Digitaliseur OCR de Bulletin' : 'Bulletin OCR Digitizer');
@endphp

@section('title', $pageTitle)

@push('styles')
<style>
/* ─── Wizard steps ───────────────────────────────────────────────────────── */
.wizard-steps {
    display:flex; gap:0; margin-bottom:2rem; overflow-x:auto;
    border-bottom:2px solid var(--border); padding-bottom:1rem;
}
.w-step {
    display:flex; align-items:center; gap:.5rem;
    padding:.5rem 1rem; white-space:nowrap;
    font-size:.8rem; font-weight:600; color:var(--text-muted);
    cursor:pointer;
}
.w-step.active { color:var(--primary); }
.w-step.done   { color:#10b981; }
.w-step-num {
    width:26px;height:26px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:.75rem;font-weight:800;
    background:var(--surface-2); color:var(--text-muted);
    flex-shrink:0;
}
.w-step.active .w-step-num { background:var(--primary); color:#fff; }
.w-step.done   .w-step-num { background:#10b981; color:#fff; }

/* ─── Canvas zone mapping ────────────────────────────────────────────────── */
.canvas-wrapper {
    position:relative; display:inline-block;
    cursor:crosshair; border:2px solid var(--border);
    border-radius:var(--radius); overflow:hidden;
    max-width:100%;
}
.canvas-wrapper img { display:block; max-width:100%; height:auto; }
.zone-overlay {
    position:absolute; border:2px solid var(--primary); background:rgba(13,148,136,.15);
    pointer-events:none; border-radius:3px;
}
.zone-label {
    position:absolute; background:var(--primary); color:#fff;
    font-size:.65rem; font-weight:700; padding:.15rem .4rem;
    border-radius:3px; top:-1px; left:-1px; white-space:nowrap;
}

/* ─── Zone list ──────────────────────────────────────────────────────────── */
.zone-item {
    display:flex;align-items:center;gap:.75rem;
    padding:.65rem; border-radius:8px; border:1.5px solid var(--border);
    margin-bottom:.5rem; transition:all .15s ease; cursor:pointer;
}
.zone-item:hover { border-color:var(--primary); background:var(--primary-bg); }
.zone-item.active { border-color:var(--primary); background:var(--primary-bg); }
.zone-item.mapped { border-color:#10b981; background:#f0fdf4; }
.zone-dot { width:10px;height:10px;border-radius:50%;flex-shrink:0; }

/* ─── WYSIWYG preview ────────────────────────────────────────────────────── */
.bulletin-preview-table { border-collapse:collapse; width:100%; }
.bulletin-preview-table th, .bulletin-preview-table td { border:1px solid #ccc; padding:.4rem .75rem; font-size:.82rem; }
.bulletin-preview-table th { background:#f8fafc; font-weight:700; }
.editable-cell { outline:none; }
.editable-cell:focus { background:#fffbeb; }
</style>
@endpush

@section('content')

@php
    $isFr = app()->getLocale() === 'fr';
    $zones = [
        ['id' => 'matieres',     'label_fr' => 'Colonne Matières',     'label_en' => 'Subjects Column',    'color' => '#0d9488'],
        ['id' => 'notes',        'label_fr' => 'Colonne Notes',        'label_en' => 'Grades Column',      'color' => '#3b82f6'],
        ['id' => 'coefficients', 'label_fr' => 'Colonne Coefficients', 'label_en' => 'Coefficients Column','color' => '#8b5cf6'],
        ['id' => 'moyennes',     'label_fr' => 'Colonne Moyennes',     'label_en' => 'Average Column',     'color' => '#f59e0b'],
        ['id' => 'moy_generale', 'label_fr' => 'Moyenne Générale',     'label_en' => 'General Average',    'color' => '#ef4444'],
        ['id' => 'rang',         'label_fr' => 'Rang',                 'label_en' => 'Rank',               'color' => '#10b981'],
        ['id' => 'appreciations','label_fr' => 'Appréciations',        'label_en' => 'Remarks',            'color' => '#f97316'],
    ];
@endphp

{{-- ─── Page Header ─────────────────────────────────────────────────────────── --}}
<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
            <i data-lucide="scan"></i>
        </div>
        <div>
            <h1 class="page-title">{{ $isFr ? 'Digitaliseur OCR de Bulletin' : 'Bulletin OCR Digitizer' }}</h1>
            <p class="page-subtitle text-muted">{{ $isFr ? 'Convertissez votre bulletin papier en formulaire numérique interactif' : 'Convert your paper report card into an interactive digital form' }}</p>
        </div>
    </div>
</div>

{{-- ─── Wizard Steps Bar ─────────────────────────────────────────────────────── --}}
<div class="wizard-steps" id="wizard-steps">
    @foreach([
        ['fr' => 'Upload', 'en' => 'Upload'],
        ['fr' => 'Détection', 'en' => 'Detect'],
        ['fr' => 'Mappage', 'en' => 'Mapping'],
        ['fr' => 'WYSIWYG', 'en' => 'WYSIWYG'],
        ['fr' => 'Données', 'en' => 'Data'],
        ['fr' => 'Calculs', 'en' => 'Calculate'],
        ['fr' => 'Export', 'en' => 'Export'],
    ] as $i => $step)
    <div class="w-step {{ $i === 0 ? 'active' : '' }}" id="ws-{{ $i }}" onclick="goWizardStep({{ $i }})">
        <div class="w-step-num" id="wsn-{{ $i }}">{{ $i + 1 }}</div>
        {{ $isFr ? $step['fr'] : $step['en'] }}
    </div>
    @endforeach
</div>

<div class="row gy-4">
    <div class="col-lg-8">

        {{-- ══ STEP 0: Upload ══ --}}
        <div class="wizard-panel card" id="wp-0">
            <div class="card-body text-center p-5">
                <div class="import-drop-zone" id="ocr-drop-zone"
                     onclick="document.getElementById('ocr-file').click()"
                     ondragover="event.preventDefault();this.classList.add('drag-over')"
                     ondragleave="this.classList.remove('drag-over')"
                     ondrop="handleOcrDrop(event)"
                     style="border:2px dashed var(--border);border-radius:var(--radius-lg);padding:3rem;cursor:pointer;transition:all .2s">
                    <i data-lucide="scan" style="width:56px;color:var(--primary);display:block;margin:0 auto .75rem"></i>
                    <h5 class="fw-bold">{{ $isFr ? 'Glissez votre bulletin PDF ou image ici' : 'Drop your bulletin PDF or image here' }}</h5>
                    <p class="text-muted" style="font-size:.85rem">{{ $isFr ? 'Formats supportés: PDF, PNG, JPEG, JPG — max 20MB' : 'Supported: PDF, PNG, JPEG, JPG — max 20MB' }}</p>
                    <button class="btn btn-primary mt-2">{{ $isFr ? 'Choisir un fichier' : 'Choose file' }}</button>
                    <input type="file" id="ocr-file" accept=".pdf,.png,.jpg,.jpeg" hidden onchange="handleOcrFile(this.files[0])">
                </div>
                <div id="upload-progress" style="display:none;margin-top:1.5rem">
                    <div class="d-flex align-items-center gap-3">
                        <div class="spinner-border text-primary spinner-border-sm"></div>
                        <span style="font-size:.88rem">{{ $isFr ? 'Analyse OCR en cours...' : 'Running OCR analysis...' }}</span>
                    </div>
                    <div class="progress mt-2" style="height:6px">
                        <div class="progress-bar bg-primary" id="ocr-progress-bar" style="width:0%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ STEP 1: OCR Detection WITH IMAGE PREVIEW ══ --}}
        <div class="wizard-panel card" id="wp-1" style="display:none">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0">{{ $isFr ? 'Résultat de la détection OCR' : 'OCR Detection Result' }}</h6>
                    <span id="ocr-confidence-badge" class="badge bg-success" style="font-size:.75rem;display:none">
                        {{ $isFr ? 'Confiance:' : 'Confidence:' }} <span id="confidence-value">0</span>%
                    </span>
                </div>
            </div>
            <div class="card-body">
                {{-- Two-column layout: Image + Text --}}
                <div class="row gy-3">
                    {{-- Left column: Original bulletin image --}}
                    <div class="col-lg-6">
                        <div style="border: 2px solid var(--border); border-radius: var(--radius); overflow: hidden; background: #f8f9fa;">
                            <div style="background: #e9ecef; padding: 0.5rem; text-align: center; font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">
                                {{ $isFr ? 'Bulletin Original' : 'Original Bulletin' }}
                            </div>
                            <div id="detected-image-container" style="position:relative; display:flex; align-items:center; justify-content:center; min-height:400px; background:#fff;">
                                <img id="detected-image" src="" alt="{{ $isFr ? 'Bulletin' : 'Bulletin' }}" 
                                     style="display:none; width:100%; height:auto; max-height:500px; object-fit:contain; background:#fff;" onload="this.style.display='block';">
                                <div id="detected-image-loading" style="text-align:center; color:var(--text-muted);">
                                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status">
                                        <span class="visually-hidden">{{ $isFr ? 'Chargement...' : 'Loading...' }}</span>
                                    </div>
                                    <div style="font-size:0.85rem">{{ $isFr ? 'Attendez la détection OCR...' : 'Waiting for OCR detection...' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right column: Extracted text --}}
                    <div class="col-lg-6">
                        <div style="display: flex; flex-direction: column; height: 100%; gap: 1rem;">
                            {{-- Text Display --}}
                            <div style="flex: 1; border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem; background: var(--surface-2); overflow-y: auto;">
                                <div style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-muted); text-transform: uppercase;">
                                    {{ $isFr ? 'Texte extrait' : 'Extracted Text' }}
                                </div>
                                <div id="detected-text" style="font-family:monospace; font-size:0.75rem; white-space:pre-wrap; word-break:break-word; max-height:400px; overflow-y:auto; line-height:1.4;">
                                    {{ $isFr ? 'Le texte détecté apparaîtra ici...' : 'Detected text will appear here...' }}
                                </div>
                            </div>

                            {{-- Metadata --}}
                            <div id="ocr-metadata" style="padding: 0.75rem; background: var(--surface-2); border-radius: var(--radius); font-size: 0.8rem; display: none;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span style="font-weight: 600; color: var(--primary);" id="ocr-method">OCR.Space</span>
                                    <span id="ocr-fallback-badge" class="badge bg-warning" style="display:none; font-size: 0.7rem;">
                                        {{ $isFr ? 'Fallback' : 'Fallback' }}
                                    </span>
                                </div>
                                <div style="color: var(--text-muted);">
                                    <span id="subjects-count">0</span> {{ $isFr ? 'matière(s)' : 'subject(s)' }} - 
                                    <span id="tables-count">0</span> {{ $isFr ? 'table(s)' : 'table(s)' }} {{ $isFr ? 'détectée(s)' : 'detected' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-light btn-sm" onclick="goWizardStep(0)">
                        <i data-lucide="arrow-left" style="width:14px" class="me-1"></i>{{ $isFr ? 'Recommencer' : 'Restart' }}
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="goWizardStep(2)">
                        {{ $isFr ? 'Mapper les zones' : 'Map zones' }}
                        <i data-lucide="arrow-right" style="width:14px" class="ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ STEP 2: Zone Mapping WITH IMAGE ══ --}}
        <div class="wizard-panel card" id="wp-2" style="display:none">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0">{{ $isFr ? 'Visualiser et mapper les zones' : 'Visualize and map zones' }}</h6>
                    <span id="zones-count-badge" class="badge bg-info" style="font-size:.72rem">0 {{ $isFr ? 'zones' : 'zones' }}</span>
                </div>
            </div>
            <div class="card-body">
                {{-- Main bulletin image with zone overlays --}}
                <div class="canvas-wrapper" id="canvas-wrapper" style="position:relative; display:inline-block; max-width:100%; margin-bottom:1.5rem; border:2px solid var(--border); border-radius:var(--radius); overflow:hidden; background:#f8f9fa;">
                    <div id="bulletin-preview-loading" style="position:absolute; top:0; left:0; right:0; bottom:0; display:flex; align-items:center; justify-content:center; background:#f8f9fa; z-index:10; min-height:400px;">
                        <div style="text-align:center; color:var(--text-muted);">
                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status">
                                <span class="visually-hidden">{{ $isFr ? 'Chargement...' : 'Loading...' }}</span>
                            </div>
                            <div style="font-size:0.85rem">{{ $isFr ? 'Chargement du bulletin...' : 'Loading bulletin...' }}</div>
                        </div>
                    </div>
                    <img id="bulletin-preview-img" src="" alt="{{ $isFr ? 'Bulletin' : 'Bulletin' }}" style="display:block; width:100%; max-width:100%; height:auto;" onload="document.getElementById('bulletin-preview-loading').style.display='none'; displayDetectedZones();">
                    <div id="zones-overlay" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none;"></div>
                </div>

                {{-- Navigation buttons --}}
                <div class="d-flex gap-2">
                    <button class="btn btn-light btn-sm" onclick="goWizardStep(1)">
                        <i data-lucide="arrow-left" style="width:14px" class="me-1"></i>{{ $isFr ? 'Retour' : 'Back' }}
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="goWizardStep(3)">
                        {{ $isFr ? 'Aperçu WYSIWYG' : 'WYSIWYG Preview' }}
                        <i data-lucide="arrow-right" style="width:14px" class="ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ STEP 3: WYSIWYG Preview WITH ORIGINAL IMAGE ══ --}}
        <div class="wizard-panel card" id="wp-3" style="display:none">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">{{ $isFr ? 'Aperçu — Comparez avec le bulletin original' : 'Preview — Compare with original bulletin' }}</h6>
                <span class="badge bg-primary" style="font-size:.72rem">{{ $isFr ? 'Simulé: 1 élève' : 'Simulated: 1 student' }}</span>
            </div>
            <div class="card-body">
                {{-- Two-column layout: Original image + WYSIWYG table --}}
                <div class="row gy-3">
                    {{-- Left: Original bulletin --}}
                    <div class="col-lg-6">
                        <div style="border: 2px solid var(--border); border-radius: var(--radius); overflow: hidden; background: #f8f9fa;">
                            <div style="background: #e9ecef; padding: 0.5rem; text-align: center; font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">
                                {{ $isFr ? 'Bulletin Original (Scané)' : 'Original Bulletin (Scanned)' }}
                            </div>
                            <div id="preview-comparison-container" style="position:relative; display:flex; align-items:center; justify-content:center; min-height:400px; background:#fff;">
                                <img id="preview-comparison-img" src="" alt="{{ $isFr ? 'Bulletin original' : 'Original bulletin' }}" 
                                     style="display:none; width:100%; height:auto; max-height:600px; object-fit:contain; background:#fff;" onload="this.style.display='block';">
                                <div id="preview-comparison-loading" style="text-align:center; color:var(--text-muted);">
                                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status">
                                        <span class="visually-hidden">{{ $isFr ? 'Chargement...' : 'Loading...' }}</span>
                                    </div>
                                    <div style="font-size:0.85rem">{{ $isFr ? 'Affichage du bulletin...' : 'Displaying bulletin...' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right: WYSIWYG table --}}
                    <div class="col-lg-6">
                        <div style="border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem; background: var(--surface-2); overflow-y:auto; max-height:600px;">
                            <div style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-muted); text-transform: uppercase;">
                                {{ $isFr ? 'Données extraites' : 'Extracted Data' }}
                            </div>
                            <div class="table-responsive">
                                <table class="bulletin-preview-table" style="font-size:0.8rem;">
                                    <thead>
                                        <tr>
                                            <th>{{ $isFr ? 'Matière' : 'Subject' }}</th>
                                            <th style="text-align:center">Coef.</th>
                                            <th style="text-align:center">{{ $isFr ? 'Note' : 'Grade' }}</th>
                                            <th style="text-align:center">{{ $isFr ? 'Moy.' : 'Avg.' }}</th>
                                            <th>{{ $isFr ? 'Appréciation' : 'Remark' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="wysiwyg-tbody">
                                        {{-- Generated dynamically from OCR result --}}
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:#f8fafc">
                                            <td colspan="3" style="font-weight:800">{{ $isFr ? 'MOYENNE GÉNÉRALE' : 'GENERAL AVERAGE' }}</td>
                                            <td style="text-align:center;font-weight:900;color:var(--primary)" id="moy-gen-display">—</td>
                                            <td id="appr-gen-display" style="font-weight:700">—</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" style="font-size:.75rem">
                                                {{ $isFr ? 'Rang:' : 'Rank:' }}
                                                <strong id="rang-display">—</strong>
                                                /
                                                <input type="number" id="class-size" class="form-control form-control-sm d-inline-block" value="30" style="width:60px" oninput="recalculate()">
                                                {{ $isFr ? 'élèves' : 'students' }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Navigation --}}
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-light btn-sm" onclick="goWizardStep(2)">
                        <i data-lucide="arrow-left" style="width:14px" class="me-1"></i>{{ $isFr ? 'Retour' : 'Back' }}
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="goWizardStep(4)">
                        {{ $isFr ? 'Étape suivante' : 'Next step' }} →
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ STEP 4-6: Data, Calculate, Export ══ --}}
        @foreach([4,5,6] as $step)
        <div class="wizard-panel card" id="wp-{{ $step }}" style="display:none">
            <div class="card-body text-center py-5">
                <i data-lucide="{{ ['database','calculator','download'][$step-4] }}" style="width:48px;color:var(--primary);display:block;margin:0 auto 1rem"></i>
                <h5 class="fw-bold">
                    @if($step === 4) {{ $isFr ? 'Saisir les données élèves' : 'Enter student data' }}
                    @elseif($step === 5) {{ $isFr ? 'Calcul automatique moyennes' : 'Auto-calculate averages' }}
                    @else {{ $isFr ? 'Export & Impression' : 'Export & Print' }}
                    @endif
                </h5>
                <p class="text-muted" style="font-size:.88rem">{{ $isFr ? 'Étape en cours de configuration...' : 'Step in configuration...' }}</p>
                @if($step < 6)
                <button class="btn btn-primary" onclick="goWizardStep({{ $step + 1 }})">
                    {{ $isFr ? 'Étape suivante' : 'Next step' }} →
                </button>
                @else
                <a href="{{ route('teacher.bulletin.grid', $classId ?? 1) }}" class="btn btn-primary">
                    <i data-lucide="check" style="width:14px" class="me-1"></i>
                    {{ $isFr ? 'Terminer et saisir les notes' : 'Finish and enter grades' }}
                </a>
                @endif
            </div>
        </div>
        @endforeach

    </div>

    {{-- ─── Right sidebar: Zone list ────────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-lucide="map-pin" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Zones à identifier' : 'Zones to identify' }}
                </h6>
            </div>
            <div class="card-body">
                @foreach($zones as $zone)
                <div class="zone-item" id="zi-{{ $zone['id'] }}" onclick="selectZone('{{ $zone['id'] }}')">
                    <div class="zone-dot" style="background:{{ $zone['color'] }}"></div>
                    <div class="flex-grow-1">
                        <div style="font-size:.83rem;font-weight:600">{{ $isFr ? $zone['label_fr'] : $zone['label_en'] }}</div>
                    </div>
                    <span id="zi-status-{{ $zone['id'] }}" style="font-size:.72rem;color:var(--text-muted)">
                        {{ $isFr ? 'À délimiter' : 'To map' }}
                    </span>
                </div>
                @endforeach
                <div class="mt-3">
                    <button class="btn btn-primary w-100" id="btn-save-zones" disabled onclick="saveZones()">
                        <i data-lucide="save" style="width:14px" class="me-1"></i>
                        {{ $isFr ? 'Sauvegarder le modèle' : 'Save template' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
'use strict';

const CSRF  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const isFr  = {{ app()->getLocale() === 'fr' ? 'true' : 'false' }};
let ocrData = null;
let zones   = {};
let currentZone = null;
let isDragging  = false;
let startX, startY;

// ─── Wizard navigation ────────────────────────────────────────────────────────
window.goWizardStep = function(step) {
    document.querySelectorAll('.wizard-panel').forEach(p => p.style.display = 'none');
    document.getElementById(`wp-${step}`)?.style.setProperty('display', '');
    document.querySelectorAll('.w-step').forEach((s, i) => {
        s.classList.remove('active', 'done');
        if (i < step)  s.classList.add('done');
        if (i === step) s.classList.add('active');
    });
};

// ─── File upload ──────────────────────────────────────────────────────────────
window.handleOcrDrop = function(e) {
    e.preventDefault();
    document.getElementById('ocr-drop-zone').classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) handleOcrFile(file);
};

window.handleOcrFile = async function(file) {
    if (!file) return;
    document.getElementById('upload-progress').style.display = '';
    simulateProgress();

    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', CSRF);

    try {
        console.log('🔄 Sending OCR request to /teacher/bulletin/ocr/upload');
        const res = await fetch('/teacher/bulletin/ocr/upload', { 
            method: 'POST', 
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('📡 Response status:', res.status, res.statusText);
        
        // Vérifier le statut HTTP
        if (!res.ok) {
            const contentType = res.headers.get('content-type');
            let errorMsg = `HTTP ${res.status}: ${res.statusText}`;
            
            // Essayer de lire le corps pour plus de détails
            const text = await res.text();
            console.error('❌ Server error response:', text);
            
            if (contentType && contentType.includes('application/json')) {
                try {
                    const errorData = JSON.parse(text);
                    errorMsg = errorData.message || errorMsg;
                } catch (e) {
                    // Pas du JSON valide
                }
            } else {
                // Montrer les premières 200 caractères de la réponse
                if (text.length > 0) {
                    errorMsg = errorMsg + '\n' + text.substring(0, 200);
                }
            }
            
            throw new Error(errorMsg);
        }
        
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Invalid response type: expected JSON, got ' + (contentType || 'unknown'));
        }
        
        const data = await res.json();
        console.log('✅ OCR Response received:', data);

        document.getElementById('ocr-progress-bar').style.width = '100%';

        if (data.success) {
            ocrData = data;
            
            // ✅ STEP 1 DISPLAY: IMAGE + TEXT
            // Display the original bulletin image with robust loading
            if (data.preview_url) {
                // Step 1: Display detected image
                console.log('📸 Loading preview image from:', data.preview_url);
                loadImageIntoElement('detected-image', 'detected-image-loading', data.preview_url);
                
                // Step 2: Display image for zone mapping
                loadImageIntoElement('bulletin-preview-img', 'bulletin-preview-loading', data.preview_url);
                
                // Step 3: Display image for WYSIWYG comparison
                loadImageIntoElement('preview-comparison-img', 'preview-comparison-loading', data.preview_url);
            } else {
                console.warn('⚠️ No preview_url in OCR response');
                setLoadingError('detected-image-loading', isFr ? '❌ Image non disponible' : '❌ Image not available');
            }
            
            // Display the extracted text
            document.getElementById('detected-text').textContent = data.raw_text ?? (isFr ? 'Texte extrait avec succès.' : 'Text extracted successfully.');
            
            // Display confidence and metadata
            if (data.confidence !== undefined) {
                document.getElementById('ocr-confidence-badge').style.display = 'inline-block';
                document.getElementById('confidence-value').textContent = Math.round(data.confidence);
            }
            
            // Show OCR metadata panel
            document.getElementById('ocr-metadata').style.display = 'block';
            document.getElementById('ocr-method').textContent = data.method ? data.method.toUpperCase() : 'OCR.SPACE';
            
            if (data.is_fallback) {
                document.getElementById('ocr-fallback-badge').style.display = 'inline-block';
            } else {
                document.getElementById('ocr-fallback-badge').style.display = 'none';
            }
            
            const subjectsCount = data.structure?.subjects ? Object.keys(data.structure.subjects).length : 0;
            const tablesCount = data.tables_detected || 0;
            document.getElementById('subjects-count').textContent = subjectsCount;
            document.getElementById('tables-count').textContent = tablesCount;
            
            // Populate WYSIWYG preview table with detected structures
            populateWysiwygTable(data.structure);
            
            console.log('✅ OCR processing complete, moving to step 1');
            goWizardStep(1);
        } else {
            const errMsg = data.message ?? (isFr ? 'Erreur OCR.' : 'OCR error.');
            console.error('❌ OCR Error:', data);
            alert('⚠️ ' + errMsg);
        }
    } catch (err) {
        console.error('❌ Upload error:', err.message || err);
        console.error('Stack:', err.stack);
        alert((isFr ? 'Erreur: ' : 'Error: ') + (err.message || (isFr ? 'Connexion ou réponse invalide.' : 'Connection or invalid response.')));
    } finally {
        document.getElementById('upload-progress').style.display = 'none';
    }
};

// ─── Helper function to load image robustly ────────────────────────────────────
function loadImageIntoElement(imgId, loadingId, imageUrl) {
    const imgElement = document.getElementById(imgId);
    const loadingElement = document.getElementById(loadingId);
    
    if (!imgElement) return;
    
    // Timeout to ensure loading state doesn't stay forever
    const timeout = setTimeout(() => {
        if (loadingElement && loadingElement.parentElement) {
            setLoadingError(loadingId, isFr ? '⏱️ Délai dépassé' : '⏱️ Timeout');
        }
    }, 10000);
    
    imgElement.onerror = function() {
        clearTimeout(timeout);
        console.error('Failed to load image:', imageUrl);
        if (loadingElement && loadingElement.parentElement) {
            setLoadingError(loadingId, isFr ? '❌ Erreur chargement image' : '❌ Failed to load image');
        }
    };
    
    imgElement.onload = function() {
        clearTimeout(timeout);
        imgElement.style.display = 'block';
        if (loadingElement && loadingElement.parentElement) {
            loadingElement.style.display = 'none';
        }
    };
    
    imgElement.src = imageUrl;
    
    // For cached images, onload might fire before we set the handler
    if (imgElement.complete) {
        imgElement.onload();
    }
}

function setLoadingError(loadingId, message) {
    const loadingElement = document.getElementById(loadingId);
    if (loadingElement) {
        loadingElement.innerHTML = '<div style="color:var(--text-muted); font-size: 0.85rem;">' + message + '</div>';
    }
}

// ─── Populate WYSIWYG table with OCR structure ─────────────────────────────────
function populateWysiwygTable(structure) {
    const tbody = document.getElementById('wysiwyg-tbody');
    if (!tbody || !structure || !structure.subjects) return;
    
    tbody.innerHTML = '';
    
    const subjects = structure.subjects || {};
    Object.entries(subjects).forEach(([subjectName, subjectData]) => {
        const tr = document.createElement('tr');
        
        const coef = subjectData.coefficient || 1;
        const note = subjectData.grade || '0';
        const moy = subjectData.average || '—';
        const appr = subjectData.appreciation || '—';
        
        tr.innerHTML = `
            <td style="font-weight:600">${escapeHtml(subjectName)}</td>
            <td style="text-align:center"><input type="number" class="form-control form-control-sm" value="${coef}" style="width:60px; text-align:center;" step="0.5" min="0"></td>
            <td style="text-align:center"><input type="number" class="wysiwyg-note form-control form-control-sm" value="${note}" data-coef="${coef}" oninput="recalculate()" style="width:70px;" step="0.5" min="0" max="20"></td>
            <td style="text-align:center"><span class="subject-avg" data-subject="${escapeHtml(subjectName)}">${moy}</span></td>
            <td><input type="text" class="form-control form-control-sm" value="${appr}" placeholder="${isFr ? 'Appréciation' : 'Remark'}" style="font-size:0.75rem;"></td>
        `;
        
        tbody.appendChild(tr);
    });
    
    // Set general average from structure if available
    if (structure.general_average) {
        document.getElementById('moy-gen-display').textContent = parseFloat(structure.general_average).toFixed(2);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function simulateProgress() {
    let pct = 0;
    const interval = setInterval(() => {
        pct = Math.min(pct + Math.random() * 15, 90);
        document.getElementById('ocr-progress-bar').style.width = pct + '%';
        if (pct >= 90) clearInterval(interval);
    }, 300);
}

// ─── Display detected zones as overlays ────────────────────────────────────────
function displayDetectedZones() {
    const overlay = document.getElementById('zones-overlay');
    if (!overlay) return;
    
    overlay.innerHTML = '';
    
    if (!ocrData || !ocrData.ocr_zones || ocrData.ocr_zones.length === 0) {
        console.log('No OCR zones detected');
        document.getElementById('zones-count-badge').textContent = '0 ' + (isFr ? 'zones' : 'zones');
        return;
    }
    
    const img = document.getElementById('bulletin-preview-img');
    const wrapper = document.getElementById('canvas-wrapper');
    
    if (!img || !wrapper) return;
    
    // Wait for image to be fully loaded
    if (!img.complete && img.naturalHeight === 0) {
        setTimeout(displayDetectedZones, 200);
        return;
    }
    
    const imgRect = img.getBoundingClientRect();
    const wrapperRect = wrapper.getBoundingClientRect();
    
    // Calculate scale factors
    const scaleX = img.offsetWidth / img.naturalWidth || 1;
    const scaleY = img.offsetHeight / img.naturalHeight || 1;
    
    // Create zone overlays based on detected zones
    ocrData.ocr_zones.forEach((zone, idx) => {
        const zoneEl = document.createElement('div');
        zoneEl.className = 'zone-overlay';
        zoneEl.dataset.zoneIdx = idx;
        
        const x = (zone.x || 0) * scaleX;
        const y = (zone.y || 0) * scaleY;
        const w = (zone.width || 100) * scaleX;
        const h = (zone.height || 100) * scaleY;
        
        zoneEl.style.left = x + 'px';
        zoneEl.style.top = y + 'px';
        zoneEl.style.width = w + 'px';
        zoneEl.style.height = h + 'px';
        
        // Add label
        const label = document.createElement('div');
        label.className = 'zone-label';
        label.textContent = zone.label || `Zone ${idx + 1}`;
        zoneEl.appendChild(label);
        
        overlay.appendChild(zoneEl);
    });
    
    document.getElementById('zones-count-badge').textContent = ocrData.ocr_zones.length + ' ' + (isFr ? 'zone(s) détectée(s)' : 'zone(s) detected');
}

// Override goWizardStep to show zones when going to step 2 and image in step 3
const originalGoWizardStep = window.goWizardStep;
window.goWizardStep = function(step) {
    originalGoWizardStep(step);
    
    // Display zones when entering step 2
    if (step === 2 && ocrData) {
        const bulletinImg = document.getElementById('bulletin-preview-img');
        if (bulletinImg && bulletinImg.complete) {
            displayDetectedZones();
        } else if (bulletinImg) {
            bulletinImg.onload = function() {
                setTimeout(displayDetectedZones, 100);
            };
        } else {
            setTimeout(displayDetectedZones, 200);
        }
    }
    
    // Display comparison image when entering step 3
    if (step === 3 && ocrData && ocrData.preview_url) {
        setTimeout(() => {
            loadImageIntoElement('preview-comparison-img', 'preview-comparison-loading', ocrData.preview_url);
        }, 100);
    }
};

// ─── Zone mapping ─────────────────────────────────────────────────────────────
window.selectZone = function(zoneId) {
    document.querySelectorAll('.zone-item').forEach(z => z.classList.remove('active'));
    document.getElementById(`zi-${zoneId}`)?.classList.add('active');
    currentZone = zoneId;
};

// ─── WYSIWYG recalculate ──────────────────────────────────────────────────────
window.recalculate = function() {
    let totalPts = 0, totalCoef = 0;
    
    const noteInputs = document.querySelectorAll('.wysiwyg-note');
    
    noteInputs.forEach(inp => {
        const noteVal = parseFloat(inp.value);
        const coef = parseFloat(inp.dataset.coef ?? 1);
        
        if (!isNaN(noteVal) && !isNaN(coef) && coef > 0) { 
            totalPts += noteVal * coef; 
            totalCoef += coef; 
        }
    });
    
    const avg = totalCoef > 0 ? (totalPts / totalCoef).toFixed(2) : '—';
    document.getElementById('moy-gen-display').textContent = avg;
};

// ─── Save zones ───────────────────────────────────────────────────────────────
window.saveZones = async function() {
    const res  = await fetch('/teacher/bulletin/ocr/save-structure', {
        method: 'POST',
        headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json' },
        body: JSON.stringify({ zones, class_id: {{ $classId ?? 0 }} }),
    });
    const data = await res.json();
    if (data.success) goWizardStep(3);
};

})();
</script>
@endpush
