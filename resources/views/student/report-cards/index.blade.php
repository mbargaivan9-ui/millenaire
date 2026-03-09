@extends('layouts.app')

@section('title', "Mes Bulletins — Millénaire Connect")

@section('content')
<style>
    /* ─────────────────────────────────────────────────────────────── */
    /* DESIGN SYSTEM - Student Report Cards Module                   */
    /* ─────────────────────────────────────────────────────────────── */

    .report-cards-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8ecef 100%);
        min-height: 100vh;
        padding: 30px 0;
    }

    .report-cards-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        color: white;
        padding: 40px;
        margin-bottom: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
    }

    .report-cards-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .report-cards-header-content {
        position: relative;
        z-index: 1;
    }

    .report-cards-header h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0 0 15px 0;
        letter-spacing: -0.5px;
    }

    .report-cards-header .header-subtitle {
        font-size: 1.1rem;
        opacity: 0.95;
        margin: 0;
    }

    .header-meta {
        display: flex;
        gap: 20px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.2);
        padding: 10px 16px;
        border-radius: 8px;
        backdrop-filter: blur(10px);
        font-weight: 500;
    }

    .meta-item i {
        font-size: 1.2rem;
    }

    /* ─── Statistiques ─────────────────────────────────────────────── */
    .stats-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border-left: 4px solid #2563eb;
    }

    .stat-card.primary {
        border-left-color: #2563eb;
    }

    .stat-card.success {
        border-left-color: #10b981;
    }

    .stat-card.warning {
        border-left-color: #f59e0b;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1e293b;
    }

    .stat-description {
        font-size: 0.85rem;
        color: #9ca3af;
        margin-top: 8px;
    }

    /* ─── Grille de Bulletins ─────────────────────────────────────── */
    .report-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .report-card-item {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .report-card-item:hover {
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
        transform: translateY(-4px);
        border-color: #2563eb;
    }

    .report-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 16px;
    }

    .report-card-item.excellent .report-card-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .report-card-item.good .report-card-header {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .report-card-item.average .report-card-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .report-card-item.poor .report-card-header {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .card-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0 0 5px 0;
    }

    .card-subtitle {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    .report-card-body {
        padding: 16px;
    }

    .card-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.9rem;
    }

    .card-info-row:last-child {
        border-bottom: none;
    }

    .card-info-label {
        color: #6b7280;
        font-weight: 500;
    }

    .card-info-value {
        color: #1e293b;
        font-weight: 600;
    }

    .appreciation-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #f3e8ff;
        color: #7c3aed;
    }

    .report-card-footer {
        padding: 16px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 10px;
    }

    .btn-small {
        flex: 1;
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-view {
        background: #2563eb;
        color: white;
    }

    .btn-view:hover {
        background: #1d4ed8;
    }

    .btn-pdf {
        background: #ef4444;
        color: white;
    }

    .btn-pdf:hover {
        background: #dc2626;
    }

    /* ─── Empty State ────────────────────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .empty-state-icon {
        font-size: 3rem;
        color: #d1d5db;
        margin-bottom: 15px;
    }

    .empty-state-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .empty-state-text {
        color: #6b7280;
        margin-bottom: 20px;
    }

    /* Animations */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .report-card-item {
        animation: slideInUp 0.3s ease forwards;
    }

    .report-card-item:nth-child(2) { animation-delay: 0.05s; }
    .report-card-item:nth-child(3) { animation-delay: 0.1s; }
    .report-card-item:nth-child(4) { animation-delay: 0.15s; }

    /* Responsive */
    @media (max-width: 768px) {
        .report-cards-header {
            padding: 30px;
        }

        .report-cards-header h1 {
            font-size: 1.8rem;
        }

        .header-meta {
            flex-direction: column;
            gap: 10px;
        }

        .report-cards-grid {
            grid-template-columns: 1fr;
        }

        .stats-section {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="report-cards-container">
    <div class="container-lg">
        
        {{-- ═══ HEADER ═══ --}}
        <div class="report-cards-header">
            <div class="report-cards-header-content">
                <h1>
                    <i class="fas fa-file-alt me-2"></i>Mes Bulletins
                </h1>
                <p class="header-subtitle">Consultez vos bulletins de notes par trimestre</p>
                
                <div class="header-meta">
                    <div class="meta-item">
                        <i class="fas fa-user"></i>
                        <span>{{ optional($student)->full_name ?? 'Élève' }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span>{{ optional($student->classe ?? null)->name ?? 'Classe' }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Année {{ date('Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ STATISTIQUES ═══ --}}
        @if(count($reportCards) > 0)
        <div class="stats-section">
            <div class="stat-card primary">
                <div class="stat-label"><i class="fas fa-file-alt me-1"></i>Total Bulletins</div>
                <div class="stat-value">{{ count($reportCards) }}</div>
                <div class="stat-description">Bulletins disponibles</div>
            </div>
            <div class="stat-card success">
                <div class="stat-label"><i class="fas fa-star me-1"></i>Meilleure Moyenne</div>
                <div class="stat-value">{{ number_format(max(array_map(fn($r) => $r->average ?? 0, $reportCards)), 2) }}</div>
                <div class="stat-description">Votre meilleure performance</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label"><i class="fas fa-chart-bar me-1"></i>Moyenne Actuelle</div>
                <div class="stat-value">{{ number_format($reportCards->first()?->average ?? 0, 2) }}</div>
                <div class="stat-description">Dernier bulletin</div>
            </div>
        </div>
        @endif

        {{-- ═══ GRILLE DE BULLETINS ═══ --}}
        @if(count($reportCards) > 0)
            <div class="report-cards-grid">
                @foreach($reportCards as $bulletin)
                    @php
                        // Déterminer la classe de style basée sur la moyenne
                        $average = $bulletin->average ?? 0;
                        $class = '';
                        if ($average >= 15) {
                            $class = 'excellent';
                        } elseif ($average >= 12) {
                            $class = 'good';
                        } elseif ($average >= 10) {
                            $class = 'average';
                        } else {
                            $class = 'poor';
                        }
                    @endphp
                    <div class="report-card-item {{ $class }}">
                        
                        {{-- Header --}}
                        <div class="report-card-header">
                            <h3 class="card-title">Trimestre {{ $bulletin->term ?? '-' }}</h3>
                            <div class="card-subtitle">
                                <i class="fas fa-calendar-check me-1"></i>
                                Année {{ $bulletin->year ?? date('Y') }}
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="report-card-body">
                            <div class="card-info-row">
                                <span class="card-info-label"><i class="fas fa-star me-1"></i>Moyenne</span>
                                <span class="card-info-value">{{ number_format($bulletin->average ?? 0, 2) }}/20</span>
                            </div>
                            <div class="card-info-row">
                                <span class="card-info-label"><i class="fas fa-trophy me-1"></i>Rang</span>
                                <span class="card-info-value">
                                    @if($bulletin->rank)
                                        {{ $bulletin->rank }}ème
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="card-info-row">
                                <span class="card-info-label"><i class="fas fa-book me-1"></i>Matières</span>
                                <span class="card-info-value">{{ $bulletin->subjects_count ?? 0 }}</span>
                            </div>
                            <div class="card-info-row">
                                <span class="card-info-label"><i class="fas fa-medal me-1"></i>Appréciation</span>
                                <span class="appreciation-badge">{{ $bulletin->appreciation ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Footer avec actions --}}
                        <div class="report-card-footer">
                            <a href="{{ route('student.report-cards.show', $bulletin->id) }}" class="btn-small btn-view">
                                <i class="fas fa-eye me-1"></i>Voir
                            </a>
                            <a href="{{ route('student.report-cards.pdf', $bulletin->id) }}" class="btn-small btn-pdf">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3 class="empty-state-title">Aucun bulletin disponible</h3>
                <p class="empty-state-text">Aucun bulletin n'est disponible pour le moment. Ils apparaîtront ici une fois validés par votre professeur principal.</p>
                <a href="{{ route('student.dashboard') }}" style="display: inline-block; background: #2563eb; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-arrow-left me-1"></i>Retour au Tableau de Bord
                </a>
            </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
    console.log('Student Report Cards page loaded');
    console.log('Total bulletins:', @json(count($reportCards)));
</script>
@endpush
