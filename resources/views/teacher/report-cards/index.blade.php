@extends('layouts.app')

@section('title', "Bulletins Classe — {$classe->name} — Millénaire Connect")

@section('content')
<style>
    /* ─────────────────────────────────────────────────────────────── */
    /* DESIGN SYSTEM - Report Cards Module                           */
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

    /* ─── Contrôles / Filtres ─────────────────────────────────────── */
    .report-cards-controls {
        background: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .filter-group {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-group label {
        font-weight: 600;
        color: #1e293b;
    }

    .filter-group select {
        padding: 8px 12px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .filter-group select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .btn-filter-reset {
        padding: 8px 16px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-filter-reset:hover {
        background: #e5e7eb;
    }

    /* ─── Stats Section ─────────────────────────────────────────────── */
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

    /* ─── Report Cards Grid ─────────────────────────────────────────── */
    .report-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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

    .report-card-item.success .report-card-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .report-card-item.pending .report-card-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .report-card-item.draft .report-card-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .card-student-name {
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0 0 5px 0;
    }

    .card-student-id {
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

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.draft {
        background: #f3e8ff;
        color: #5b21b6;
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

    .btn-edit {
        background: #f59e0b;
        color: white;
    }

    .btn-edit:hover {
        background: #d97706;
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

    /* ─── Responsive ─────────────────────────────────────────────────── */
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

        .filter-group {
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 5px;
        }

        .filter-group select {
            width: 100%;
        }
    }

    /* Animation */
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
</style>

<div class="report-cards-container">
    <div class="container-lg">
        
        {{-- ═══ HEADER ═══ --}}
        <div class="report-cards-header">
            <div class="report-cards-header-content">
                <h1>
                    <i class="fas fa-file-alt me-2"></i>Bulletins Classe
                </h1>
                <p class="header-subtitle">Gestion et consultation des bulletins de {{ $classe->name }}</p>
                
                <div class="header-meta">
                    <div class="meta-item">
                        <i class="fas fa-book"></i>
                        <span>{{ $classe->name }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-users"></i>
                        <span>{{ $classe->students()->where('is_active', true)->count() }} élèves</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Année {{ date('Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ FILTRES ═══ --}}
        <div class="report-cards-controls">
            <form method="GET" class="filter-group">
                <label for="term-filter"><i class="fas fa-filter me-1"></i>Trimestre :</label>
                <select name="term" id="term-filter" onchange="this.form.submit()">
                    <option value="">Tous les trimestres</option>
                    @foreach($terms ?? [] as $term)
                        <option value="{{ $term }}" {{ request('term') == $term ? 'selected' : '' }}>
                            Trimestre {{ $term }}
                        </option>
                    @endforeach
                </select>
                @if(request()->has('term') && request('term') != '')
                    <a href="{{ route('teacher.report-cards') }}" class="btn-filter-reset">
                        <i class="fas fa-times me-1"></i>Réinitialiser
                    </a>
                @endif
            </form>
        </div>

        {{-- ═══ STATISTIQUES ═══ --}}
        <div class="stats-section">
            <div class="stat-card primary">
                <div class="stat-label"><i class="fas fa-file-alt me-1"></i>Total Bulletins</div>
                <div class="stat-value">{{ $reportCards->count() }}</div>
                <div class="stat-description">Bulletins disponibles</div>
            </div>
            <div class="stat-card success">
                <div class="stat-label"><i class="fas fa-check-circle me-1"></i>Complétés</div>
                <div class="stat-value">{{ $reportCards->where('status', 'completed')->count() }}</div>
                <div class="stat-description">Prêts à consulter</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label"><i class="fas fa-hourglass-half me-1"></i>En attente</div>
                <div class="stat-value">{{ $reportCards->where('status', 'pending')->count() }}</div>
                <div class="stat-description">À finaliser</div>
            </div>
        </div>

        {{-- ═══ GRILLE BULLETINS ═══ --}}
        @if($reportCards->count() > 0)
            <div class="report-cards-grid">
                @foreach($reportCards as $bulletinCard)
                    <div class="report-card-item {{ $bulletinCard->status }}">
                        
                        {{-- Header avec statut --}}
                        <div class="report-card-header">
                            <h3 class="card-student-name">{{ $bulletinCard->student->full_name ?? 'N/A' }}</h3>
                            <div class="card-student-id">
                                <i class="fas fa-id-card me-1"></i>ID: {{ $bulletinCard->student->id ?? 'N/A' }}
                            </div>
                        </div>

                        {{-- Body avec infos --}}
                        <div class="report-card-body">
                            <div class="card-info-row">
                                <span class="card-info-label"><i class="fas fa-graduation-cap me-1"></i>Trimestre</span>
                                <span class="card-info-value">T{{ $bulletinCard->term ?? '-' }}</span>
                            </div>
                            <div class="card-info-row">
                                <span class="card-info-label"><i class="fas fa-star me-1"></i>Moyenne</span>
                                <span class="card-info-value">{{ number_format($bulletinCard->average ?? 0, 2) }}/20</span>
                            </div>
                            <div class="card-info-row">
                                <span class="card-info-label"><i class="fas fa-chart-bar me-1"></i>Rang</span>
                                <span class="card-info-value">{{ $bulletinCard->rank ?? '-' }}</span>
                            </div>
                            <div class="card-info-row">
                                <span class="card-info-label"><i class="fas fa-info-circle me-1"></i>Statut</span>
                                <span class="status-badge {{ $bulletinCard->status }}">
                                    {{ ucfirst($bulletinCard->status) }}
                                </span>
                            </div>
                        </div>

                        {{-- Footer avec actions --}}
                        <div class="report-card-footer">
                            <a href="{{ route('teacher.report-cards.show', $bulletinCard->id) }}" class="btn-small btn-view">
                                <i class="fas fa-eye me-1"></i>Voir
                            </a>
                            @if($bulletinCard->status == 'draft')
                                <a href="{{ route('teacher.report-cards.edit', $bulletinCard->id) }}" class="btn-small btn-edit">
                                    <i class="fas fa-edit me-1"></i>Éditer
                                </a>
                            @endif
                            <a href="{{ route('teacher.report-cards.pdf', $bulletinCard->id) }}" class="btn-small btn-pdf">
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
                <h3 class="empty-state-title">Aucun bulletin</h3>
                <p class="empty-state-text">Aucun bulletin n'est disponible pour cette classe pour le moment.</p>
                <a href="{{ route('teacher.bulletin.index') }}" class="btn" style="background: #2563eb; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-arrow-left me-1"></i>Retour aux bulletins
                </a>
            </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
    // Debug: Check if page loads
    console.log('Report Cards page loaded');
    console.log('Bulletins:', @json($reportCards->count()));
</script>
@endpush
