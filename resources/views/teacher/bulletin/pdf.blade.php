<!DOCTYPE html>
<html lang="{{ $bulletin->classe?->section === 'anglophone' ? 'en' : 'fr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bulletin — {{ $student->user->name }} — T{{ $bulletin->term }}S{{ $bulletin->sequence }}</title>
    <style>
        /* ── Reset & Base ──────────────────────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #1a1a2e;
            background: #fff;
            line-height: 1.4;
        }

        /* ── Page Layout ───────────────────────────────────────────────────── */
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 12mm 14mm;
            background: #fff;
        }

        /* ── Header ─────────────────────────────────────────────────────────── */
        .header {
            display: flex;
            align-items: center;
            padding-bottom: 8pt;
            border-bottom: 3pt solid #0d9488;
            margin-bottom: 8pt;
        }
        .header-logo { width: 60pt; margin-right: 12pt; }
        .header-logo img { width: 100%; height: auto; }
        .header-info { flex: 1; }
        .school-name {
            font-size: 14pt; font-weight: bold;
            color: #0d9488; letter-spacing: .3pt; line-height: 1.2;
        }
        .school-tagline { font-size: 8pt; color: #666; margin-top: 1pt; }
        .bulletin-title {
            text-align: center;
            background: #0d9488;
            color: #fff;
            padding: 4pt 10pt;
            border-radius: 4pt;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 4pt;
        }
        .header-qr { width: 55pt; margin-left: 8pt; }
        .header-qr img { width: 100%; height: auto; }

        /* ── Student Info Band ───────────────────────────────────────────────── */
        .student-band {
            display: flex;
            background: #f0fdfa;
            border: 1pt solid #0d9488;
            border-radius: 5pt;
            padding: 6pt 10pt;
            margin-bottom: 8pt;
            gap: 12pt;
            align-items: center;
        }
        .student-photo {
            width: 55pt; height: 65pt;
            border: 2pt solid #0d9488; border-radius: 4pt;
            object-fit: cover; flex-shrink: 0;
        }
        .student-photo-placeholder {
            width: 55pt; height: 65pt;
            border: 2pt solid #0d9488; border-radius: 4pt;
            background: #e0f2f1; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 18pt; color: #0d9488; font-weight: bold;
        }
        .student-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2pt 16pt;
            flex: 1;
        }
        .student-field { font-size: 8.5pt; }
        .student-label { color: #666; font-size: 7.5pt; text-transform: uppercase; letter-spacing: .3pt; }
        .student-value { font-weight: bold; color: #1a1a2e; }

        /* ── Period badge ────────────────────────────────────────────────────── */
        .period-badge {
            text-align: center;
            background: #0f766e;
            color: white;
            padding: 3pt 8pt;
            border-radius: 3pt;
            font-size: 8pt;
            font-weight: bold;
        }

        /* ── Grades Table ────────────────────────────────────────────────────── */
        table.grades {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 8pt;
        }
        table.grades thead tr {
            background: #0d9488;
            color: white;
        }
        table.grades thead th {
            padding: 4pt 5pt;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            letter-spacing: .2pt;
        }
        table.grades thead th:first-child { text-align: left; }
        table.grades tbody tr:nth-child(even) { background: #f8fffe; }
        table.grades tbody tr:nth-child(odd)  { background: #fff; }
        table.grades tbody td {
            padding: 3.5pt 5pt;
            border-bottom: .5pt solid #e0f2f1;
        }
        table.grades tbody td:not(:first-child) { text-align: center; }
        .subject-name { font-weight: 600; }
        .coef-badge {
            display: inline-block;
            background: #e0f2f1;
            color: #0d9488;
            padding: 0 3pt;
            border-radius: 2pt;
            font-size: 7pt;
            font-weight: bold;
        }

        /* Grade colors */
        .g-low    { color: #dc2626; font-weight: bold; }
        .g-mid    { color: #d97706; font-weight: bold; }
        .g-good   { color: #2563eb; font-weight: bold; }
        .g-high   { color: #059669; font-weight: bold; }
        .g-excel  { color: #7c3aed; font-weight: bold; }

        /* ── Results Band ────────────────────────────────────────────────────── */
        .results-band {
            display: flex;
            gap: 8pt;
            margin-bottom: 8pt;
        }
        .result-box {
            flex: 1;
            text-align: center;
            padding: 6pt;
            border: 1.5pt solid #0d9488;
            border-radius: 5pt;
        }
        .result-box.highlight { background: #0d9488; color: white; }
        .result-number { font-size: 18pt; font-weight: 900; line-height: 1; }
        .result-label  { font-size: 7pt; text-transform: uppercase; letter-spacing: .3pt; margin-top: 2pt; }
        .result-box.highlight .result-number { color: white; }
        .result-box.highlight .result-label  { color: rgba(255,255,255,.8); }

        /* ── Appreciation ────────────────────────────────────────────────────── */
        .appreciation-box {
            border: 1.5pt solid #0d9488;
            border-radius: 5pt;
            padding: 5pt 8pt;
            margin-bottom: 8pt;
        }
        .appreciation-title { font-size: 7.5pt; color: #666; text-transform: uppercase; letter-spacing: .3pt; margin-bottom: 2pt; }
        .appreciation-text  { font-size: 10pt; font-weight: bold; color: #0d9488; }
        .observation-text   { font-size: 8.5pt; color: #333; margin-top: 3pt; font-style: italic; }

        /* ── Absences ────────────────────────────────────────────────────────── */
        .absences-row {
            display: flex;
            gap: 8pt;
            margin-bottom: 8pt;
        }
        .absence-box {
            flex: 1;
            padding: 4pt 8pt;
            background: #fef2f2;
            border: 1pt solid #fca5a5;
            border-radius: 4pt;
            text-align: center;
        }
        .absence-number { font-size: 14pt; font-weight: 800; color: #dc2626; }
        .absence-label  { font-size: 7pt; color: #666; text-transform: uppercase; }
        .absence-justified { background: #ecfdf5; border-color: #86efac; }
        .absence-justified .absence-number { color: #059669; }

        /* ── Signatures ──────────────────────────────────────────────────────── */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 12pt;
            padding-top: 8pt;
            border-top: 1pt solid #e0f2f1;
        }
        .sig-box { text-align: center; width: 30%; }
        .sig-label { font-size: 7.5pt; color: #666; text-transform: uppercase; letter-spacing: .3pt; margin-bottom: 6pt; }
        .sig-line  { border-bottom: 1pt solid #ccc; height: 30pt; margin-bottom: 3pt; }
        .sig-img   { max-width: 80pt; max-height: 30pt; margin: 0 auto; display: block; }
        .sig-name  { font-size: 8pt; font-weight: bold; color: #1a1a2e; }

        /* ── Footer ──────────────────────────────────────────────────────────── */
        .footer {
            margin-top: 10pt;
            padding-top: 5pt;
            border-top: 1pt solid #e0f2f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 7pt;
            color: #999;
        }
        .footer-verify {
            font-size: 7pt;
            color: #0d9488;
        }

        /* ── Anglophone grade override ───────────────────────────────────────── */
        .letter-grade {
            display: inline-block;
            width: 22pt; height: 22pt;
            border-radius: 50%;
            text-align: center;
            line-height: 22pt;
            font-weight: 900;
            font-size: 10pt;
        }
        .lg-a { background:#059669; color:white; }
        .lg-b { background:#2563eb; color:white; }
        .lg-c { background:#d97706; color:white; }
        .lg-d { background:#dc2626; color:white; }
        .lg-f { background:#7f1d1d; color:white; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-logo">
            @if($settings->logo_path ?? null)
            <img src="{{ public_path($settings->logo_path) }}" alt="Logo">
            @else
            <div style="width:60pt;height:60pt;background:#0d9488;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:18pt;font-weight:900">M</div>
            @endif
        </div>
        <div class="header-info">
            <div class="school-name">{{ $settings->platform_name ?? 'Collège Millénaire Bilingue' }}</div>
            <div class="school-tagline">{{ $settings->address ?? 'Douala, Cameroun' }} · {{ $settings->phone ?? '' }}</div>
            <div class="bulletin-title">
                @if($isAnglo)
                    REPORT CARD — Term {{ $bulletin->term }} / Sequence {{ $bulletin->sequence }}
                @else
                    BULLETIN SCOLAIRE — Trimestre {{ $bulletin->term }} / Séquence {{ $bulletin->sequence }}
                @endif
            </div>
        </div>
        @if($qrCodeSvg ?? null)
        <div class="header-qr">
            {!! $qrCodeSvg !!}
        </div>
        @endif
    </div>

    {{-- ── Student Info ──────────────────────────────────────────────────────── --}}
    <div class="student-band">
        @if($student->user->avatar_path ?? null)
        <img src="{{ public_path($student->user->avatar_path) }}" class="student-photo" alt="">
        @else
        <div class="student-photo-placeholder">
            {{ strtoupper(substr($student->user->name ?? 'E', 0, 1)) }}
        </div>
        @endif

        <div class="student-grid">
            <div class="student-field">
                <div class="student-label">{{ $isAnglo ? 'Full Name' : 'Nom & Prénom' }}</div>
                <div class="student-value">{{ $student->user->name }}</div>
            </div>
            <div class="student-field">
                <div class="student-label">{{ $isAnglo ? 'Class' : 'Classe' }}</div>
                <div class="student-value">{{ $bulletin->classe?->name }}</div>
            </div>
            <div class="student-field">
                <div class="student-label">{{ $isAnglo ? 'Student ID' : 'Matricule' }}</div>
                <div class="student-value">{{ $student->matricule }}</div>
            </div>
            <div class="student-field">
                <div class="student-label">{{ $isAnglo ? 'Date of Birth' : 'Date de Naissance' }}</div>
                <div class="student-value">{{ $student->birth_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="student-field">
                <div class="student-label">{{ $isAnglo ? 'Academic Year' : 'Année Scolaire' }}</div>
                <div class="student-value">{{ $academicYear ?? date('Y') . '/' . (date('Y') + 1) }}</div>
            </div>
            <div class="student-field">
                <div class="student-label">{{ $isAnglo ? 'Section' : 'Section' }}</div>
                <div class="student-value">
                    {{ $isAnglo ? '🇬🇧 Anglophone' : '🇫🇷 Francophone' }}
                </div>
            </div>
        </div>

        <div class="period-badge">
            @if($isAnglo)
                TERM {{ $bulletin->term }}<br>SEQ. {{ $bulletin->sequence }}
            @else
                TRIM. {{ $bulletin->term }}<br>SÉQ. {{ $bulletin->sequence }}
            @endif
        </div>
    </div>

    {{-- ── Grades Table ──────────────────────────────────────────────────────── --}}
    <table class="grades">
        <thead>
            <tr>
                <th style="width:38%">{{ $isAnglo ? 'Subject' : 'Matière' }}</th>
                <th>{{ $isAnglo ? 'Coef.' : 'Coef.' }}</th>
                <th>{{ $isAnglo ? 'Grade' : 'Note /20' }}</th>
                @if($isAnglo)
                <th>Letter</th>
                @endif
                <th>{{ $isAnglo ? 'Points' : 'Points' }}</th>
                <th>{{ $isAnglo ? 'Class Avg.' : 'Moy. Classe' }}</th>
                <th>{{ $isAnglo ? 'Appreciation' : 'Appréciation' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($marks as $mark)
            @php
                $score = (float)$mark->score;
                $coef  = (int)($mark->subject?->coefficient ?? 1);
                $points= $score * $coef;
                $classAvg = $classAverages[$mark->subject_id] ?? null;
                $colorClass = $score >= 16 ? 'g-high' : ($score >= 13 ? 'g-good' : ($score >= 10 ? 'g-mid' : 'g-low'));
                $appr = $score >= 16 ? ($isAnglo ? 'Excellent' : 'Très Bien') : ($score >= 13 ? ($isAnglo ? 'Good' : 'Bien') : ($score >= 10 ? ($isAnglo ? 'Fair' : 'Assez Bien') : ($isAnglo ? 'Insufficient' : 'Insuffisant')));
                $letter = $score >= 16 ? 'A' : ($score >= 13 ? 'B' : ($score >= 10 ? 'C' : ($score >= 7 ? 'D' : 'F')));
                $letterCls = 'lg-' . strtolower($letter);
            @endphp
            <tr>
                <td>
                    <span class="subject-name">{{ $mark->subject?->name }}</span>
                </td>
                <td>
                    <span class="coef-badge">×{{ $coef }}</span>
                </td>
                <td class="{{ $colorClass }}">{{ number_format($score, 2) }}</td>
                @if($isAnglo)
                <td><span class="letter-grade {{ $letterCls }}">{{ $letter }}</span></td>
                @endif
                <td>{{ number_format($points, 2) }}</td>
                <td>{{ $classAvg !== null ? number_format($classAvg, 2) : '—' }}</td>
                <td style="font-size:8pt">{{ $appr }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Results Band ──────────────────────────────────────────────────────── --}}
    <div class="results-band">
        @php
            $moyClasse = $classMoyenne ?? null;
            $maxMoy    = $maxMoyenne ?? null;
            $minMoy    = $minMoyenne ?? null;
        @endphp
        <div class="result-box highlight">
            <div class="result-number">{{ $bulletin->moyenne !== null ? number_format((float)$bulletin->moyenne, 2) : '—' }}</div>
            <div class="result-label">{{ $isAnglo ? 'General Average' : 'Moyenne Générale' }}</div>
        </div>
        <div class="result-box">
            <div class="result-number" style="color:#0d9488">{{ $bulletin->rang ?? '—' }}<sup style="font-size:10pt">{{ $isAnglo ? 'th' : 'e' }}</sup></div>
            <div class="result-label">{{ $isAnglo ? 'Class Rank' : 'Rang dans la Classe' }}</div>
        </div>
        <div class="result-box">
            <div class="result-number" style="color:#666;font-size:12pt">{{ $moyClasse !== null ? number_format($moyClasse, 2) : '—' }}</div>
            <div class="result-label">{{ $isAnglo ? 'Class Average' : 'Moy. de la Classe' }}</div>
        </div>
        <div class="result-box">
            <div class="result-number" style="color:#666;font-size:12pt">{{ $maxMoy !== null ? number_format($maxMoy, 2) : '—' }}</div>
            <div class="result-label">{{ $isAnglo ? 'Highest Average' : 'Meilleure Moy.' }}</div>
        </div>
        <div class="result-box">
            <div class="result-number" style="color:#666;font-size:12pt">{{ $minMoy !== null ? number_format($minMoy, 2) : '—' }}</div>
            <div class="result-label">{{ $isAnglo ? 'Lowest Average' : 'Plus Basse Moy.' }}</div>
        </div>
    </div>

    {{-- ── Appreciation & Observation ────────────────────────────────────────── --}}
    <div class="appreciation-box">
        <div class="appreciation-title">{{ $isAnglo ? 'Appreciation' : 'Appréciation du Professeur Principal' }}</div>
        <div class="appreciation-text">{{ $bulletin->appreciation ?? ($isAnglo ? 'Fair' : 'Assez Bien') }}</div>
        @if($bulletin->observation ?? null)
        <div class="observation-text">{{ $bulletin->observation }}</div>
        @endif
    </div>

    {{-- ── Absences ──────────────────────────────────────────────────────────── --}}
    <div class="absences-row">
        <div class="absence-box">
            <div class="absence-number">{{ $absences['total'] ?? 0 }}</div>
            <div class="absence-label">{{ $isAnglo ? 'Total Absences' : 'Absences Total' }}</div>
        </div>
        <div class="absence-box absence-justified">
            <div class="absence-number">{{ $absences['justified'] ?? 0 }}</div>
            <div class="absence-label">{{ $isAnglo ? 'Justified' : 'Justifiées' }}</div>
        </div>
        <div class="absence-box">
            <div class="absence-number">{{ $absences['unjustified'] ?? 0 }}</div>
            <div class="absence-label">{{ $isAnglo ? 'Unjustified' : 'Non Justifiées' }}</div>
        </div>
    </div>

    {{-- ── Signatures ────────────────────────────────────────────────────────── --}}
    <div class="signatures">
        <div class="sig-box">
            <div class="sig-label">{{ $isAnglo ? 'Head Teacher' : 'Professeur Principal' }}</div>
            <div class="sig-line">
                @if($headTeacher?->signature_path ?? null)
                <img src="{{ public_path($headTeacher->signature_path) }}" class="sig-img" alt="">
                @endif
            </div>
            <div class="sig-name">{{ $headTeacher?->user?->name ?? '___________________' }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">{{ $isAnglo ? 'Parent / Guardian' : 'Parent / Tuteur' }}</div>
            <div class="sig-line"></div>
            <div class="sig-name">{{ $student->user->name }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">{{ $isAnglo ? 'Principal' : 'Proviseur' }}</div>
            <div class="sig-line">
                @if($settings->signature_image ?? null)
                <img src="{{ public_path($settings->signature_image) }}" class="sig-img" alt="">
                @endif
            </div>
            <div class="sig-name">{{ $settings->proviseur_name ?? $settings->platform_name }}</div>
        </div>
    </div>

    {{-- ── Footer ────────────────────────────────────────────────────────────── --}}
    <div class="footer">
        <span>{{ $settings->platform_name }} · {{ date('Y') }}</span>
        <span class="footer-verify">
            🔒 {{ $isAnglo ? 'Verify at:' : 'Vérifier sur:' }}
            {{ route('bulletin.verify', $bulletin->verification_token) }}
        </span>
        <span>{{ $isAnglo ? 'Page' : 'Page' }} 1/1</span>
    </div>

</div>
</body>
</html>
