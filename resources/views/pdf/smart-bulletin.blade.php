{{-- resources/views/pdf/smart-bulletin.blade.php --}}
{{-- Template PDF généré par DomPDF — optimisé @media print --}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    /* ── Reset & Base ── */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 10px;
        color: #1a1a1a;
        background: #fff;
    }

    /* ── Page ── */
    @page { margin: 15mm 12mm; size: A4 portrait; }
    .page { width: 100%; }

    /* ── En-tête établissement ── */
    .header {
        border-bottom: 3px solid #003366;
        padding-bottom: 10px;
        margin-bottom: 12px;
    }
    .header-inner {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .school-logo {
        width: 70px;
        height: 70px;
        object-fit: contain;
        flex-shrink: 0;
    }
    .school-logo-placeholder {
        width: 70px;
        height: 70px;
        background: #003366;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        font-weight: bold;
        flex-shrink: 0;
    }
    .school-info { flex: 1; }
    .school-name {
        font-size: 14px;
        font-weight: bold;
        color: #003366;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .school-address { font-size: 8.5px; color: #555; margin-top: 2px; }

    .bulletin-title {
        text-align: center;
        flex-shrink: 0;
    }
    .bulletin-title h1 {
        font-size: 13px;
        font-weight: bold;
        color: #003366;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: 2px solid #003366;
        padding: 6px 12px;
        display: inline-block;
    }
    .bulletin-title .period {
        font-size: 9px;
        color: #555;
        margin-top: 4px;
    }

    /* ── Infos élève ── */
    .student-info {
        background: #f0f4ff;
        border-left: 4px solid #003366;
        padding: 8px 12px;
        margin-bottom: 12px;
        border-radius: 0 4px 4px 0;
    }
    .student-info table { width: 100%; }
    .student-info td { padding: 2px 8px 2px 0; font-size: 10px; }
    .student-info .label { color: #666; font-weight: normal; min-width: 80px; }
    .student-info .value { font-weight: bold; color: #1a1a1a; }

    /* ── Table des notes ── */
    .grades-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .grades-table thead tr {
        background: #003366;
        color: white;
    }
    .grades-table thead th {
        padding: 7px 8px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        text-align: center;
    }
    .grades-table thead th:first-child { text-align: left; padding-left: 10px; }
    .grades-table tbody tr:nth-child(even) { background: #f8f9ff; }
    .grades-table tbody tr:nth-child(odd)  { background: #ffffff; }
    .grades-table tbody td {
        padding: 6px 8px;
        font-size: 9.5px;
        border-bottom: 1px solid #e8e8f0;
        vertical-align: middle;
    }
    .grades-table tbody td:first-child { text-align: left; padding-left: 10px; font-weight: 500; }
    .grades-table tbody td.center { text-align: center; }
    .grade-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 3px;
        font-weight: bold;
        font-size: 10px;
        min-width: 35px;
        text-align: center;
    }
    .grade-good    { background: #d4edda; color: #155724; }
    .grade-average { background: #fff3cd; color: #856404; }
    .grade-bad     { background: #f8d7da; color: #721c24; }
    .grade-absent  { background: #e2e3e5; color: #6c757d; font-style: italic; }

    .appreciation-text { font-size: 8.5px; color: #444; font-style: italic; }

    /* ── Résumé / Statistiques ── */
    .summary-box {
        display: flex;
        gap: 0;
        border: 1.5px solid #003366;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 12px;
    }
    .summary-item {
        flex: 1;
        text-align: center;
        padding: 10px 8px;
        border-right: 1px solid #d0d8e8;
    }
    .summary-item:last-child { border-right: none; }
    .summary-item .sum-label { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
    .summary-item .sum-value { font-size: 16px; font-weight: bold; color: #003366; margin: 3px 0; }
    .summary-item .sum-sub   { font-size: 8px; color: #888; }

    .appreciation-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        letter-spacing: 0.3px;
    }

    /* ── Observations ── */
    .observations {
        display: flex;
        gap: 10px;
        margin-bottom: 12px;
    }
    .obs-box {
        flex: 1;
        border: 1px solid #d0d0d0;
        border-radius: 4px;
        padding: 8px 10px;
    }
    .obs-box h4 {
        font-size: 8.5px;
        font-weight: bold;
        color: #003366;
        text-transform: uppercase;
        margin-bottom: 4px;
        padding-bottom: 3px;
        border-bottom: 1px solid #e0e0e0;
    }
    .obs-box p {
        font-size: 9px;
        color: #333;
        min-height: 30px;
        font-style: italic;
    }

    /* ── Pied de page ── */
    .footer {
        border-top: 2px solid #003366;
        padding-top: 8px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 10px;
    }
    .signature-block { text-align: center; }
    .signature-line {
        width: 120px;
        border-bottom: 1px solid #333;
        margin: 20px auto 4px;
    }
    .signature-label { font-size: 8px; color: #666; text-transform: uppercase; }

    .qr-zone { text-align: center; }
    .qr-zone .token { font-size: 7px; color: #aaa; font-family: monospace; }

    /* ── Print ── */
    @media print {
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
    }
</style>
</head>
<body>

<div class="page">

    {{-- ═══ EN-TÊTE ═══ --}}
    <div class="header">
        <div class="header-inner">
            {{-- Logo --}}
            @php $settings = \App\Models\EstablishmentSetting::first(); @endphp
            @if($settings?->logo_path)
                <img src="{{ public_path('storage/' . $settings->logo_path) }}" class="school-logo" alt="Logo">
            @else
                <div class="school-logo-placeholder">
                    {{ strtoupper(substr($settings?->about_title ?? 'É', 0, 1)) }}
                </div>
            @endif

            {{-- Infos école --}}
            <div class="school-info">
                <div class="school-name">{{ $settings?->about_title ?? 'Établissement Scolaire' }}</div>
                <div class="school-address">{{ $settings?->address ?? '' }}</div>
                @if($settings?->email)
                    <div class="school-address">{{ $settings->email }} | {{ $settings->phone }}</div>
                @endif
            </div>

            {{-- Titre bulletin --}}
            <div class="bulletin-title">
                <h1>BULLETIN DE NOTES</h1>
                <div class="period">
                    Trimestre {{ $bulletin->term }} — {{ $bulletin->academic_year }}
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ INFOS ÉLÈVE ═══ --}}
    <div class="student-info">
        <table>
            <tr>
                <td class="label">Élève :</td>
                <td class="value">{{ strtoupper($student->last_name) }} {{ $student->first_name }}</td>
                <td class="label">Matricule :</td>
                <td class="value">{{ $student->matricule ?? 'N/A' }}</td>
                <td class="label">Classe :</td>
                <td class="value">{{ $classe->name }}</td>
            </tr>
            <tr>
                <td class="label">Date de naissance :</td>
                <td class="value">{{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : 'N/A' }}</td>
                <td class="label">Année scolaire :</td>
                <td class="value">{{ $bulletin->academic_year }}</td>
                <td class="label">Effectif :</td>
                <td class="value">{{ $bulletin->total_students ?? 'N/A' }} élèves</td>
            </tr>
        </table>
    </div>

    {{-- ═══ TABLE DES NOTES ═══ --}}
    <table class="grades-table">
        <thead>
            <tr>
                <th style="width:30%">Matière</th>
                <th style="width:8%">Coeff.</th>
                <th style="width:12%">Note /20</th>
                <th style="width:12%">Moy. Pondérée</th>
                <th style="width:38%">Appréciation du professeur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grades as $grade)
                @php
                    $g = $grade->grade;
                    $badgeClass = match(true) {
                        $grade->absent         => 'grade-absent',
                        $g === null            => 'grade-absent',
                        $g >= 16               => 'grade-good',
                        $g >= 10               => 'grade-average',
                        default                => 'grade-bad',
                    };
                @endphp
                <tr>
                    <td>{{ $grade->subject->name ?? 'N/A' }}</td>
                    <td class="center">{{ number_format($grade->coefficient, 1) }}</td>
                    <td class="center">
                        <span class="grade-badge {{ $badgeClass }}">
                            {{ $grade->display_grade }}
                        </span>
                    </td>
                    <td class="center">
                        @if($grade->countsForAverage())
                            {{ number_format($grade->grade * $grade->coefficient, 2) }}
                        @else
                            –
                        @endif
                    </td>
                    <td>
                        <span class="appreciation-text">
                            {{ $grade->teacher_appreciation ?: ($grade->ai_suggestion_accepted ? $grade->ai_suggested_appreciation : '') ?: '–' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══ RÉSUMÉ STATISTIQUES ═══ --}}
    <div class="summary-box">
        <div class="summary-item">
            <div class="sum-label">Moyenne générale</div>
            <div class="sum-value">
                {{ $bulletin->student_average !== null ? number_format($bulletin->student_average, 2) : '–' }}<span style="font-size:10px;color:#888">/20</span>
            </div>
            <div class="sum-sub">Pondérée par coefficients</div>
        </div>
        <div class="summary-item">
            <div class="sum-label">Rang dans la classe</div>
            <div class="sum-value">{{ $rank_label }}</div>
            <div class="sum-sub">{{ $bulletin->total_students ?? '–' }} élèves</div>
        </div>
        <div class="summary-item">
            <div class="sum-label">Moyenne de la classe</div>
            <div class="sum-value">
                {{ $bulletin->class_average !== null ? number_format($bulletin->class_average, 2) : '–' }}<span style="font-size:10px;color:#888">/20</span>
            </div>
            <div class="sum-sub">Trimestre {{ $bulletin->term }}</div>
        </div>
        <div class="summary-item">
            <div class="sum-label">Appréciation générale</div>
            <div class="sum-value" style="font-size:12px;">
                @if($bulletin->appreciation)
                    <span class="appreciation-badge"
                          style="background: {{ $bulletin->appreciation_color }}22; color: {{ $bulletin->appreciation_color }}; border: 1.5px solid {{ $bulletin->appreciation_color }}">
                        {{ $bulletin->appreciation }}
                    </span>
                @else
                    –
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ OBSERVATIONS ═══ --}}
    <div class="observations">
        <div class="obs-box">
            <h4>Observation du professeur principal</h4>
            <p>{{ $bulletin->principal_comment ?: 'Néant' }}</p>
        </div>
        <div class="obs-box">
            <h4>Appréciation de l'administration</h4>
            <p>{{ $bulletin->admin_comment ?: 'Néant' }}</p>
        </div>
    </div>

    {{-- ═══ PIED DE PAGE ═══ --}}
    <div class="footer">
        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-label">Professeur Principal</div>
        </div>

        <div style="text-align:center; font-size:8px; color:#999;">
            <div>Bulletin généré le {{ now()->format('d/m/Y à H:i') }}</div>
            <div style="margin-top:2px; font-family:monospace; font-size:7px; color:#ccc;">
                {{ $bulletin->verification_token }}
            </div>
        </div>

        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-label">{{ $settings?->proviseur_name ? 'Le Proviseur : ' . $settings->proviseur_name : 'Le Proviseur' }}</div>
        </div>
    </div>

</div>

</body>
</html>
