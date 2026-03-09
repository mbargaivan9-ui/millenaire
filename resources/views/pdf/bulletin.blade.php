<!DOCTYPE html>
<html lang="{{ $bulletin->student?->classe?->section === 'anglophone' ? 'en' : 'fr' }}">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; }

    /* ─── Page layout ─────────────────────────────────────────────────────── */
    .page { width: 210mm; padding: 12mm 14mm; }

    /* ─── Header ─────────────────────────────────────────────────────────── */
    .bulletin-header { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; border-bottom: 2px solid #0d9488; padding-bottom: 10px; }
    .school-logo { width: 60px; height: 60px; border-radius: 8px; object-fit: contain; }
    .school-info { flex: 1; }
    .school-name { font-size: 14px; font-weight: bold; color: #0d9488; margin-bottom: 2px; }
    .school-sub  { font-size: 9px; color: #475569; }
    .bulletin-title { text-align: right; }
    .bulletin-title h2 { font-size: 13px; font-weight: bold; color: #0f172a; text-transform: uppercase; letter-spacing: .5px; }
    .bulletin-title .period { font-size: 10px; color: #0d9488; font-weight: bold; }

    /* ─── Student info box ────────────────────────────────────────────────── */
    .student-info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; }
    .info-grid { display: flex; gap: 12px; flex-wrap: wrap; }
    .info-item { flex: 1; min-width: 120px; }
    .info-label { font-size: 8px; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; font-weight: bold; margin-bottom: 2px; }
    .info-value { font-size: 10px; font-weight: bold; color: #0f172a; }

    /* ─── Grades table ────────────────────────────────────────────────────── */
    .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .grades-table th { background: #0d9488; color: #fff; padding: 6px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: .4px; text-align: center; }
    .grades-table th:first-child { text-align: left; }
    .grades-table td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-size: 9.5px; vertical-align: middle; }
    .grades-table tr:nth-child(even) td { background: #f8fafc; }
    .grades-table td:not(:first-child) { text-align: center; }
    .subject-name { font-weight: 600; }

    /* Grade color coding */
    .grade-low  { color: #ef4444; font-weight: bold; }
    .grade-mid  { color: #f59e0b; font-weight: bold; }
    .grade-good { color: #3b82f6; font-weight: bold; }
    .grade-high { color: #10b981; font-weight: bold; }

    /* ─── Summary row ────────────────────────────────────────────────────── */
    .summary-row td { background: #0d9488 !important; color: #fff !important; font-weight: bold; padding: 7px 8px; font-size: 10px; }

    /* ─── Stats grid ─────────────────────────────────────────────────────── */
    .stats-grid { display: flex; gap: 10px; margin-bottom: 12px; }
    .stat-box { flex: 1; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 8px; text-align: center; }
    .stat-box-value { font-size: 18px; font-weight: 900; color: #0d9488; line-height: 1; }
    .stat-box-label { font-size: 8px; color: #94a3b8; text-transform: uppercase; letter-spacing: .4px; margin-top: 3px; font-weight: bold; }

    /* ─── Footer with signature ───────────────────────────────────────────── */
    .bulletin-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 16px; padding-top: 12px; border-top: 1px solid #e2e8f0; }
    .proviseur-block { text-align: center; min-width: 150px; }
    .proviseur-sig { max-width: 100px; max-height: 50px; margin: 0 auto 5px; display: block; }
    .proviseur-name  { font-size: 9px; font-weight: bold; color: #0f172a; }
    .proviseur-title { font-size: 8px; color: #94a3b8; }

    /* ─── QR Code ─────────────────────────────────────────────────────────── */
    .qr-block { text-align: center; }
    .qr-label { font-size: 8px; color: #94a3b8; margin-top: 4px; }

    /* ─── Appreciation badge ─────────────────────────────────────────────── */
    .appr-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 9px; font-weight: bold; }

    /* ─── Observations box ───────────────────────────────────────────────── */
    .observations { border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; margin-bottom: 12px; }
    .obs-title { font-size: 9px; font-weight: bold; color: #475569; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
    .obs-text  { font-size: 9.5px; color: #0f172a; }
</style>
</head>
<body>
<div class="page">

    @php
        $settings   = \App\Models\EstablishmentSetting::getInstance();
        $student    = $bulletin->student;
        $class      = $student->classe;
        $marks      = $marks ?? $bulletin->marks ?? collect();
        $isFr       = $class?->section !== 'anglophone';
        $moyenne    = $bulletin->moyenne;
        $rang       = $bulletin->rang;
        $totalStudents = $totalStudents ?? $class?->students()->count() ?? 0;

        $apprColor = match(true) {
            $moyenne === null  => '#94a3b8',
            $moyenne < 10      => '#ef4444',
            $moyenne < 13      => '#f59e0b',
            $moyenne < 16      => '#3b82f6',
            $moyenne < 19      => '#10b981',
            default            => '#8b5cf6',
        };
        $apprLabel = match(true) {
            $moyenne === null  => '—',
            $moyenne < 10      => $isFr ? 'Insuffisant'  : 'Insufficient',
            $moyenne < 13      => $isFr ? 'Assez Bien'   : 'Fair',
            $moyenne < 16      => $isFr ? 'Bien'          : 'Good',
            $moyenne < 19      => $isFr ? 'Très Bien'     : 'Very Good',
            default            => 'Excellent',
        };

        $gradeClass = fn($s) => match(true) {
            $s < 10  => 'grade-low',
            $s < 13  => 'grade-mid',
            $s < 16  => 'grade-good',
            default  => 'grade-high',
        };
    @endphp

    {{-- ─── Header ─────────────────────────────────────────────────────────── --}}
    <div class="bulletin-header">
        @if($settings->logo_path)
        <img src="{{ public_path($settings->logo_path) }}" class="school-logo" alt="Logo">
        @endif
        <div class="school-info">
            <div class="school-name">{{ $settings->platform_name ?? 'Collège Millénaire Bilingue' }}</div>
            <div class="school-sub">{{ $settings->address ?? 'Douala, Cameroun' }}</div>
            <div class="school-sub">{{ $settings->phone }} · {{ $settings->email }}</div>
        </div>
        <div class="bulletin-title">
            <h2>{{ $isFr ? 'Bulletin de Notes' : 'Report Card' }}</h2>
            <div class="period">
                {{ $isFr ? 'Trimestre' : 'Term' }} {{ $bulletin->term }} — {{ $isFr ? 'Séquence' : 'Sequence' }} {{ $bulletin->sequence }}
            </div>
            <div style="font-size:9px;color:#94a3b8;margin-top:4px">
                {{ $isFr ? 'Année Scolaire' : 'Academic Year' }} {{ $settings->current_academic_year ?? date('Y') . '/' . (date('Y') + 1) }}
            </div>
        </div>
    </div>

    {{-- ─── Student info ─────────────────────────────────────────────────────── --}}
    <div class="student-info-box">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">{{ $isFr ? 'Nom & Prénom' : 'Full Name' }}</div>
                <div class="info-value">{{ $student->user->display_name ?? $student->user->name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Matricule</div>
                <div class="info-value">{{ $student->matricule }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">{{ $isFr ? 'Classe' : 'Class' }}</div>
                <div class="info-value">{{ $class?->name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">{{ $isFr ? 'Section' : 'Section' }}</div>
                <div class="info-value">{{ $class?->section === 'anglophone' ? '🇬🇧 Anglophone' : '🇫🇷 Francophone' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">{{ $isFr ? 'Prof. Principal' : 'Head Teacher' }}</div>
                <div class="info-value">{{ $class?->headTeacher?->user?->name ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- ─── Summary stats ───────────────────────────────────────────────────── --}}
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-box-value" style="color:{{ $apprColor }}">{{ $moyenne !== null ? number_format((float)$moyenne, 2) : '—' }}</div>
            <div class="stat-box-label">{{ $isFr ? 'Moy. Générale' : 'General Avg.' }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-box-value">{{ $rang !== null ? $rang . 'e/' . $totalStudents : '—' }}</div>
            <div class="stat-box-label">{{ $isFr ? 'Rang' : 'Rank' }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-box-value">{{ $classMoyenne ?? '—' }}</div>
            <div class="stat-box-label">{{ $isFr ? 'Moy. Classe' : 'Class Avg.' }}</div>
        </div>
        <div class="stat-box" style="border-color:{{ $apprColor }}22">
            <div class="appr-badge" style="background:{{ $apprColor }}22;color:{{ $apprColor }};font-size:12px;padding:4px 10px">
                {{ $apprLabel }}
            </div>
            <div class="stat-box-label" style="margin-top:6px">{{ $isFr ? 'Appréciation' : 'Grade' }}</div>
        </div>
    </div>

    {{-- ─── Grades table ─────────────────────────────────────────────────────── --}}
    <table class="grades-table">
        <thead>
            <tr>
                <th style="width:35%">{{ $isFr ? 'Matière' : 'Subject' }}</th>
                <th style="width:8%">Coef.</th>
                <th style="width:10%">{{ $isFr ? 'Note' : 'Grade' }}</th>
                <th style="width:10%">{{ $isFr ? 'Moy. Cl.' : 'Cl. Avg.' }}</th>
                <th style="width:12%">{{ $isFr ? 'Points' : 'Points' }}</th>
                <th style="width:25%">{{ $isFr ? 'Appréciation' : 'Remark' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($marks as $mark)
            @php
                $score = (float)($mark->score ?? 0);
                $coef  = $mark->subject?->coefficient ?? 1;
                $pts   = $score * $coef;
                $cls   = $gradeClass($score);
            @endphp
            <tr>
                <td class="subject-name">{{ $mark->subject?->name }}</td>
                <td>{{ $coef }}</td>
                <td class="{{ $cls }}">{{ number_format($score, 2) }}/20</td>
                <td style="color:#64748b">{{ number_format((float)($mark->class_average ?? 0), 2) }}</td>
                <td class="{{ $cls }}">{{ number_format($pts, 2) }}</td>
                <td>
                    @php
                        $a = match(true) { $score < 10 => ['Insuffisant','#ef4444'], $score < 13 => ['Assez Bien','#f59e0b'], $score < 16 => ['Bien','#3b82f6'], default => ['Très Bien','#10b981'] };
                    @endphp
                    <span class="appr-badge" style="background:{{ $a[1] }}22;color:{{ $a[1] }}">{{ $a[0] }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="summary-row">
                <td>{{ $isFr ? 'MOYENNE GÉNÉRALE' : 'GENERAL AVERAGE' }}</td>
                <td colspan="4" style="text-align:center">{{ $moyenne !== null ? number_format((float)$moyenne, 2) . '/20' : '—' }}</td>
                <td style="text-align:center">{{ $apprLabel }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ─── Observations ─────────────────────────────────────────────────────── --}}
    @if($bulletin->observations ?? null)
    <div class="observations">
        <div class="obs-title">{{ $isFr ? 'Observations du Conseil de Classe' : "Class Council's Remarks" }}</div>
        <div class="obs-text">{{ $bulletin->observations }}</div>
    </div>
    @endif

    {{-- ─── Footer ──────────────────────────────────────────────────────────── --}}
    <div class="bulletin-footer">
        <div>
            <div style="font-size:8px;color:#94a3b8;margin-bottom:4px">{{ $isFr ? 'Visa des Parents / Tuteurs' : "Parent / Guardian's Signature" }}</div>
            <div style="width:140px;height:50px;border:1px solid #e2e8f0;border-radius:6px"></div>
        </div>

        <div class="proviseur-block">
            @if($settings->signature_image)
            <img src="{{ public_path($settings->signature_image) }}" class="proviseur-sig" alt="Signature">
            @else
            <div style="width:100px;height:45px;border-bottom:1px solid #0f172a;margin:0 auto 5px"></div>
            @endif
            <div class="proviseur-name">{{ $settings->proviseur_name ?? 'Le Proviseur' }}</div>
            <div class="proviseur-title">{{ $settings->proviseur_title ?? ($isFr ? 'Directeur de l\'Établissement' : 'School Principal') }}</div>
        </div>

        <div class="qr-block">
            @if($bulletin->verification_token)
            {!! QrCode::size(70)->generate(route('bulletin.verify', $bulletin->verification_token)) !!}
            <div class="qr-label">{{ $isFr ? 'Vérifier l\'authenticité' : 'Verify authenticity' }}</div>
            @endif
        </div>
    </div>

    {{-- ─── Footer bar ──────────────────────────────────────────────────────── --}}
    <div style="margin-top:12px;padding-top:8px;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;font-size:8px;color:#94a3b8">
        <span>{{ $settings->platform_name }} · {{ $settings->address }}</span>
        <span>{{ $isFr ? 'Généré le' : 'Generated on' }} {{ now()->format('d/m/Y à H:i') }}</span>
    </div>

</div>
</body>
</html>
