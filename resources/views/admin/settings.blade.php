{{--
    |--------------------------------------------------------------------------
    | admin/settings.blade.php — Paramètres de l'Établissement
    |--------------------------------------------------------------------------
    | Phase 2 — Configuration complète: couleurs, logo, année scolaire,
    | frais, signatures, intégrations
    --}}

@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Paramètres' : 'Settings')

@push('styles')
<style>
.settings-nav { display:flex; flex-direction:column; gap:.25rem; }
.settings-nav-item {
    padding:.55rem .85rem; border-radius:8px; cursor:pointer;
    font-size:.845rem; font-weight:500; color:var(--text-secondary);
    display:flex; align-items:center; gap:.65rem; transition:all .15s ease;
    border:none; background:none; text-align:left; width:100%;
}
.settings-nav-item:hover  { background:var(--primary-hover); color:var(--primary); }
.settings-nav-item.active { background:var(--primary-bg); color:var(--primary); font-weight:700; }
.settings-nav-item [data-lucide] { width:16px; height:16px; flex-shrink:0; }

.settings-panel { display:none; }
.settings-panel.active { display:block; }

.color-preview {
    width:36px; height:36px; border-radius:8px;
    border:2px solid var(--border); cursor:pointer;
    display:inline-block;
}
</style>
@endpush

@section('content')
@php $isFr = app()->getLocale() === 'fr'; $s = $settings; @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#64748b,#475569)"><i data-lucide="settings"></i></div>
        <div>
            <h1 class="page-title">{{ $isFr ? 'Paramètres de l\'Établissement' : 'Establishment Settings' }}</h1>
            <p class="page-subtitle text-muted">{{ $isFr ? 'Configuration générale de la plateforme' : 'General platform configuration' }}</p>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">✅ {{ session('success') }}</div>
@endif

<div class="row gy-4">

    {{-- ─── Left nav ─────────────────────────────────────────────────────── --}}
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body p-2">
                <nav class="settings-nav">
                    <button class="settings-nav-item active" onclick="switchPanel('general', this)">
                        <i data-lucide="building-2"></i>{{ $isFr ? 'Général' : 'General' }}
                    </button>
                    <button class="settings-nav-item" onclick="switchPanel('branding', this)">
                        <i data-lucide="palette"></i>{{ $isFr ? 'Apparence' : 'Branding' }}
                    </button>
                    <button class="settings-nav-item" onclick="switchPanel('academic', this)">
                        <i data-lucide="calendar"></i>{{ $isFr ? 'Année Scolaire' : 'Academic Year' }}
                    </button>
                    <button class="settings-nav-item" onclick="switchPanel('fees', this)">
                        <i data-lucide="banknote"></i>{{ $isFr ? 'Frais & Paiements' : 'Fees & Payments' }}
                    </button>
                    <button class="settings-nav-item" onclick="switchPanel('grading', this)">
                        <i data-lucide="bar-chart-2"></i>{{ $isFr ? 'Barème de Notes' : 'Grade Scale' }}
                    </button>
                    <button class="settings-nav-item" onclick="switchPanel('signature', this)">
                        <i data-lucide="pen-tool"></i>{{ $isFr ? 'Signature & Cachet' : 'Signature & Seal' }}
                    </button>
                    <button class="settings-nav-item" onclick="switchPanel('integrations', this)">
                        <i data-lucide="plug"></i>Intégrations
                    </button>
                    <button class="settings-nav-item" onclick="switchPanel('testimonials', this)">
                        <i data-lucide="star"></i>{{ $isFr ? 'Témoignages' : 'Testimonials' }}
                    </button>
                </nav>
            </div>
        </div>
    </div>

    {{-- ─── Right content ────────────────────────────────────────────────── --}}
    <div class="col-lg-9">

        {{-- ── GENERAL ─────────────────────────────────────────────────────── --}}
        <div class="settings-panel active" id="panel-general">
            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="building-2" style="width:16px" class="me-2"></i>{{ $isFr ? 'Informations générales' : 'General information' }}</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <input type="hidden" name="section" value="general">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Nom de la plateforme' : 'Platform name' }}</label>
                                <input type="text" name="platform_name" class="form-control" value="{{ old('platform_name', $s->platform_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slogan</label>
                                <input type="text" name="slogan" class="form-control" value="{{ old('slogan', $s->slogan) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Email de contact' : 'Contact email' }}</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $s->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Téléphone' : 'Phone' }}</label>
                                <input type="tel" name="phone" class="form-control" value="{{ old('phone', $s->phone) }}" placeholder="+237 6XX XXX XXX">
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ $isFr ? 'Adresse' : 'Address' }}</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $s->address) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Nom du Proviseur' : "Principal's name" }}</label>
                                <input type="text" name="proviseur_name" class="form-control" value="{{ old('proviseur_name', $s->proviseur_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Titre du Proviseur' : "Principal's title" }}</label>
                                <input type="text" name="proviseur_title" class="form-control" value="{{ old('proviseur_title', $s->proviseur_title) }}" placeholder="{{ $isFr ? 'Directeur de l\'Établissement' : 'School Principal' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                @if($s->logo_path)
                                <img src="{{ asset($s->logo_path) }}" style="height:40px;margin-top:.5rem;border-radius:6px">
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Favicon</label>
                                <input type="file" name="favicon" class="form-control" accept="image/*,.ico">
                            </div>
                        </div>
                        <div class="mt-4"><button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:14px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</button></div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── BRANDING ──────────────────────────────────────────────────────── --}}
        <div class="settings-panel" id="panel-branding">
            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="palette" style="width:16px" class="me-2"></i>{{ $isFr ? 'Couleurs & Apparence' : 'Colors & Appearance' }}</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="section" value="branding">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Couleur primaire' : 'Primary color' }}</label>
                                <div class="d-flex gap-3 align-items-center">
                                    <input type="color" name="primary_color" value="{{ old('primary_color', $s->primary_color ?? '#0d9488') }}"
                                           style="width:48px;height:38px;border-radius:8px;border:1.5px solid var(--border);cursor:pointer;padding:2px">
                                    <input type="text" name="primary_color_hex" value="{{ old('primary_color', $s->primary_color ?? '#0d9488') }}"
                                           class="form-control" style="width:130px;font-family:monospace" placeholder="#0d9488">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Couleur secondaire' : 'Secondary color' }}</label>
                                <div class="d-flex gap-3 align-items-center">
                                    <input type="color" name="secondary_color" value="{{ old('secondary_color', $s->secondary_color ?? '#0f766e') }}"
                                           style="width:48px;height:38px;border-radius:8px;border:1.5px solid var(--border);cursor:pointer;padding:2px">
                                    <input type="text" value="{{ old('secondary_color', $s->secondary_color ?? '#0f766e') }}"
                                           class="form-control" style="width:130px;font-family:monospace">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ $isFr ? 'Aperçu' : 'Preview' }}</label>
                                <div id="color-preview" style="padding:1rem;border-radius:12px;background:linear-gradient(135deg, {{ $s->primary_color ?? '#0d9488' }}, {{ $s->secondary_color ?? '#0f766e' }});color:#fff;font-weight:700">
                                    {{ $isFr ? 'Aperçu de la couleur primaire' : 'Primary color preview' }} — {{ $s->platform_name ?? 'Millénaire Connect' }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-4"><button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:14px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</button></div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── ACADEMIC ──────────────────────────────────────────────────────── --}}
        <div class="settings-panel" id="panel-academic">
            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="calendar" style="width:16px" class="me-2"></i>{{ $isFr ? 'Année Scolaire' : 'Academic Year' }}</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="section" value="academic">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Année scolaire courante' : 'Current academic year' }}</label>
                                <input type="text" name="current_academic_year" class="form-control" value="{{ old('current_academic_year', $s->current_academic_year ?? date('Y').'/'.date('Y',strtotime('+1 year'))) }}" placeholder="2025/2026">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Trimestre actif' : 'Active term' }}</label>
                                <select name="current_term" class="form-select">
                                    @foreach([1,2,3] as $t)
                                    <option value="{{ $t }}" {{ ($s->current_term ?? 1) == $t ? 'selected' : '' }}>{{ $isFr ? "Trimestre $t" : "Term $t" }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Séquence active' : 'Active sequence' }}</label>
                                <select name="current_sequence" class="form-select">
                                    @foreach([1,2,3,4,5,6] as $seq)
                                    <option value="{{ $seq }}" {{ ($s->current_sequence ?? 1) == $seq ? 'selected' : '' }}>{{ $isFr ? "Séquence $seq" : "Sequence $seq" }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Note de passage' : 'Passing grade' }}</label>
                                <input type="number" name="passing_grade" class="form-control" value="{{ old('passing_grade', $s->passing_grade ?? 10) }}" min="0" max="20" step="0.5">
                            </div>
                        </div>
                        <div class="mt-4"><button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:14px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</button></div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── FEES ──────────────────────────────────────────────────────────── --}}
        <div class="settings-panel" id="panel-fees">
            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="banknote" style="width:16px" class="me-2"></i>{{ $isFr ? 'Frais de Scolarité' : 'School Fees' }}</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="section" value="fees">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Frais annuels (XAF)' : 'Annual fees (XAF)' }}</label>
                                <input type="number" name="annual_fees" class="form-control" value="{{ old('annual_fees', $s->annual_fees ?? 0) }}" min="0" step="500">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Acompte minimum (XAF)' : 'Minimum deposit (XAF)' }}</label>
                                <input type="number" name="minimum_deposit" class="form-control" value="{{ old('minimum_deposit', $s->minimum_deposit ?? 0) }}" min="0" step="500">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Orange Money {{ $isFr ? 'activé' : 'enabled' }}</label>
                                <select name="orange_money_enabled" class="form-select">
                                    <option value="1" {{ ($s->orange_money_enabled ?? true) ? 'selected' : '' }}>{{ $isFr ? 'Oui' : 'Yes' }}</option>
                                    <option value="0" {{ !($s->orange_money_enabled ?? true) ? 'selected' : '' }}>{{ $isFr ? 'Non' : 'No' }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">MTN MoMo {{ $isFr ? 'activé' : 'enabled' }}</label>
                                <select name="mtn_momo_enabled" class="form-select">
                                    <option value="1" {{ ($s->mtn_momo_enabled ?? true) ? 'selected' : '' }}>{{ $isFr ? 'Oui' : 'Yes' }}</option>
                                    <option value="0" {{ !($s->mtn_momo_enabled ?? true) ? 'selected' : '' }}>{{ $isFr ? 'Non' : 'No' }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4"><button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:14px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</button></div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── GRADING ───────────────────────────────────────────────────────── --}}
        <div class="settings-panel" id="panel-grading">
            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="bar-chart-2" style="width:16px" class="me-2"></i>{{ $isFr ? 'Barème d\'Appréciation' : 'Grade Scale' }}</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="section" value="grading">
                        <p class="text-muted mb-3" style="font-size:.83rem">{{ $isFr ? 'Définissez les seuils pour chaque mention.' : 'Define the thresholds for each grade mention.' }}</p>
                        @php
                            $gradeScale = $s->grade_scale ?? [
                                ['min'=>18,'max'=>20,'label_fr'=>'Excellent','label_en'=>'Excellent','color'=>'#8b5cf6'],
                                ['min'=>16,'max'=>17.99,'label_fr'=>'Très Bien','label_en'=>'Very Good','color'=>'#10b981'],
                                ['min'=>13,'max'=>15.99,'label_fr'=>'Bien','label_en'=>'Good','color'=>'#3b82f6'],
                                ['min'=>10,'max'=>12.99,'label_fr'=>'Assez Bien','label_en'=>'Fair','color'=>'#f59e0b'],
                                ['min'=>0,'max'=>9.99,'label_fr'=>'Insuffisant','label_en'=>'Insufficient','color'=>'#ef4444'],
                            ];
                        @endphp
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ $isFr ? 'Min' : 'Min' }}</th>
                                        <th>{{ $isFr ? 'Max' : 'Max' }}</th>
                                        <th>{{ $isFr ? 'Mention (FR)' : 'Grade (FR)' }}</th>
                                        <th>{{ $isFr ? 'Mention (EN)' : 'Grade (EN)' }}</th>
                                        <th>{{ $isFr ? 'Couleur' : 'Color' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gradeScale as $i => $row)
                                    <tr>
                                        <td><input type="number" name="grade_scale[{{ $i }}][min]" class="form-control form-control-sm" value="{{ $row['min'] }}" step="0.01" style="width:70px"></td>
                                        <td><input type="number" name="grade_scale[{{ $i }}][max]" class="form-control form-control-sm" value="{{ $row['max'] }}" step="0.01" style="width:70px"></td>
                                        <td><input type="text" name="grade_scale[{{ $i }}][label_fr]" class="form-control form-control-sm" value="{{ $row['label_fr'] }}"></td>
                                        <td><input type="text" name="grade_scale[{{ $i }}][label_en]" class="form-control form-control-sm" value="{{ $row['label_en'] }}"></td>
                                        <td>
                                            <input type="color" name="grade_scale[{{ $i }}][color]" value="{{ $row['color'] }}"
                                                   style="width:36px;height:32px;border-radius:6px;border:1.5px solid var(--border);cursor:pointer">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4"><button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:14px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</button></div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── SIGNATURE ─────────────────────────────────────────────────────── --}}
        <div class="settings-panel" id="panel-signature">
            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="pen-tool" style="width:16px" class="me-2"></i>{{ $isFr ? 'Signature & Cachet' : 'Signature & Seal' }}</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <input type="hidden" name="section" value="signature">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Image de signature' : 'Signature image' }}</label>
                                <input type="file" name="signature_image" class="form-control" accept="image/*">
                                @if($s->signature_image)
                                <img src="{{ asset($s->signature_image) }}" style="max-height:60px;margin-top:.5rem;border-radius:4px">
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $isFr ? 'Image du cachet' : 'Seal image' }}</label>
                                <input type="file" name="seal_image" class="form-control" accept="image/*">
                                @if($s->seal_image)
                                <img src="{{ asset($s->seal_image) }}" style="max-height:60px;margin-top:.5rem;border-radius:4px">
                                @endif
                            </div>
                        </div>
                        <div class="mt-4"><button type="submit" class="btn btn-primary"><i data-lucide="save" style="width:14px" class="me-1"></i>{{ $isFr ? 'Enregistrer' : 'Save' }}</button></div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── INTEGRATIONS ──────────────────────────────────────────────────── --}}
        <div class="settings-panel" id="panel-integrations">
            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="plug" style="width:16px" class="me-2"></i>Intégrations API</h6></div>
                <div class="card-body">
                    <div class="alert alert-info"><i data-lucide="info" style="width:16px" class="me-2"></i>{{ $isFr ? 'Ces clés sont configurées dans le fichier .env pour des raisons de sécurité.' : 'These keys are configured in the .env file for security reasons.' }}</div>
                    <div class="row gy-3">
                        @foreach(['ORANGE_MONEY_MERCHANT_KEY' => 'Orange Money Merchant Key', 'MTN_MOMO_SUBSCRIPTION_KEY' => 'MTN MoMo Subscription Key', 'REVERB_APP_KEY' => 'Laravel Reverb Key', 'MAIL_HOST' => 'Mail Server'] as $key => $label)
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:.8rem">{{ $label }}</label>
                            <input type="text" class="form-control form-control-sm"
                                   value="{{ env($key) ? str_repeat('•', min(strlen(env($key)), 12)) . substr(env($key), -4) : '—' }}"
                                   disabled style="font-family:monospace;color:var(--text-muted)">
                        </div>
                        @endforeach
                    </div>
                    <p class="text-muted mt-3" style="font-size:.8rem">{{ $isFr ? 'Pour modifier ces valeurs, éditez le fichier .env et redémarrez le serveur.' : 'To change these values, edit the .env file and restart the server.' }}</p>
                </div>
            </div>
        </div>

        {{-- ── TESTIMONIALS ──────────────────────────────────────────────────── --}}
        <div class="settings-panel" id="panel-testimonials">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i data-lucide="star" style="width:16px" class="me-2"></i>{{ $isFr ? 'Témoignages' : 'Testimonials' }}</h6>
                    <button class="btn btn-primary btn-sm" onclick="addTestimonialForm()">
                        <i data-lucide="plus" style="width:13px" class="me-1"></i>{{ $isFr ? 'Ajouter' : 'Add' }}
                    </button>
                </div>
                <div class="card-body">
                    @forelse($testimonials ?? [] as $t)
                    <div class="d-flex align-items-start gap-3 mb-3 p-3 rounded-3" style="border:1px solid var(--border)">
                        <div style="flex:1">
                            <div class="fw-bold" style="font-size:.85rem">{{ $t->author_name }}</div>
                            <div style="font-size:.76rem;color:var(--text-muted)">{{ $t->author_role }}</div>
                            <div style="font-size:.8rem;margin-top:.25rem">"{{ Str::limit($t->content_fr, 80) }}"</div>
                        </div>
                        <form method="POST" action="{{ route('admin.testimonials.destroy', $t->id) }}" onsubmit="return confirm('{{ $isFr ? 'Supprimer ?' : 'Delete?' }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger"><i data-lucide="trash-2" style="width:12px"></i></button>
                        </form>
                    </div>
                    @empty
                    <p class="text-muted" style="font-size:.83rem">{{ $isFr ? 'Aucun témoignage.' : 'No testimonials.' }}</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>{{-- /col-lg-9 --}}
</div>

@endsection

@push('scripts')
<script>
window.switchPanel = function(panel, btn) {
    document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + panel)?.classList.add('active');
    btn.classList.add('active');
};

// Check URL hash on load
document.addEventListener('DOMContentLoaded', () => {
    const hash = location.hash.replace('#', '');
    if (hash) {
        const btn = document.querySelector(`[onclick*="'${hash}'"]`);
        if (btn) switchPanel(hash, btn);
    }
});
</script>
@endpush


