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
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            background: #f5f5f5;
        }
        page {
            display: block;
            background: white;
            page-break-after: always;
            padding: 20px;
            margin: 10px auto;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            max-width: 21cm;
            min-height: 29.7cm;
        }
        
        /* ─────────────────────────────────────────────── */
        /* EN-TÊTE OBLIGATOIRE BILINGUE */
        /* ─────────────────────────────────────────────── */
        .header-official {
            text-align: center;
            border-bottom: 2px solid #000;
            padding: 15px 0;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        .header-col {
            flex: 1;
            font-size: 10px;
            line-height: 1.3;
        }
        .header-col strong {
            font-size: 11px;
        }
        .header-logo {
            flex: 0 0 60px;
            text-align: center;
            font-size: 30px;
        }
        
        /* ─────────────────────────────────────────────── */
        /* INFOS ÉCOLE & ÉLÈVE */
        /* ─────────────────────────────────────────────── */
        .info-block {
            display: flex;
            gap: 20px;
            margin-bottom: 12px;
            padding: 8px;
            background: #f9f9f9;
            border-radius: 3px;
        }
        .info-col {
            flex: 1;
        }
        .info-label {
            font-weight: bold;
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 11px;
            margin-top: 2px;
        }
        
        /* ─────────────────────────────────────────────── */
        /* TABLEAU DE NOTES */
        /* ─────────────────────────────────────────────── */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }
        .grades-table th, .grades-table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }
        .grades-table th {
            background: #2c3e50;
            color: white;
            font-weight: bold;
        }
        .grades-table td:first-child {
            text-align: left;
        }
        .grades-table tr:nth-child(odd) {
            background: #f9f9f9;
        }
        .grade-pass {
            background: #d4edda;
            color: #155724;
            font-weight: bold;
        }
        .grade-fail {
            background: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
        
        /* ─────────────────────────────────────────────── */
        /* SECTION RÉSULTATS PAR TRIMESTRE */
        /* ─────────────────────────────────────────────── */
        .trimester-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .trimester-header {
            background: #34495e;
            color: white;
            padding: 8px 10px;
            margin-bottom: 8px;
            border-radius: 3px;
            font-weight: bold;
        }
        .trimester-stats {
            display: flex;
            gap: 10px;
            margin-bottom: 8px;
        }
        .stat-box {
            flex: 1;
            background: #ecf0f1;
            border: 1px solid #bdc3c7;
            padding: 6px 8px;
            border-radius: 3px;
            text-align: center;
        }
        .stat-label {
            font-size: 9px;
            color: #666;
            font-weight: bold;
        }
        .stat-value {
            font-size: 13px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        /* ─────────────────────────────────────────────── */
        /* CONDUITE & COMPORTEMENT */
        /* ─────────────────────────────────────────────── */
        .conduct-section {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .conduct-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 6px;
            color: #2c3e50;
        }
        .conduct-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            font-size: 9px;
        }
        .conduct-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .conduct-item-check {
            width: 14px;
            height: 14px;
            border: 1px solid #999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .conduct-item-check.checked {
            background: #27ae60;
            color: white;
            font-weight: bold;
        }
        .conduct-item-label {
            flex: 1;
        }
        
        /* ─────────────────────────────────────────────── */
        /* APPRÉCIATIONS */
        /* ─────────────────────────────────────────────── */
        .appreciations-section {
            margin-top: 12px;
            padding: 8px;
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            font-size: 10px;
            page-break-inside: avoid;
        }
        .appreciations-title {
            font-weight: bold;
            margin-bottom: 4px;
            color: #856404;
        }
        .appreciations-list {
            margin-left: 10px;
        }
        .appreciations-item {
            margin-bottom: 2px;
        }
        
        /* ─────────────────────────────────────────────── */
        /* FOOTER */
        /* ─────────────────────────────────────────────── */
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .signature-line {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
            font-size: 9px;
        }
        .signature-box {
            width: 100px;
            text-align: center;
        }
        .signature-date {
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

{{-- PAGE 1: NOTES & RÉSULTATS --}}
<page>
    {{-- En-tête officiel --}}
    <div class="header-official">
        <div class="header-col">
            <strong>RÉPUBLIQUE DU CAMEROUN</strong><br>
            Paix - Travail - Patrie<br>
            <hr style="margin: 2px 0;">
            Ministère de l'Éducation de Base
        </div>
        <div class="header-logo">🏫</div>
        <div class="header-col">
            <strong>REPUBLIC OF CAMEROON</strong><br>
            Peace - Work - Fatherland<br>
            <hr style="margin: 2px 0;">
            Ministry of Basic Education
        </div>
    </div>
    
    {{-- Infos école --}}
    <div class="info-block">
        <div class="info-col">
            <div class="info-label">Établissement</div>
            <div class="info-value">{{ $config->school_name ?? 'ÉTABLISSEMENT LE MILLENAIRE' }}</div>
        </div>
        <div class="info-col">
            <div class="info-label">Classe</div>
            <div class="info-value">{{ $config->nom_classe }}</div>
        </div>
        <div class="info-col">
            <div class="info-label">Année Académique</div>
            <div class="info-value">{{ $config->annee_academique }}</div>
        </div>
    </div>
    
    {{-- Infos élève --}}
    <div class="info-block">
        <div class="info-col">
            <div class="info-label">Matricule</div>
            <div class="info-value">{{ $student->matricule }}</div>
        </div>
        <div class="info-col" style="flex: 2;">
            <div class="info-label">Nom & Prénom</div>
            <div class="info-value">{{ $student->nom }}</div>
        </div>
        <div class="info-col">
            <div class="info-label">Sexe</div>
            <div class="info-value">{{ $student->sexe === 'M' ? 'Masculin' : 'Féminin' }}</div>
        </div>
    </div>
    
    {{-- TABLEAU DE NOTES PAR MATIÈRE --}}
    <div style="margin-bottom: 15px; page-break-inside: avoid;">
        <div style="background: #34495e; color: white; padding: 8px 10px; margin-bottom: 8px; border-radius: 3px; font-weight: bold; font-size: 11px;">
            📚 NOTES DÉTAILLÉES PAR MATIÈRE
        </div>
        <table class="grades-table">
            <thead>
                <tr>
                    <th>Matière</th>
                    <th>Coef.</th>
                    <th colspan="2">Seq 1</th>
                    <th colspan="2">Seq 2</th>
                    <th colspan="2">Seq 3</th>
                    <th colspan="2">Seq 4</th>
                    <th colspan="2">Seq 5</th>
                    <th colspan="2">Seq 6</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                    @php
                        $subjectNotes = $notes[$subject->id] ?? ['notes' => []];
                        $subjectNotes = $subjectNotes['notes'] ?? [];
                    @endphp
                    <tr>
                        <td style="text-align: left;">{{ $subject->nom }}</td>
                        <td>{{ $subject->coefficient }}</td>
                        @for($seq = 1; $seq <= 6; $seq++)
                            @php
                                $note = $subjectNotes[$seq] ?? null;
                                $isPass = $note !== null && $note >= 10;
                            @endphp
                            <td class="{{ $isPass ? 'grade-pass' : ($note !== null ? 'grade-fail' : '') }}" style="width: 8%;">
                                {{ $note !== null ? number_format($note, 2) : '—' }}
                            </td>
                            <td style="font-size: 9px; text-align: center; background: #f0f0f0; width: 5%;">
                                {{-- Colonne vide pour l'espacement --}}
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    {{-- RÉSULTATS PAR TRIMESTRE --}}
    @for($t = 1; $t <= 3; $t++)
        @php
            $trimesterLabel = match($t) {
                1 => '1er TRIMESTRE (Séquences 1 & 2)',
                2 => '2ème TRIMESTRE (Séquences 3 & 4)',
                3 => '3ème TRIMESTRE / FINAL (Séquences 5, 6 & Moyenne Annuelle)',
            };
            
            $trimData = $trimesterData[$t] ?? null;
            $moyenne = $trimData['moyenne'] ?? 0;
            $rang = $trimData['rang'] ?? '-';
        @endphp
        
        <div class="trimester-section">
            <div class="trimester-header">📊 {{ $trimesterLabel }}</div>
            
            <div class="trimester-stats">
                <div class="stat-box">
                    <div class="stat-label">MOYENNE</div>
                    <div class="stat-value {{ $moyenne >= 10 ? 'grade-pass' : 'grade-fail' }}">
                        {{ $moyenne > 0 ? number_format($moyenne, 2) : '—' }}
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">RANG</div>
                    <div class="stat-value">
                        {{ $rang !== '-' ? $rang : '—' }}
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">APPRÉCIATION</div>
                    <div class="stat-value">
                        @php
                            $appr = match(true) {
                                $moyenne < 10 => 'Échec',
                                $moyenne < 12 => 'Passable',
                                $moyenne < 15 => 'Assez Bien',
                                $moyenne < 17 => 'Bien',
                                default => 'Excellent'
                            };
                        @endphp
                        {{ $appr }}
                    </div>
                </div>
            </div>
        </div>
    @endfor
    
    {{-- CONDUITE & COMPORTEMENT --}}
    @if($conduite)
    <div class="conduct-section">
        <div class="conduct-title">🧑‍💼 CONDUITE & COMPORTEMENT</div>
        
        <div class="conduct-grid">
            @if($conduite->tableau_honneur)
                <div class="conduct-item">
                    <div class="conduct-item-check checked">✓</div>
                    <div class="conduct-item-label">Tableau d'Honneur</div>
                </div>
            @endif
            @if($conduite->encouragement)
                <div class="conduct-item">
                    <div class="conduct-item-check checked">✓</div>
                    <div class="conduct-item-label">Encouragement</div>
                </div>
            @endif
            @if($conduite->felicitations)
                <div class="conduct-item">
                    <div class="conduct-item-check checked">✓</div>
                    <div class="conduct-item-label">Félicitations</div>
                </div>
            @endif
            @if($conduite->blame_travail)
                <div class="conduct-item">
                    <div class="conduct-item-check checked">✓</div>
                    <div class="conduct-item-label">Blame Travail</div>
                </div>
            @endif
            @if($conduite->absences_totales > 0)
                <div class="conduct-item">
                    <div class="conduct-item-label">Absences: {{ $conduite->absences_totales }}h</div>
                </div>
            @endif
            @if($conduite->absences_nj > 0)
                <div class="conduct-item">
                    <div class="conduct-item-label">Absences NJ: {{ $conduite->absences_nj }}h</div>
                </div>
            @endif
        </div>
    </div>
    @endif
    
    {{-- FOOTER --}}
    <div class="footer">
        Bulletin généré par le Système de Gestion des Bulletins NG - Le Millenaire<br>
        Date d'émission: {{ now()->format('d/m/Y à H:i') }}
    </div>
    
    <div class="signature-line">
        <div class="signature-box">
            <div class="signature-date"></div>
            Professeur Principal
        </div>
        <div class="signature-box">
            <div class="signature-date"></div>
            Directeur d'Établissement
        </div>
    </div>
</page>

</body>
</html>
