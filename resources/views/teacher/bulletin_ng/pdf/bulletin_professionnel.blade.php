<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin - {{ $student->nom }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }
        
        page {
            display: block;
            background: white;
            page-break-after: always;
            padding: 10px;
            margin: 0;
            max-width: 21cm;
            min-height: 29.7cm;
        }
        
        /* ════════════════════════════════════════════ */
        /* EN-TÊTE OFFICIEL */
        /* ════════════════════════════════════════════ */
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding: 8px 0;
            margin-bottom: 8px;
            font-size: 10px;
        }
        
        .header-row {
            display: flex;
            justify-content: space-around;
            align-items: flex-start;
            gap: 20px;
        }
        
        .header-col {
            flex: 1;
            text-align: center;
            line-height: 1.3;
        }
        
        .header-col strong {
            font-size: 11px;
            display: block;
            margin-bottom: 2px;
        }
        
        .header-logo {
            flex: 0 0 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .header-logo img {
            max-width: 80px;
            max-height: 80px;
        }
        
        .divider-line {
            border-bottom: 2px solid #000;
            margin: 3px 0;
        }
        
        /* ════════════════════════════════════════════ */
        /* TITRE ET INFOS BULLETIN */
        /* ════════════════════════════════════════════ */
        .bulletin-title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 8px 0 3px 0;
        }
        
        .bulletin-subtitle {
            text-align: center;
            font-size: 10px;
            margin-bottom: 8px;
        }
        
        /* ════════════════════════════════════════════ */
        /* SECTION INFOS ÉLÈVE */
        /* ════════════════════════════════════════════ */
        .student-info {
            margin-bottom: 8px;
            border: 2px solid #000;
            padding: 6px;
            background: #f9f9f9;
        }
        
        .info-table {
            width: 100%;
            font-size: 10px;
        }
        
        .info-table td {
            padding: 3px 6px;
            vertical-align: top;
        }
        
        .info-label {
            font-weight: bold;
            width: 35%;
        }
        
        /* ════════════════════════════════════════════ */
        /* TABLEAU NOTES */
        /* ════════════════════════════════════════════ */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 10px;
        }
        
        .grades-table thead {
            background: #4472C4;
            color: white;
        }
        
        .grades-table th {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
        }
        
        .grades-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            height: 20px;
        }
        
        .grades-table td:first-child {
            text-align: left;
            font-weight: 500;
            padding-left: 6px;
        }
        
        .grades-table tr:nth-child(odd) {
            background: white;
        }
        
        .grades-table tr:nth-child(even) {
            background: #f2f2f2;
        }
        
        .section-total {
            background: #D9E1F2;
            font-weight: bold;
        }
        
        .grade-good {
            background: #C6E0B4;
            font-weight: bold;
        }
        
        .grade-bad {
            background: #F4B084;
            font-weight: bold;
        }
        
        /* ════════════════════════════════════════════ */
        /* RÉSUMÉ ANNUEL */
        /* ════════════════════════════════════════════ */
        .summary-section {
            margin: 10px 0;
            border-collapse: collapse;
            font-size: 10px;
        }
        
        .summary-section td {
            border: 1px solid #000;
            padding: 5px 6px;
        }
        
        .summary-header {
            background: #4472C4;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        /* ════════════════════════════════════════════ */
        /* SIGNATURES */
        /* ════════════════════════════════════════════ */
        .signatures {
            margin-top: 15px;
            display: flex;
            justify-content: space-around;
            font-size: 9px;
        }
        
        .signature-box {
            width: 30%;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin: 40px 0 5px 0;
        }
    </style>
</head>
<body>

<page>
    {{-- EN-TÊTE OFFICIEL --}}
    <div class="header">
        <div class="header-row">
            <div class="header-col">
                <strong>RÉPUBLIQUE DU CAMEROUN</strong>
                Paix-Travail-Patrie
                <div class="divider-line"></div>
                Ministère de l'Éducation de Base
                <br>Délégation Régionale de l'Adamaoua
                <br>Délégation Départementale de la Vina
            </div>
            
            <div class="header-logo">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo">
                @else
                    <div style="font-size: 40px;">🏫</div>
                @endif
            </div>
            
            <div class="header-col">
                <strong>REPUBLIC OF CAMEROON</strong>
                Peace-Work-Fatherland
                <div class="divider-line"></div>
                Ministry of Basic Education
                <br>Adamaoua Regional Delegation
                <br>Divisional Delegation of Vina Division
            </div>
        </div>
    </div>
    
    {{-- NOM ÉTABLISSEMENT --}}
    <div style="text-align: center; font-weight: bold; font-size: 12px; margin-bottom: 3px; color: #2c3e50;">
        {{ $config->school_name ?? 'ÉTABLISSEMENT LE MILLENAIRE' }}
    </div>
    <div style="text-align: center; font-size: 9px; margin-bottom: 8px;">
        {{ $config->delegation_fr ?? 'Division/Région' }} • {{ $config->numero_telephoneecole ?? 'Tél: (237)...' }}
    </div>
    
    {{-- TITRE BULLETIN --}}
    <div class="bulletin-title">
        BULLETIN SCOLAIRE - 
        @php
            $trimLabels = [
                1 => '1er TRIMESTRE',
                2 => '2e TRIMESTRE',
                3 => '3e TRIMESTRE'
            ];
        @endphp
        {{ $trimLabels[$config->trimestre] ?? '1er TRIMESTRE' }}
    </div>
    
    <div class="bulletin-subtitle">
        Année Scolaire: {{ $config->annee_academique }}
    </div>
    
    {{-- INFOS ÉLÈVE --}}
    <div class="student-info">
        <div style="border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 3px; font-weight: bold;">
            INFORMATIONS DE L'ÉLÈVE
        </div>
        <table class="info-table">
            <tr>
                <td class="info-label">Nom et Prénom:</td>
                <td>{{ strtoupper($student->nom) }}</td>
                <td class="info-label">Matricule:</td>
                <td>{{ $student->matricule }}</td>
            </tr>
            <tr>
                <td class="info-label">Date de Naissance:</td>
                <td>{{ $student->date_naissance ? \Carbon\Carbon::parse($student->date_naissance)->format('d/m/Y') : 'N/A' }}</td>
                <td class="info-label">Classe:</td>
                <td>{{ $config->nom_classe }}</td>
            </tr>
            <tr>
                <td class="info-label">Lieu de Naissance:</td>
                <td>{{ $student->lieu_naissance ?? 'N/A' }}</td>
                <td class="info-label">Effectif:</td>
                <td>{{ $config->effectif }}</td>
            </tr>
            <tr>
                <td class="info-label">Enseignant(e) principal(e):</td>
                <td colspan="3">{{ Auth::user()->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
    
    {{-- TABLEAU NOTES --}}
    @if($subjects->count() > 0)
    <table class="grades-table">
        <thead>
            <tr>
                <th>MATIÈRES</th>
                <th>PROF</th>
                <th>SEQ1</th>
                <th>SEQ2</th>
                <th>COMP</th>
                <th>MOY</th>
                <th>COEF</th>
                <th>TOTAL</th>
                <th>RANG</th>
                <th>APPRÉCIATION</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjects as $subject)
                @php
                    $subjectData = $courseDetails[$subject->id] ?? [];
                    $seq1 = $subjectData['seq1'] ?? 0;
                    $seq2 = $subjectData['seq2'] ?? 0;
                    $composite = $subjectData['composite'] ?? 0;
                    $moyenne = $subjectData['moyenne'] ?? 0;
                    $coeff = $subject->coefficient ?? 0;
                    $total = $subjectData['total'] ?? 0;
                    $rang = $subjectData['rang'] ?? '-';
                    $appreciation = $subjectData['appreciation'] ?? 'N/A';
                    // ✅ FIX: Use correct field names from BulletinNgSubject model
                    $subjectName = $subject->nom ?? 'N/A'; // ✅ Use nom field
                    $teacherName = $subject->nom_prof ?? $subject->teacher?->name ?? 'N/A'; // ✅ Use nom_prof first, then fallback to teacher->name
                    $teacherShort = substr($teacherName, 0, 15);
                @endphp
                <tr>
                    <td>{{ $subjectName }}</td>
                    <td style="font-size: 8px;">{{ $teacherShort }}</td>
                    <td class="{{ $seq1 >= 10 ? 'grade-good' : ($seq1 > 0 ? 'grade-bad' : '') }}">
                        {{ $seq1 > 0 ? number_format($seq1, 1) : '—' }}
                    </td>
                    <td class="{{ $seq2 >= 10 ? 'grade-good' : ($seq2 > 0 ? 'grade-bad' : '') }}">
                        {{ $seq2 > 0 ? number_format($seq2, 1) : '—' }}
                    </td>
                    <td class="{{ $composite >= 10 ? 'grade-good' : ($composite > 0 ? 'grade-bad' : '') }}">
                        {{ $composite > 0 ? number_format($composite, 1) : '—' }}
                    </td>
                    <td>{{ $moyenne > 0 ? number_format($moyenne, 1) : '—' }}</td>
                    <td>{{ $coeff }}</td>
                    <td>{{ $total > 0 ? number_format($total, 1) : '—' }}</td>
                    <td>{{ $rang }}</td>
                    <td style="font-size: 9px;">{{ $appreciation }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="background: #fff3cd; border: 1px solid #f8d7da; padding: 10px; margin: 8px 0; border-radius: 4px;">
        <strong>⚠️ Aucune matière configurée pour cette classe</strong>
    </div>
    @endif
    
    {{-- RÉSUMÉ ANNUEL DE L'ÉLÈVE --}}
    <table style="width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 10px;">
        <tr>
            <td class="summary-header" style="width: 25%;">RÉSULTAT ANNUEL DE L'ÉLÈVE</td>
            <td class="summary-header" style="width: 25%;">PROFIL DE LA CLASSE</td>
            <td class="summary-header" style="width: 25%;">TRAVAIL DE L'ÉLÈVE</td>
            <td class="summary-header" style="width: 25%;">CONDUITE DE L'ÉLÈVE</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 6px; vertical-align: top;">
                <div>Moyenne de l'élève: <strong>{{ number_format($summaryData['student_avg'] ?? 0, 2) }}</strong></div>
                <div>Rang de l'élève: <strong>{{ $summaryData['student_rank'] ?? '-' }} / {{ $config->effectif }}</strong></div>
                <div style="margin-top: 3px; font-weight: bold;">
                    {{ $summaryData['status'] ?? 'ACQUIS' }}
                </div>
            </td>
            <td style="border: 1px solid #000; padding: 6px; vertical-align: top;">
                <div>Moyenne → 10: <strong>{{ $summaryData['class_avg'] ?? 0 }}</strong></div>
                <div>Moyenne Classe: <strong>{{ number_format($summaryData['class_avg'] ?? 0, 2) }}</strong></div>
                <div>% Réussite: <strong>{{ $summaryData['success_rate'] ?? '0' }}%</strong></div>
                <div>Moyenne Max: <strong>{{ number_format($summaryData['max_avg'] ?? 0, 2) }}</strong></div>
                <div>Moyenne Min: <strong>{{ number_format($summaryData['min_avg'] ?? 0, 2) }}</strong></div>
            </td>
            <td style="border: 1px solid #000; padding: 6px; vertical-align: top;">
                <div>T.H + Encouragement: {{ $conduite->tableau_honneur || $conduite->encouragement ? 'Oui' : 'Non' }}</div>
                <div>T.H + Félicitation: {{ $conduite->felicitations ? 'Oui' : 'Non' }}</div>
                <div>Avertissement Travail: {{ $conduite->blame_travail ? 'Oui' : 'Non' }}</div>
                <div>Blâme Travail: {{ $conduite->blame_travail ? 'Oui' : 'Non' }}</div>
            </td>
            <td style="border: 1px solid #000; padding: 6px; vertical-align: top;">
                <div>Absences Totales: <strong>{{ $conduite->absences_totales ?? 0 }}h</strong></div>
                <div>Absences NJ: <strong>{{ $conduite->absences_nj ?? 0 }}h</strong></div>
                <div>Exclusions: {{ $conduite->exclusion ? 'Oui' : 'Non' }}</div>
                <div>Aver. Conduite: {{ $conduite->avert_conduite ? 'Oui' : 'Non' }}</div>
                <div>Blâme Conduite: {{ $conduite->blame_conduite ? 'Oui' : 'Non' }}</div>
            </td>
        </tr>
    </table>
    
    {{-- SIGNATURES --}}
    <div class="signatures">
        <div class="signature-box">
            <div style="margin-bottom: 3px; font-weight: bold;">VISA DU PARENT</div>
            <div class="signature-line"></div>
        </div>
        <div class="signature-box">
            <div style="margin-bottom: 3px; font-weight: bold;">DÉCISION DU CONSEIL DE CLASSE</div>
            <div class="signature-line"></div>
        </div>
        <div class="signature-box">
            <div style="margin-bottom: 3px; font-weight: bold;">VISA DU CHEF D'ÉTABLISSEMENT</div>
            <div class="signature-line"></div>
        </div>
    </div>
    
    <div style="margin-top: 8px; text-align: center; font-size: 8px; color: #666; border-top: 1px solid #ddd; padding-top: 5px;">
        Le bulletin est délivré sans ratures. Il s'adresse à l'établissement ou l'administration scolaire.
    </div>
</page>

</body>
</html>
