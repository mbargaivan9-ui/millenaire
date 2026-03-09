@extends('layouts.app')

@section('title', "Bulletin — {$reportCard->student->full_name} — Millénaire Connect")

@section('content')
<style>
    /* ─────────────────────────────────────────────────────────────── */
    /* DESIGN SYSTEM - Report Card Detail View                       */
    /* ─────────────────────────────────────────────────────────────── */

    .report-card-detail-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8ecef 100%);
        min-height: 100vh;
        padding: 30px 0;
    }

    .report-card-detail-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        color: white;
        padding: 40px;
        margin-bottom: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    }

    .detail-header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        flex-wrap: wrap;
    }

    .detail-header-left h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0 0 10px 0;
    }

    .detail-header-left .subtitle {
        font-size: 1.1rem;
        opacity: 0.95;
    }

    .detail-header-right {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .detail-btn {
        padding: 12px 20px;
        border: 2px solid white;
        background: transparent;
        color: white;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }

    .detail-btn:hover {
        background: white;
        color: #1e3a5f;
    }

    .detail-btn.btn-edit {
        background: rgba(251, 191, 36, 0.2);
        border-color: #fbbf24;
        color: white;
    }

    .detail-btn.btn-pdf {
        background: rgba(239, 68, 68, 0.2);
        border-color: #ef4444;
        color: white;
    }

    /* Content Cards */
    .detail-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .detail-card-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 20px 0;
        padding-bottom: 15px;
        border-bottom: 2px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .detail-card-title i {
        color: #2563eb;
        font-size: 1.5rem;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .info-item {
        padding: 16px;
        background: #f9fafb;
        border-radius: 8px;
        border-left: 4px solid #2563eb;
    }

    .info-label {
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .info-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }

    .info-description {
        font-size: 0.85rem;
        color: #9ca3af;
        margin-top: 5px;
    }

    /* Student Info Section */
    .student-info-section {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 20px;
        align-items: start;
    }

    .student-avatar {
        width: 100px;
        height: 100px;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
    }

    .student-details {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .student-name {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1e293b;
    }

    .student-meta {
        font-size: 0.95rem;
        color: #6b7280;
    }

    /* Grades Table */
    .grades-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .grades-table thead {
        background: #f3f4f6;
        border-bottom: 2px solid #e5e7eb;
    }

    .grades-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
    }

    .grades-table td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .grades-table tbody tr:hover {
        background: #f9fafb;
    }

    .grade-value {
        font-weight: 700;
        color: #2563eb;
        font-size: 1.1rem;
    }

    /* Summary Section */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .summary-item {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }

    .summary-item.success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .summary-item.warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .summary-label {
        font-size: 0.85rem;
        opacity: 0.9;
        margin-bottom: 5px;
    }

    .summary-value {
        font-size: 2rem;
        font-weight: 800;
    }

    /* Observations */
    .observations-text {
        background: #f9fafb;
        padding: 16px;
        border-radius: 8px;
        font-style: italic;
        color: #4b5563;
        border-left: 4px solid #2563eb;
        line-height: 1.6;
    }

    /* Back Button */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        color: #374151;
        text-decoration: none;
        transition: all 0.2s ease;
        margin-bottom: 20px;
    }

    .btn-back:hover {
        background: #e5e7eb;
        border-color: #9ca3af;
    }

    /* Print Styles */
    @media print {
        .report-card-detail-header,
        .detail-btn,
        .btn-back {
            display: none;
        }

        .detail-card {
            box-shadow: none;
            border: 1px solid #e5e7eb;
            page-break-inside: avoid;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .report-card-detail-header {
            padding: 30px;
        }

        .detail-header-content {
            flex-direction: column;
        }

        .student-info-section {
            grid-template-columns: 1fr;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .grades-table {
            font-size: 0.85rem;
        }

        .grades-table th,
        .grades-table td {
            padding: 8px;
        }
    }
</style>

<div class="report-card-detail-container">
    <div class="container-lg">
        
        {{-- Back Button --}}
        <a href="{{ route('teacher.report-cards') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            <span>Retour à la liste</span>
        </a>

        {{-- ═══ HEADER ═══ --}}
        <div class="report-card-detail-header">
            <div class="detail-header-content">
                <div class="detail-header-left">
                    <h1>
                        <i class="fas fa-file-alt me-2"></i>Détail Bulletin
                    </h1>
                    <p class="subtitle">Consultation et gestion du bulletin de l'élève</p>
                </div>
                <div class="detail-header-right">
                    <button onclick="window.print()" class="detail-btn">
                        <i class="fas fa-print me-1"></i>Imprimer
                    </button>
                    <a href="{{ route('teacher.report-cards.pdf', $reportCard->id) }}" class="detail-btn btn-pdf">
                        <i class="fas fa-file-pdf me-1"></i>Télécharger PDF
                    </a>
                    @if($reportCard->status == 'draft')
                        <a href="{{ route('teacher.report-cards.edit', $reportCard->id) }}" class="detail-btn btn-edit">
                            <i class="fas fa-edit me-1"></i>Éditer
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══ STUDENT INFO ═══ --}}
        <div class="detail-card">
            <h2 class="detail-card-title">
                <i class="fas fa-user"></i>Informations Élève
            </h2>
            
            <div class="student-info-section">
                <div class="student-avatar">
                    {{ strtoupper(substr($reportCard->student->first_name ?? 'E', 0, 1)) }}
                </div>
                <div class="student-details">
                    <div class="student-name">{{ $reportCard->student->full_name ?? 'N/A' }}</div>
                    <div class="student-meta">
                        <i class="fas fa-id-card me-1"></i>
                        ID: {{ $reportCard->student->id ?? 'N/A' }}
                    </div>
                    <div class="student-meta">
                        <i class="fas fa-envelope me-1"></i>
                        {{ $reportCard->student->email ?? 'Non disponible' }}
                    </div>
                    <div class="student-meta">
                        <i class="fas fa-graduation-cap me-1"></i>
                        Classe: {{ $reportCard->student->classe->name ?? 'N/A' }}
                    </div>
                </div>
            </div>

            <div class="info-grid" style="margin-top: 20px;">
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-calendar me-1"></i>Trimestre</div>
                    <div class="info-value">T{{ $reportCard->term ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-calendar-check me-1"></i>Année</div>
                    <div class="info-value">{{ date('Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-flag me-1"></i>Statut</div>
                    <div class="info-value">
                        <span style="display: inline-block; background: {{ $reportCard->status == 'completed' ? '#10b981' : '#f59e0b' }}; color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.75rem;">
                            {{ ucfirst($reportCard->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ SUMMARY ═══ --}}
        <div class="detail-card">
            <h2 class="detail-card-title">
                <i class="fas fa-chart-line"></i>Résumé Performance
            </h2>
            
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Moyenne Générale</div>
                    <div class="summary-value">{{ number_format($reportCard->average ?? 0, 2) }}</div>
                    <div class="summary-label">/20</div>
                </div>
                <div class="summary-item {{ $reportCard->rank <= 5 ? 'success' : '' }}">
                    <div class="summary-label">Rang de Classe</div>
                    <div class="summary-value">{{ $reportCard->rank ?? '-' }}</div>
                    <div class="summary-label">sur {{ $reportCard->student->classe->students()->where('is_active', true)->count() }}</div>
                </div>
                <div class="summary-item warning">
                    <div class="summary-label">Matières Prises</div>
                    <div class="summary-value">{{ $reportCard->subjects_count ?? 0 }}</div>
                </div>
            </div>

            {{-- Observations --}}
            @if($reportCard->observations)
            <div style="margin-top: 20px;">
                <h3 style="margin-bottom: 10px; color: #1e293b; font-weight: 600;">Observations</h3>
                <div class="observations-text">
                    {{ $reportCard->observations }}
                </div>
            </div>
            @endif
        </div>

        {{-- ═══ GRADES ═══ --}}
        <div class="detail-card">
            <h2 class="detail-card-title">
                <i class="fas fa-book-open"></i>Détail des Notes
            </h2>
            
            @if(isset($reportCard->grades) && count($reportCard->grades) > 0)
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Coefficient</th>
                            <th>Note</th>
                            <th>Appréciation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportCard->grades ?? [] as $grade)
                        <tr>
                            <td>{{ $grade->subject->name ?? 'N/A' }}</td>
                            <td>{{ $grade->coefficient ?? 1 }}</td>
                            <td><span class="grade-value">{{ number_format($grade->value ?? 0, 2) }}/20</span></td>
                            <td>{{ $grade->appreciation ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; padding: 30px; color: #9ca3af;">
                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <p>Aucune note disponible</p>
                </div>
            @endif
        </div>

        {{-- ═══ ACTIONS ═══ --}}
        <div style="margin-top: 30px; text-align: center; padding-bottom: 40px;">
            <a href="{{ route('teacher.report-cards') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Retour à la liste
            </a>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    console.log('Report Card Detail page loaded');
    console.log('Report Card:', @json($reportCard->id));
</script>
@endpush
