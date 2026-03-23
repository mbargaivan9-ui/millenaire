@extends('layouts.app')
@section('title', 'Système de Bulletins NG')
@section('content')
<div class="bng-page">
    <div class="bng-page-header">
        <div class="bng-page-header-inner">
            <div class="bng-page-icon">📋</div>
            <div>
                <h1 class="bng-page-title">Système de Bulletins</h1>
                <p class="bng-page-subtitle">Le Millenaire — Nouvelle Génération</p>
            </div>
        </div>
        <a href="{{ route('teacher.bulletin_ng.step1') }}" class="bng-btn bng-btn-primary">
            ➕ Nouvelle Session
        </a>
    </div>

    @if(session('success'))
        <div class="bng-alert bng-alert-success" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:10px;padding:12px 16px;margin-bottom:16px;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if($configs->isEmpty())
        <div class="bng-card">
            <div class="bng-card-body">
                <div class="bng-empty-state">
                    <div class="bng-empty-icon">📋</div>
                    <div class="bng-empty-text">Aucune session de bulletin. Créez votre première session.</div>
                    <div style="margin-top:20px;">
                        <a href="{{ route('teacher.bulletin_ng.step1') }}" class="bng-btn bng-btn-primary">
                            ➕ Créer une Session
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bng-sessions-grid">
            @foreach($configs as $cfg)
            @php
                $statusColors = [
                    'configuration'   => 'bng-badge-warning',
                    'saisie_ouverte'  => 'bng-badge-info',
                    'saisie_fermee'   => 'bng-badge-danger',
                    'conduite'        => 'bng-badge-warning',
                    'genere'          => 'bng-badge-success',
                ];
                $statusLabels = [
                    'configuration'   => 'Configuration',
                    'saisie_ouverte'  => 'Saisie ouverte',
                    'saisie_fermee'   => 'Notes verrouillées',
                    'conduite'        => 'Conduite en cours',
                    'genere'          => 'Bulletins générés',
                ];
                $stepRoutes = [
                    'configuration'   => route('teacher.bulletin_ng.step2').'?config_id='.$cfg->id.'&langue='.$cfg->langue,
                    'saisie_ouverte'  => route('teacher.bulletin_ng.step5', $cfg->id),
                    'saisie_fermee'   => route('teacher.bulletin_ng.step6', $cfg->id),
                    'conduite'        => route('teacher.bulletin_ng.step6', $cfg->id),
                    'genere'          => route('teacher.bulletin_ng.step7', $cfg->id),
                ];
            @endphp
            <div class="bng-session-card">
                <div class="bng-session-card-header">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div style="color:#fff;font-weight:800;font-size:16px;">{{ $cfg->nom_classe }}</div>
                            <div style="color:rgba(255,255,255,.75);font-size:12px;margin-top:3px;">
                                {{ $cfg->trimestre_label }} — {{ $cfg->annee_academique }}
                            </div>
                        </div>
                        <span class="bng-badge" style="background:rgba(255,255,255,.2);color:#fff;">
                            {{ $cfg->langue === 'FR' ? '🇫🇷 FR' : '🇬🇧 EN' }}
                        </span>
                    </div>
                </div>
                <div class="bng-session-card-body">
                    <span class="bng-badge {{ $statusColors[$cfg->statut] ?? 'bng-badge-primary' }}">
                        {{ $statusLabels[$cfg->statut] ?? $cfg->statut }}
                    </span>
                    <div class="bng-session-meta">
                        <span>👨‍🎓 {{ $cfg->students_count ?? $cfg->students->count() }} élève(s)</span>
                        <span>📚 {{ $cfg->subjects_count ?? $cfg->subjects->count() }} matière(s)</span>
                        <span>👤 {{ $cfg->profPrincipal->name ?? auth()->user()->name }}</span>
                        <span>📅 Créée le {{ $cfg->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="bng-session-actions">
                        <a href="{{ $stepRoutes[$cfg->statut] ?? route('teacher.bulletin_ng.step2').'?config_id='.$cfg->id.'&langue='.$cfg->langue }}"
                           class="bng-btn bng-btn-primary" style="flex:1;justify-content:center;">
                            ➡ Continuer
                        </a>
                        @if($cfg->statut === 'genere')
                        <a href="{{ route('teacher.bulletin_ng.step7', $cfg->id) }}"
                           class="bng-btn bng-btn-success" style="flex:1;justify-content:center;">
                            🎓 Bulletins
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
