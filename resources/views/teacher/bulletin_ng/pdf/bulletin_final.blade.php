<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin - {{ $student->nom }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #2c3e50;
            line-height: 1.4;
            background: #f5f5f5;
        }
        page {
            display: block;
            background: white;
            page-break-after: always;
            padding: 15px;
            margin: 5px auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 21cm;
            min-height: 29.7cm;
        }
        
        /* ─────────────────────────────────────────────── */
        /* COULEURS SYSTÈME */
        /* ─────────────────────────────────────────────── */
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --success: #27ae60;
            --warning: #e74c3c;
            --info: #3498db;
            --light: #ecf0f1;
            --border: #bdc3c7;
        }
        
        /* ─────────────────────────────────────────────── */
        /* EN-TÊTE */
        /* ─────────────────────────────────────────────── */
        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header-logo {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .header-logo img {
            max-width: 100%;
            max-height: 100%;
        }
        .header-info {
            flex: 1;
        }
        .school-name {
            font-size: 14px;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 2px;
        }
        .school-motto {
            font-size: 9px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        /* ─────────────────────────────────────────────── */
        /* SECTION INFOS */
        /* ─────────────────────────────────────────────── */
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        .info-box {
            background: var(--light);
            border: 1px solid var(--border);
            padding: 8px;
            border-radius: 3px;
        }
        .info-label {
            font-weight: bold;
            font-size: 9px;
            color: var(--primary);
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 11px;
            color: #2c3e50;
        }
        
        /* ─────────────────────────────────────────────── */
        /* SECTION TRIMESTRE */
        /* ─────────────────────────────────────────────── */
        .trimester-section {
            margin-bottom: 20px;
        }
        .trimester-title {
            background: var(--primary);
            color: white;
            padding: 10px 12px;
            font-weight: bold;
            font-size: 12px;
            border-radius: 4px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* ─────────────────────────────────────────────── */
        /* TABLEAU GRADES */
        /* ─────────────────────────────────────────────── */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }
        .grades-table thead {
            background: var(--secondary);
            color: white;
        }
        .grades-table th {
            border: 1px solid var(--border);
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
        }
        .grades-table td {
            border: 1px solid var(--border);
            padding: 5px 4px;
            text-align: center;
        }
        .grades-table td:first-child {
            text-align: left;
            padding-left: 8px;
            font-weight: 500;
        }
        .grades-table td:nth-child(2) {
            text-align: center;
            font-weight: bold;
            background: #f9f9f9;
        }
        .grades-table tr:nth-child(odd) {
            background: #fafafa;
        }
        .grade-pass {
            background: #d4edda;
            color: var(--success);
            font-weight: bold;
        }
        .grade-fail {
            background: #f8d7da;
            color: var(--warning);
            font-weight: bold;
        }
        .grade-neutral {
            background: #e3f2fd;
            color: var(--info);
        }
        
        /* ─────────────────────────────────────────────── */
        /* RÉSULTATS TRIMESTRE */
        /* ─────────────────────────────────────────────── */
        .results-box {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 8px;
            margin-bottom: 12px;
        }
        .result-item {
            background: var(--light);
            border: 2px solid var(--secondary);
            border-radius: 4px;
            padding: 10px;
            text-align: center;
        }
        .result-label {
            font-size: 9px;
            color: var(--secondary);
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .result-value {
            font-size: 16px;
            font-weight: bold;
            color: var(--primary);
        }
        .result-value.high {
            color: var(--success);
        }
        .result-value.low {
            color: var(--warning);
        }
        
        /* ─────────────────────────────────────────────── */
        /* CONDUITE & COMPORTEMENT */
        /* ─────────────────────────────────────────────── */
        .conduct-section {
            background: #fff9e6;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 15px 0;
            border-radius: 3px;
        }
        .conduct-title {
            font-weight: bold;
            font-size: 11px;
            color: #b8860b;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .conduct-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            font-size: 10px;
        }
        .conduct-item {
            display: flex;
            align-items: flex-start;
            gap: 6px;
        }
        .conduct-checkbox {
            width: 16px;
            height: 16px;
            border: 1.5px solid #bbb;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 2px;
        }
        .conduct-checkbox.checked {
            background: var(--success);
            color: white;
            border-color: var(--success);
            font-weight: bold;
            font-size: 12px;
        }
        .conduct-label {
            color: #2c3e50;
        }
        
        /* ─────────────────────────────────────────────── */
        /* FOOTER */
        /* ─────────────────────────────────────────────── */
        .footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 2px solid var(--primary);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            font-size: 9px;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin: 30px 0 5px 0;
        }
        .signature-name {
            font-weight: bold;
            font-size: 9px;
        }
    </style>
</head>
<body>

{{-- PAGE UNIQUE --}}
<page>
    {{-- EN-TÊTE --}}
    <div class="header">
        @if($logoUrl)
            <div class="header-logo">
                <img src="{{ $logoUrl }}" alt="Logo">
            </div>
        @else
            <div class="header-logo" style="font-size: 30px;">🏫</div>
        @endif
        <div class="header-info">
            <div class="school-name">{{ $config->school_name ?? 'ÉTABLISSEMENT LE MILLENAIRE' }}</div>
            <div class="school-motto">Année Académique: {{ $config->annee_academique }}</div>
        </div>
    </div>
    
    {{-- INFOS ÉLÈVE ET CLASSE --}}
    <div class="info-section">
        <div class="info-box">
            <div class="info-label">📚 Classe</div>
            <div class="info-value">{{ $config->nom_classe }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">👤 Élève</div>
            <div class="info-value">{{ $student->nom }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">🆔 Matricule</div>
            <div class="info-value">{{ $student->matricule }}</div>
        </div>
    </div>
    
    {{-- TRIMESTRE ACTUEL --}}
    @php
        $t = $currentTriple;
        $trimesterLabel = match($t) {
            1 => '1er TRIMESTRE (Séquences 1 & 2)',
            2 => '2e TRIMESTRE (Séquences 3 & 4)',
            3 => '3e TRIMESTRE - FINAL (Moyennes Annuelles)',
        };
        $trimData = $trimesterData[$t] ?? null;
        $moyenne = $trimData['moyenne'] ?? 0;
        $rang = $trimData['rang'] ?? '-';
        $effectif = $trimData['effectif'] ?? 0;
    @endphp
    
    <div class="trimester-section">
        <div class="trimester-title">
            <span>{{ $trimesterLabel }}</span>
            <span style="font-size: 10px; font-weight: normal;">Généré le: {{ now()->format('d/m/Y') }}</span>
        </div>
        
        {{-- NOTES PAR MATIÈRE --}}
        <table class="grades-table">
            <thead>
                <tr>
                    <th style="text-align: left; width: 20%;">Matière</th>
                    <th style="width: 6%;">Coef</th>
                    <th style="width: 8%;">Prof</th>
                    @for($seq = 1; $seq <= 6; $seq++)
                        <th style="width: 6%;">S{{ $seq }}</th>
                    @endfor
                    <th style="width: 8%;">Moy</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                    @php
                        $subjectData = $notes[$subject->id] ?? [];
                        $subjectNotes = $subjectData['notes'] ?? [];
                        $teacherName = $subjectData['teacher_name'] ?? $subject->nom_prof ?? 'N/A'; // ✅ FIX: Use nom_prof
                    @endphp
                    <tr>
                        <td>{{ $subject->nom }}</td>
                        <td>{{ $subject->coefficient }}</td>
                        <td style="font-size: 9px; padding: 3px;">{{ substr($teacherName, 0, 10) }}</td>
                        @for($seq = 1; $seq <= 6; $seq++)
                            @php
                                $note = $subjectNotes[$seq] ?? null;
                                $isPass = $note !== null && $note >= 10;
                            @endphp
                            <td class="{{ $isPass ? 'grade-pass' : ($note !== null ? 'grade-fail' : 'grade-neutral') }}">
                                {{ $note !== null ? number_format($note, 1) : '—' }}
                            </td>
                        @endfor
                        <td style="background: #e3f2fd; font-weight: bold; color: var(--info);">
                            {{-- Average will be calculated if needed --}}
                            —
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{-- RÉSULTATS PAR TRIMESTRE --}}
        <div class="results-box">
            <div class="result-item">
                <div class="result-label">Moyenne du Trimestre</div>
                <div class="result-value {{ $moyenne >= 10 ? 'high' : 'low' }}">
                    {{ $moyenne > 0 ? number_format($moyenne, 2) : '—' }}
                </div>
                <div style="font-size: 8px; color: #666; margin-top: 2px;">/20</div>
            </div>
            <div class="result-item">
                <div class="result-label">Rang de Classe</div>
                <div class="result-value">{{ $rang !== '-' ? $rang : '—' }}</div>
                <div style="font-size: 8px; color: #666; margin-top: 2px;">@if($effectif)sur {{ $effectif }}@endif</div>
            </div>
            <div class="result-item">
                <div class="result-label">Appréciation</div>
                <div class="result-value" style="font-size: 13px;">
                    @php
                        $appr = match(true) {
                            $moyenne < 10 => '❌ Échec',
                            $moyenne < 12 => '⚠ Passable',
                            $moyenne < 15 => '✓ Assez Bien',
                            $moyenne < 17 => '✓✓ Bien',
                            default => '🌟 Excellent'
                        };
                    @endphp
                    {{ $appr }}
                </div>
            </div>
            <div class="result-item">
                <div class="result-label">Statut</div>
                <div class="result-value" style="font-size: 12px; color: {{ $moyenne >= 10 ? 'var(--success)' : 'var(--warning)' }};">
                    {{ $moyenne >= 10 ? '✓ Admis' : '✗ Renvoi' }}
                </div>
            </div>
        </div>
    </div>
    
    {{-- CONDUITE & COMPORTEMENT --}}
    @if($conduite)
    <div class="conduct-section">
        <div class="conduct-title">🧑‍💼 CONDUITE & COMPORTEMENT</div>
        <div class="conduct-grid">
            <div class="conduct-item">
                <div class="conduct-checkbox {{ $conduite->tableau_honneur ? 'checked' : '' }}">
                    {{ $conduite->tableau_honneur ? '✓' : '' }}
                </div>
                <div class="conduct-label">Tableau d'Honneur</div>
            </div>
            <div class="conduct-item">
                <div class="conduct-checkbox {{ $conduite->encouragement ? 'checked' : '' }}">
                    {{ $conduite->encouragement ? '✓' : '' }}
                </div>
                <div class="conduct-label">Encouragement</div>
            </div>
            <div class="conduct-item">
                <div class="conduct-checkbox {{ $conduite->felicitations ? 'checked' : '' }}">
                    {{ $conduite->felicitations ? '✓' : '' }}
                </div>
                <div class="conduct-label">Félicitations</div>
            </div>
            <div class="conduct-item">
                <div class="conduct-checkbox {{ $conduite->blame_travail ? 'checked' : '' }}">
                    {{ $conduite->blame_travail ? '✓' : '' }}
                </div>
                <div class="conduct-label">Blâme (Travail)</div>
            </div>
            @if($conduite->absences_totales && $conduite->absences_totales > 0)
            <div class="conduct-item">
                <div style="color: var(--warning); font-weight: bold;">⏱</div>
                <div class="conduct-label">Absences: {{ $conduite->absences_totales }}h</div>
            </div>
            @endif
            @if($conduite->absences_nj && $conduite->absences_nj > 0)
            <div class="conduct-item">
                <div style="color: var(--warning); font-weight: bold;">❌</div>
                <div class="conduct-label">Absences NJ: {{ $conduite->absences_nj }}h</div>
            </div>
            @endif
        </div>
    </div>
    @endif
    
    {{-- FOOTER --}}
    <div class="footer">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-name">Professeur Principal</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-name">Chef d'Établissement</div>
        </div>
    </div>
    
    <div style="margin-top: 10px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 8px;">
        Bulletin généré par le Système de Gestion des Bulletins NG • {{ env('APP_NAME') }}
    </div>
</page>

</body>
</html>
